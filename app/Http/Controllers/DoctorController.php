<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Disponibilidad;
use App\Models\Cita;

use App\Services\Especialista\EspecialistaService;

class DoctorController extends Controller
{
    public function __construct(
        protected EspecialistaService $especialistaService,
        protected \App\Services\Google\GoogleMeetingService $googleMeetingService
    ) {}

    /**
     * Asegura (o crea) el perfil de especialista del usuario autenticado.
     */
    private function resolveEspecialista(Request $request): \App\Models\Especialista
    {
        $user = $request->user();
        $especialista = $user->especialista;

        if (!$especialista && $user->rol->nombre === 'doctor') {
            $especialista = \App\Models\Especialista::create([
                'usuario_id' => $user->id,
                'especialidad' => 'Psicología General',
                'biografia' => 'Perfil profesional en construcción.',
            ]);
        }

        if (!$especialista) {
            abort(403, 'El usuario no tiene un perfil de especialista asociado.');
        }

        return $especialista;
    }

    /**
     * Vista "Hoy": agenda del día, tiempo entre sesiones, tareas pendientes,
     * bandeja de solicitudes y recordatorios de mañana.
     */
    public function dashboard(Request $request): \Illuminate\View\View
    {
        $especialista = $this->resolveEspecialista($request);

        $todaySessions = $this->especialistaService->getTodaySessions($especialista->id);
        $todaySessionsCount = $this->especialistaService->countSessionsByDate($especialista->id, now()->toDateString(), [
            Cita::STATUS_RESERVADA,
            Cita::STATUS_ATTENTION,
            Cita::STATUS_COMPLETED,
        ]);
        $pendingTasks  = $this->especialistaService->getPendingTasks($especialista->id);

        // Próxima cita (para el contador de tiempo libre): la primera cuya sesión aún
        // no ha terminado — incluye las próximas y la que esté en curso ahora.
        $nextAppointment = $this->especialistaService->getUpcomingAppointments($especialista->id)
            ->first(fn ($c) => optional($c->disponibilidad) && $c->end_at->isFuture());

        // Bandeja: solicitudes de reprogramación pendientes
        $solicitudesPendientes = \App\Models\SolicitudReprogramacion::where('especialista_id', $especialista->id)
            ->where('estado', \App\Models\SolicitudReprogramacion::ESTADO_PENDIENTE)
            ->with(['paciente', 'cita.disponibilidad'])
            ->latest()
            ->get();

        // Recordatorios de mañana: cuántas citas hay y cuántas ya se notificaron
        $manana = now()->addDay()->toDateString();
        $citasManana = $this->especialistaService->countSessionsByDate($especialista->id, $manana, [
            Cita::STATUS_RESERVADA,
            Cita::STATUS_RESCHEDULED,
            Cita::STATUS_ATTENTION,
        ]);
        $recordatorios = app(\App\Services\Recordatorio\RecordatorioService::class)->logDeFecha($manana, $especialista->id);

        // Calendario (modal "Ver calendario"): todas las citas del especialista
        // agrupadas por fecha, para marcar los días con reservas por estado.
        $calendarDays = [];
        \App\Models\Cita::where('especialista_id', $especialista->id)
            ->whereHas('disponibilidad')
            ->with(['disponibilidad:id,fecha,hora_inicio', 'paciente:id,nombre'])
            ->get()
            ->each(function ($c) use (&$calendarDays) {
                $fecha = optional(optional($c->disponibilidad)->fecha)->toDateString();
                if (!$fecha) {
                    return;
                }
                $cat = match ($c->estado) {
                    \App\Models\Cita::STATUS_CANCELLED => 'cancelada',
                    \App\Models\Cita::STATUS_COMPLETED => 'realizada',
                    default => 'reservada', // reservada / reprogramada / en_atencion
                };
                $label = match ($c->estado) {
                    \App\Models\Cita::STATUS_CANCELLED   => 'Cancelada',
                    \App\Models\Cita::STATUS_COMPLETED   => 'Realizada',
                    \App\Models\Cita::STATUS_ATTENTION   => 'En atención',
                    \App\Models\Cita::STATUS_RESCHEDULED => 'Reprogramada',
                    default                              => 'Reservada',
                };
                $calendarDays[$fecha][] = [
                    'hora'     => \Carbon\Carbon::parse($c->disponibilidad->hora_inicio)->format('h:i A'),
                    'paciente' => $c->paciente->nombre ?? 'Paciente',
                    'cat'      => $cat,
                    'label'    => $label,
                ];
            });

        // Ordenar cada día por hora
        foreach ($calendarDays as $f => $items) {
            usort($calendarDays[$f], fn ($a, $b) => strcmp($a['hora'], $b['hora']));
        }

        return view('doctor.dashboard', compact(
            'especialista', 'todaySessions', 'todaySessionsCount', 'pendingTasks', 'nextAppointment',
            'solicitudesPendientes', 'citasManana', 'recordatorios', 'calendarDays'
        ));
    }

    /**
     * Vista "Métricas": estadísticas reales del especialista.
     */
    public function metrics(Request $request): \Illuminate\View\View
    {
        $especialista = $this->resolveEspecialista($request);
        $stats = $this->especialistaService->getRealMetrics($especialista->id);

        return view('doctor.metrics', compact('especialista', 'stats'));
    }

    /**
     * Disparar manualmente el envío de recordatorios de las citas de mañana.
     */
    public function enviarRecordatorios(Request $request): \Illuminate\Http\RedirectResponse
    {
        $especialista = $this->resolveEspecialista($request);
        $manana = now()->addDay()->toDateString();

        $enviados = app(\App\Services\Recordatorio\RecordatorioService::class)->enviarParaFecha($manana, $especialista->id);

        return back()->with('success', $enviados > 0
            ? "Se enviaron {$enviados} recordatorio(s) para mañana."
            : 'No hay recordatorios pendientes de enviar para mañana.');
    }

    /**
     * Show the schedule management view.
     */
    public function schedule(Request $request): \Illuminate\View\View
    {
        $user = $request->user();
        $especialista = $user->especialista;

        if (!$especialista && $user->rol->nombre === 'doctor') {
            $especialista = \App\Models\Especialista::create([
                'usuario_id' => $user->id,
                'especialidad' => 'Psicología General',
                'biografia' => 'Perfil profesional en construcción.',
            ]);
        }
        
        // Obtener bloques de los próximos 30 días para la gestión
        $availability = Disponibilidad::where('especialista_id', $especialista->id)
            ->where('fecha', '>=', now()->toDateString())
            ->orderBy('fecha')
            ->orderBy('hora_inicio')
            ->get();

        // Obtener horarios semanales recurrentes
        $weeklySchedules = $especialista->horariosSemanales()->orderBy('dia_semana')->orderBy('hora_inicio')->get();

        // Próximas citas activas (incluye las de días recientes aún sin cerrar, para poder finalizarlas)
        $upcomingCitas = Cita::join('disponibilidades', 'citas.disponibilidad_id', '=', 'disponibilidades.id')
            ->where('citas.especialista_id', $especialista->id)
            ->whereIn('citas.estado', [Cita::STATUS_RESERVADA, Cita::STATUS_ATTENTION])
            ->where('disponibilidades.fecha', '>=', now()->subDays(7)->toDateString())
            ->with(['paciente', 'disponibilidad'])
            ->select('citas.*')
            ->orderBy('disponibilidades.fecha', 'asc')
            ->orderBy('disponibilidades.hora_inicio', 'asc')
            ->get();

        return view('doctor.schedule', compact('availability', 'especialista', 'weeklySchedules', 'upcomingCitas'));
    }

    /**
     * List all assigned patients.
     */
    public function patients(Request $request): \Illuminate\View\View
    {
        $especialista = $request->user()->especialista;
        $pacientes = $this->especialistaService->getAssignedPatients($especialista->id);
        
        return view('doctor.patients.index', compact('pacientes'));
    }

    /**
     * Show a detailed patient history.
     */
    public function showPatient(Request $request, int $id): \Illuminate\View\View
    {
        $especialista = $request->user()->especialista;
        
        // El id recibido es el usuario_id del paciente
        $paciente = \App\Models\Paciente::where('usuario_id', $id)
            ->with('usuario')
            ->firstOrFail();

        $citas = $this->especialistaService->getPatientHistory($especialista->id, $id);

        // Notas clínicas de este paciente (crisis primero)
        $notas = \App\Models\NotaEspecialista::where('especialista_id', $especialista->id)
            ->where('paciente_id', $id)
            ->latest()
            ->get();
        $crisisActiva = $notas->firstWhere('tipo', \App\Models\NotaEspecialista::TIPO_CRISIS);

        // Estado de ánimo basado en las sesiones (más antigua -> más reciente, con algún registro)
        $animoSesiones = $citas
            ->filter(fn ($c) => $c->animo_inicial !== null || $c->animo_final !== null)
            ->sortBy(fn ($c) => optional($c->disponibilidad)->fecha)
            ->take(8)
            ->values();

        return view('doctor.patients.show', compact('paciente', 'citas', 'notas', 'crisisActiva', 'animoSesiones'));
    }

    /**
     * Guardar una nota clínica ligada a un paciente.
     */
    public function storePatientNote(Request $request, int $id)
    {
        $especialista = $request->user()->especialista;

        $validated = $request->validate([
            'titulo'    => 'required|string|max:255',
            'contenido' => 'required|string|max:5000',
        ]);

        \App\Models\NotaEspecialista::create([
            'especialista_id' => $especialista->id,
            'paciente_id'     => $id,
            'titulo'          => $validated['titulo'],
            'contenido'       => $validated['contenido'],
            'tipo'            => \App\Models\NotaEspecialista::TIPO_GENERAL,
        ]);

        return back()->with('success', 'Nota clínica guardada.');
    }

    /**
     * Registrar un evento de crisis del paciente (nota destacada).
     */
    public function reportCrisis(Request $request, int $id)
    {
        $especialista = $request->user()->especialista;

        $validated = $request->validate([
            'contenido' => 'required|string|max:5000',
        ]);

        \App\Models\NotaEspecialista::create([
            'especialista_id' => $especialista->id,
            'paciente_id'     => $id,
            'titulo'          => 'Alerta de crisis · ' . now()->format('d/m/Y H:i'),
            'contenido'       => $validated['contenido'],
            'tipo'            => \App\Models\NotaEspecialista::TIPO_CRISIS,
        ]);

        return back()->with('success', 'Crisis registrada. Se ha marcado una alerta en la ficha del paciente.');
    }

    /**
     * Eliminar una nota clínica (permite resolver una crisis).
     */
    public function deleteNota(Request $request, \App\Models\NotaEspecialista $nota)
    {
        $especialista = $request->user()->especialista;

        if ($nota->especialista_id !== $especialista->id) {
            abort(403);
        }

        $nota->delete();

        return back()->with('success', 'Nota eliminada.');
    }

    /**
     * Registrar el estado de ánimo de la sesión (inicio o fin) por parte del doctor.
     */
    public function saveAnimo(Request $request, \App\Models\Cita $cita)
    {
        $especialista = $request->user()->especialista;

        if ($cita->especialista_id !== $especialista->id) {
            abort(403);
        }

        $validated = $request->validate([
            'momento' => 'required|in:inicial,final',
            'valor'   => 'required|integer|min:1|max:5',
        ]);

        $cita->{'animo_' . $validated['momento']} = $validated['valor'];
        $cita->save();

        return back()->with('success', 'Estado de ánimo registrado.');
    }

    /**
     * Handle the "Join Session" request.
     * Generates a Google Meet link if it doesn't exist and redirects directly to it.
     */
    public function joinMeeting(Request $request, Cita $cita)
    {
        $user = $request->user();
        $especialista = $user->especialista;

        // Validar que la cita pertenece al especialista
        if ($cita->especialista_id !== $especialista->id) {
            abort(403, 'No tienes permiso para acceder a esta sesión.');
        }

        // El enlace solo se habilita 30 minutos antes del inicio.
        $cita->loadMissing('disponibilidad');
        if ($cita->disponibilidad && now()->lt($cita->start_at->copy()->subMinutes(30))) {
            return back()->with('info', 'El enlace de Meet se habilita 30 minutos antes del inicio de la sesión.');
        }

        // Si no tiene enlace, lo generamos
        if (empty($cita->meeting_url)) {
            if (empty($user->google_access_token)) {
                return redirect()->route('google.auth')->with('warning', 'Necesitas conectar tu cuenta de Google para generar el enlace de la sesión.');
            }

            try {
                // Datos para el evento de calendar (reusando lógica de MeetingController)
                $meetingData = [
                    'title'       => 'Sesión Terapéutica: ' . ($cita->paciente->nombre ?? 'Paciente'),
                    'description' => 'Sesión virtual agendada vía ' . config('clinic.name') . '.',
                    'start_time'  => $cita->disponibilidad->fecha->toDateString() . ' ' . $cita->disponibilidad->hora_inicio,
                    'end_time'    => $cita->disponibilidad->fecha->toDateString() . ' ' . $cita->disponibilidad->hora_fin,
                    // Invitar a paciente y especialista para que la reunión quede en sus calendarios
                    'attendees'   => array_filter([
                        $cita->paciente->email ?? null,
                        $user->email ?? null,
                    ]),
                ];

                $meetingUrl = $this->googleMeetingService->createMeeting($user, $meetingData);

                // Guardar el enlace en la cita
                $cita->update(['meeting_url' => $meetingUrl]);
            } catch (\Exception $e) {
                return back()->with('error', 'Error al generar el enlace de Google Meet: ' . $e->getMessage());
            }
        }

        // Si el estado es 'reservada', marcar como 'en_atencion' automáticamente al unirse
        if ($cita->estado === Cita::STATUS_RESERVADA) {
            $cita->update([
                'estado' => Cita::STATUS_ATTENTION,
                'atendido_at' => now(),
            ]);
        }

        return redirect($cita->meeting_url);
    }

    /**
     * Guardar nuevos bloques de disponibilidad.
     */
    public function storeSchedule(Request $request)
    {
        $user = $request->user();
        $especialista = $user->especialista;
        
        if (!$especialista) {
            $especialista = \App\Models\Especialista::create([
                'usuario_id' => $user->id,
                'especialidad' => 'Psicología General',
                'biografia' => 'Perfil profesional en construcción.',
            ]);
        }
        
        $validated = $request->validate([
            'fecha' => 'required|date|after_or_equal:today',
            'hora_inicio' => 'required',
            'hora_fin' => 'required',
        ], [
            'fecha.after_or_equal' => 'La fecha debe ser hoy o futura.',
        ]);

        // Validación manual de horas
        if (strtotime($request->hora_fin) <= strtotime($request->hora_inicio)) {
            return back()->withErrors(['hora_fin' => 'La hora de fin debe ser posterior a la de inicio.'])->withInput();
        }

        $this->especialistaService->createAvailabilitySlots($especialista->id, [$validated]);

        return back()->with('success', 'Horario añadido correctamente.');
    }

    /**
     * Eliminar un bloque de horario.
     */
    public function deleteSchedule(Request $request, int $id)
    {
        $especialista = $request->user()->especialista;
        $this->especialistaService->removeAvailabilitySlot($especialista->id, $id);

        return back()->with('success', 'Horario eliminado.');
    }

    /**
     * Guardar un nuevo horario semanal recurrente.
     */
    public function storeWeeklySchedule(Request $request)
    {
        $user = $request->user();
        $especialista = $user->especialista;

        $validated = $request->validate([
            'dia_semana' => 'required|integer|between:0,6',
            'hora_inicio' => 'required',
            'hora_fin' => 'required',
        ]);

        if (strtotime($request->hora_fin) <= strtotime($request->hora_inicio)) {
            return back()->withErrors(['hora_fin' => 'La hora de fin debe ser posterior a la de inicio.'])->withInput();
        }

        $especialista->horariosSemanales()->create($validated);

        return back()->with('success', 'Horario semanal guardado correctamente.');
    }

    /**
     * Eliminar un horario semanal recurrente.
     */
    public function deleteWeeklySchedule(Request $request, int $id)
    {
        $especialista = $request->user()->especialista;
        $especialista->horariosSemanales()->where('id', $id)->delete();

        return back()->with('success', 'Horario semanal eliminado.');
    }

    /**
     * Mostrar vista de configuración de servicios.
     */
    public function services(Request $request): \Illuminate\View\View
    {
        $especialista = $request->user()->especialista;
        $serviciosDisponibles = \App\Models\Servicio::active()->get();
        $misServiciosIds = $especialista->servicios->pluck('id')->toArray();

        return view('doctor.services', compact('serviciosDisponibles', 'misServiciosIds'));
    }

    /**
     * Actualizar servicios que ofrece el especialista.
     */
    public function updateServices(Request $request)
    {
        $especialista = $request->user()->especialista;
        $validated = $request->validate([
            'servicios' => 'nullable|array',
            'servicios.*' => 'exists:servicios,id',
        ]);

        $this->especialistaService->updateEspecialistaServices($especialista->id, $validated['servicios'] ?? []);

        return back()->with('success', 'Servicios actualizados correctamente.');
    }

    /**
     * Actualizar el estado de una cita.
     */
    public function updateStatus(Request $request, Cita $cita)
    {
        $especialista = $request->user()->especialista;

        // Validar que la cita pertenece al especialista
        if ($cita->especialista_id !== $especialista->id) {
            abort(403, 'No tienes permiso para actualizar esta cita.');
        }

        $validated = $request->validate([
            'estado' => 'required|string|in:confirmada,en_atencion,realizada,cancelada,no_asistio',
            'motivo_cancelacion' => 'nullable|string|max:500',
        ]);

        $nuevoEstado = $validated['estado'];

        // El CitaObserver se encargará de liberar los slots si el estado cambia a 'cancelada'
        
        $updateData = [
            'estado' => $nuevoEstado,
            'motivo_cancelacion' => $validated['motivo_cancelacion'] ?? $cita->motivo_cancelacion,
        ];

        // Timestamps y flags de flujo
        if ($nuevoEstado === Cita::STATUS_ATTENTION && !$cita->atendido_at) {
            $updateData['atendido_at'] = now();
        }

        if ($nuevoEstado === Cita::STATUS_COMPLETED && !$cita->finalizado_at) {
            $updateData['finalizado_at'] = now();
        }

        if ($nuevoEstado === Cita::STATUS_NO_SHOW && !$cita->finalizado_at) {
            $updateData['finalizado_at'] = now();
        }

        if ($nuevoEstado === Cita::STATUS_CANCELLED) {
            $updateData['cancelado_por_especialista'] = true;
        }

        $cita->update($updateData);

        // Notificar al paciente si el especialista canceló la cita
        if ($nuevoEstado === Cita::STATUS_CANCELLED) {
            \App\Models\Notificacion::crear(
                $cita->paciente_id,
                \App\Models\Notificacion::TIPO_CANCELACION,
                'Cita cancelada',
                'Tu especialista canceló la cita.' . (filled($updateData['motivo_cancelacion'] ?? null) ? ' Motivo: ' . $updateData['motivo_cancelacion'] : ''),
                route('portal.index', [], false) . '#actividad'
            );
        }

        $mensaje = match($nuevoEstado) {
            Cita::STATUS_ATTENTION => 'La sesión ha comenzado.',
            Cita::STATUS_COMPLETED => 'Sesión marcada como realizada.',
            Cita::STATUS_CANCELLED => 'Cita cancelada correctamente.',
            Cita::STATUS_NO_SHOW   => 'Cita marcada como inasistencia (no asistió).',
            default => 'Estado actualizado correctamente.',
        };

        return back()->with('success', $mensaje);
    }

    /**
     * Mostrar vista de edición de perfil.
     */
    public function profile(Request $request): \Illuminate\View\View
    {
        $especialista = $request->user()->especialista;
        return view('doctor.profile', compact('especialista'));
    }

    /**
     * Actualizar los datos del perfil del especialista.
     */
    public function updateProfile(Request $request)
    {
        $especialista = $request->user()->especialista;

        $validated = $request->validate([
            'especialidad' => 'required|string|max:255',
            'biografia'    => 'required|string|max:2000',
            'foto'         => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('foto')) {
            // Eliminar foto anterior si existe
            if ($especialista->foto && !\Illuminate\Support\Str::startsWith($especialista->foto, 'http')) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($especialista->foto);
            }

            $path = $request->file('foto')->store('specialists/photos', 'public');
            $especialista->foto = $path;
        }

        $especialista->especialidad = $validated['especialidad'];
        $especialista->biografia = $validated['biografia'];
        $especialista->save();

        return back()->with('success', 'Perfil actualizado correctamente.');
    }

    /**
     * Marcar todas las notificaciones del doctor como leídas.
     */
    public function marcarNotificacionesLeidas(Request $request): \Illuminate\Http\RedirectResponse
    {
        \App\Models\Notificacion::where('usuario_id', $request->user()->id)
            ->where('leida', false)
            ->update(['leida' => true]);

        return back();
    }

    /**
     * Espacio de sesión: gestión de tareas del paciente y resumen de la cita.
     */
    public function sessionWorkspace(Request $request, \App\Models\Cita $cita): \Illuminate\View\View
    {
        $especialista = $request->user()->especialista;

        if (!$especialista || $cita->especialista_id !== $especialista->id) {
            abort(403, 'No tienes acceso a esta sesión.');
        }

        $cita->load(['paciente', 'disponibilidad', 'especialista.usuario']);
        $tareas = $cita->tareas()->latest()->get();

        return view('doctor.session', compact('cita', 'tareas'));
    }

    /**
     * Crear una tarea para el paciente de la cita.
     */
    public function storeTask(Request $request, \App\Models\Cita $cita)
    {
        $especialista = $request->user()->especialista;

        if (!$especialista || $cita->especialista_id !== $especialista->id) {
            abort(403);
        }

        $validated = $request->validate([
            'titulo'      => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:2000',
        ]);

        \App\Models\TareaPaciente::create([
            'especialista_id' => $especialista->id,
            'paciente_id'     => $cita->paciente_id,
            'cita_id'         => $cita->id,
            'titulo'          => $validated['titulo'],
            'descripcion'     => $validated['descripcion'] ?? null,
        ]);

        \App\Models\Notificacion::crear(
            $cita->paciente_id,
            \App\Models\Notificacion::TIPO_TAREA,
            'Nueva tarea asignada',
            'Tu especialista te asignó la tarea: “' . $validated['titulo'] . '”.',
            route('portal.index', [], false) . '#tareas'
        );

        return back()->with('success', 'Tarea asignada al paciente.');
    }

    /**
     * Eliminar una tarea creada por el especialista.
     */
    public function deleteTask(Request $request, \App\Models\TareaPaciente $tarea)
    {
        $especialista = $request->user()->especialista;

        if (!$especialista || $tarea->especialista_id !== $especialista->id) {
            abort(403);
        }

        $tarea->delete();

        return back()->with('success', 'Tarea eliminada.');
    }

    /**
     * Guardar el resumen de la sesión y si se comparte con el paciente.
     */
    public function saveResumen(Request $request, \App\Models\Cita $cita)
    {
        $especialista = $request->user()->especialista;

        if (!$especialista || $cita->especialista_id !== $especialista->id) {
            abort(403);
        }

        $validated = $request->validate([
            'resumen_sesion'     => 'nullable|string|max:5000',
            'resumen_compartido' => 'nullable|boolean',
        ]);

        $cita->resumen_sesion     = $validated['resumen_sesion'] ?? null;
        $cita->resumen_compartido = $request->boolean('resumen_compartido');
        $cita->save();

        return back()->with('success', 'Resumen de la sesión guardado.');
    }

    /**
     * Aprobar una solicitud de reprogramación y mover la cita al horario pedido.
     */
    public function approveSolicitud(Request $request, \App\Models\SolicitudReprogramacion $solicitud)
    {
        $especialista = $request->user()->especialista;

        if (!$especialista || $solicitud->especialista_id !== $especialista->id) {
            abort(403);
        }

        if (!$solicitud->estaPendiente()) {
            return back()->with('error', 'Esta solicitud ya fue respondida.');
        }

        try {
            app(\App\Services\Booking\BookingService::class)->rescheduleAppointment($solicitud->cita, [
                'disponibilidad_id' => $solicitud->slot_id,
            ]);

            $solicitud->update([
                'estado'        => \App\Models\SolicitudReprogramacion::ESTADO_APROBADA,
                'respondida_at' => now(),
            ]);

            \App\Models\Notificacion::crear(
                $solicitud->paciente_id,
                \App\Models\Notificacion::TIPO_REPROGRAMACION,
                'Reprogramación aprobada',
                'Tu solicitud de cambio de horario fue aprobada para el ' .
                    $solicitud->fecha_solicitada->locale('es')->isoFormat('D [de] MMMM') . ' a las ' .
                    \Carbon\Carbon::parse($solicitud->hora_solicitada)->format('h:i A') . '.',
                route('portal.index', [], false) . '#solicitudes'
            );

            // Notificar al solicitante si es distinto del paciente (por ejemplo, recepcionista)
            if ($solicitud->solicitante_usuario_id) {
                $url = $solicitud->solicitante === 'recepcionista' ? route('receptionist.dashboard', [], false) : route('portal.index', [], false) . '#solicitudes';
                \App\Models\Notificacion::crear(
                    $solicitud->solicitante_usuario_id,
                    \App\Models\Notificacion::TIPO_REPROGRAMACION,
                    'Solicitud aprobada',
                    'La solicitud de reprogramación para la cita #' . $solicitud->cita_id . ' fue aprobada.',
                    $url
                );
            }

            // Notificar a administradores
            $adminIds = \App\Models\Usuario::whereHas('rol', function($q){ $q->where('nombre', \App\Models\Usuario::ROL_ADMIN); })->pluck('id');
            foreach ($adminIds as $adminId) {
                \App\Models\Notificacion::crear(
                    $adminId,
                    \App\Models\Notificacion::TIPO_REPROGRAMACION,
                    'Solicitud aprobada',
                    'Se aprobó una solicitud de reprogramación para la cita #' . $solicitud->cita_id . '.',
                    route('admin.dashboard', [], false)
                );
            }

            return back()->with('success', 'Solicitud aprobada. La cita se reprogramó al nuevo horario.');
        } catch (\Exception $e) {
            return back()->with('error', 'No se pudo reprogramar: ' . $e->getMessage());
        }
    }

    /**
     * Rechazar una solicitud de reprogramación.
     */
    public function rejectSolicitud(Request $request, \App\Models\SolicitudReprogramacion $solicitud)
    {
        $especialista = $request->user()->especialista;

        if (!$especialista || $solicitud->especialista_id !== $especialista->id) {
            abort(403);
        }

        if (!$solicitud->estaPendiente()) {
            return back()->with('error', 'Esta solicitud ya fue respondida.');
        }

        $validated = $request->validate([
            'respuesta_especialista' => 'nullable|string|max:1000',
        ]);

        $solicitud->update([
            'estado'                 => \App\Models\SolicitudReprogramacion::ESTADO_RECHAZADA,
            'respuesta_especialista' => $validated['respuesta_especialista'] ?? null,
            'respondida_at'          => now(),
        ]);

        \App\Models\Notificacion::crear(
            $solicitud->paciente_id,
            \App\Models\Notificacion::TIPO_REPROGRAMACION,
            'Reprogramación rechazada',
            'Tu solicitud de cambio de horario fue rechazada.' .
                (filled($validated['respuesta_especialista'] ?? null) ? ' Motivo: ' . $validated['respuesta_especialista'] : ''),
            route('portal.index', [], false) . '#solicitudes'
        );

        // Notificar al solicitante si fue otra persona (por ejemplo recepcionista)
        if ($solicitud->solicitante_usuario_id) {
            $url = $solicitud->solicitante === 'recepcionista' ? route('receptionist.dashboard', [], false) : route('portal.index', [], false) . '#solicitudes';
            \App\Models\Notificacion::crear(
                $solicitud->solicitante_usuario_id,
                \App\Models\Notificacion::TIPO_REPROGRAMACION,
                'Solicitud rechazada',
                'La solicitud de reprogramación para la cita #' . $solicitud->cita_id . ' fue rechazada.' .
                    (filled($validated['respuesta_especialista'] ?? null) ? ' Motivo: ' . $validated['respuesta_especialista'] : ''),
                $url
            );
        }

        // Notificar a administradores
        $adminIds = \App\Models\Usuario::whereHas('rol', function($q){ $q->where('nombre', \App\Models\Usuario::ROL_ADMIN); })->pluck('id');
        foreach ($adminIds as $adminId) {
            \App\Models\Notificacion::crear(
                $adminId,
                \App\Models\Notificacion::TIPO_REPROGRAMACION,
                'Solicitud rechazada',
                'Se rechazó una solicitud de reprogramación para la cita #' . $solicitud->cita_id . '.',
                route('admin.dashboard', [], false)
            );
        }

        return back()->with('success', 'Solicitud rechazada.');
    }
}
