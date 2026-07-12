<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

use App\Services\Portal\PortalService;
use App\Services\Booking\MedicalBookingService;
use App\Models\Cita;
use App\Models\TareaPaciente;
use App\Models\SolicitudReprogramacion;
use Illuminate\Http\RedirectResponse;

class CustomerPortalController extends Controller
{
    public function __construct(
        protected PortalService $portalService,
        protected MedicalBookingService $medicalBookingService
    ) {}

    /**
     * Mostrar el dashboard del portal del paciente.
     */
    public function index(Request $request): View
    {
        $pacienteId = $request->user()->id;
        
        // Obtenemos citas activas en orden ASC. La cita "reprogramada" es la vieja
        // (ya reemplazada por la nueva), así que NO se considera activa.
        $appointments = $this->portalService->getPatientAppointments($pacienteId, null, [
            \App\Models\Cita::STATUS_RESERVADA,
            \App\Models\Cita::STATUS_ATTENTION
        ], 'asc', true);
        
        // Obtenemos las últimas 3 sesiones (de cualquier estado) en orden DESC para el listado del dashboard
        $recentHistory = $this->portalService->getPatientAppointments($pacienteId, 3, [
            \App\Models\Cita::STATUS_RESERVADA, 
            \App\Models\Cita::STATUS_RESCHEDULED, 
            \App\Models\Cita::STATUS_ATTENTION, 
            \App\Models\Cita::STATUS_COMPLETED, 
            \App\Models\Cita::STATUS_CANCELLED
        ], 'desc');
        
        $stats = $this->portalService->getDashboardStats($pacienteId);

        // Tareas asignadas por el especialista (pendientes primero)
        $tareas = TareaPaciente::where('paciente_id', $pacienteId)
            ->with('especialista.usuario')
            ->orderBy('completada')
            ->latest()
            ->get();

        // Solicitudes de reprogramación recientes del paciente (para mostrar su estado)
        $solicitudes = SolicitudReprogramacion::where('paciente_id', $pacienteId)
            ->with(['cita.disponibilidad', 'cita.especialista.usuario'])
            ->latest()
            ->take(5)
            ->get();

        // Registro de ánimo (una sola vez por sesión gracias a los campos null):
        //  1) Prioridad: sesión recién finalizada por el doctor -> ánimo FINAL.
        //  2) Si no, sesión de hoy en curso/próxima -> ánimo INICIAL.
        $animoPrompt = null;
        $citaFinal = Cita::where('paciente_id', $pacienteId)
            ->where('estado', Cita::STATUS_COMPLETED)
            ->whereNotNull('finalizado_at')
            ->whereNull('animo_final')
            ->where('finalizado_at', '>=', now()->subDays(2))  // solo sesiones finalizadas recientemente
            ->with('disponibilidad')
            ->orderByDesc('finalizado_at')
            ->first();

        if ($citaFinal) {
            $animoPrompt = ['cita' => $citaFinal, 'momento' => 'final'];
        } else {
            // Ánimo inicial: solo para una cita registrada de hoy que aún no ha terminado.
            $citaInicial = Cita::where('paciente_id', $pacienteId)
                ->whereIn('estado', [Cita::STATUS_RESERVADA, Cita::STATUS_RESCHEDULED, Cita::STATUS_ATTENTION])
                ->whereNull('animo_inicial')
                ->whereHas('disponibilidad', fn ($q) => $q->whereDate('fecha', now()->toDateString()))
                ->with('disponibilidad')
                ->get()
                ->first(fn ($c) => optional($c->disponibilidad) && $c->end_at->isFuture());
            if ($citaInicial) {
                $animoPrompt = ['cita' => $citaInicial, 'momento' => 'inicial'];
            }
        }

        return view('portal.appointments', compact('appointments', 'recentHistory', 'stats', 'tareas', 'solicitudes', 'animoPrompt'));
    }

    /**
     * Mostrar el historial terapéutico completo del paciente.
     */
    public function history(Request $request): View
    {
        $pacienteId = $request->user()->id;
        
        // Obtener historial completo sin límites, mas reciente primero
        $appointments = $this->portalService->getPatientAppointments($pacienteId, null, [
            \App\Models\Cita::STATUS_RESERVADA, 
            \App\Models\Cita::STATUS_RESCHEDULED, 
            \App\Models\Cita::STATUS_ATTENTION, 
            \App\Models\Cita::STATUS_COMPLETED,
            \App\Models\Cita::STATUS_CANCELLED
        ], 'desc');

        // Cargar las tareas de cada cita para mostrarlas junto al resumen compartido
        $appointments->load('tareas');

        return view('portal.history', compact('appointments'));
    }


    /**
     * Mostrar los reportes de soporte del paciente y las respuestas recibidas.
     */
    public function soporte(Request $request): View
    {
        $tickets = \App\Models\TicketSoporte::with('respondidoPor')
            ->where('usuario_id', $request->user()->id)
            ->latest()
            ->get();

        return view('portal.soporte', compact('tickets'));
    }

    /**
     * Mostrar la vista de perfil del paciente con sus datos editables.
     */
    public function profile(Request $request): View
    {
        $usuario = $request->user();

        // Garantizar que exista el perfil de paciente asociado
        $paciente = $usuario->paciente ?: \App\Models\Paciente::create(['usuario_id' => $usuario->id]);

        return view('portal.profile', compact('usuario', 'paciente'));
    }

    /**
     * Actualizar los datos personales del paciente (cuenta + perfil).
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        $usuario = $request->user();

        $validated = $request->validate([
            'nombre'           => 'required|string|max:255',
            'email'            => 'required|email|max:255|unique:usuarios,email,' . $usuario->id,
            'telefono'         => 'nullable|string|max:20',
            'dni'              => 'nullable|string|max:15',
            'fecha_nacimiento' => 'nullable|date|before:today',
            'direccion'        => 'nullable|string|max:255',
            'distrito'         => 'nullable|string|max:120',
            'password'         => 'nullable|string|min:8|confirmed',
        ], [
            'email.unique'            => 'Este correo ya está registrado por otro usuario.',
            'fecha_nacimiento.before' => 'La fecha de nacimiento debe ser anterior a hoy.',
            'password.confirmed'      => 'La confirmación de la contraseña no coincide.',
            'password.min'            => 'La contraseña debe tener al menos 8 caracteres.',
        ]);

        // Datos de la cuenta (tabla usuarios)
        $usuario->nombre   = $validated['nombre'];
        $usuario->email    = $validated['email'];
        $usuario->telefono = $validated['telefono'] ?? null;

        // Cambio de contraseña opcional (el cast 'hashed' la encripta automáticamente)
        if (!empty($validated['password'])) {
            $usuario->password = $validated['password'];
        }

        $usuario->save();

        // Datos del perfil de paciente
        $paciente = $usuario->paciente ?: new \App\Models\Paciente(['usuario_id' => $usuario->id]);
        $paciente->usuario_id       = $usuario->id;
        $paciente->dni              = $validated['dni'] ?? null;
        $paciente->fecha_nacimiento = $validated['fecha_nacimiento'] ?? null;
        $paciente->direccion        = $validated['direccion'] ?? null;
        $paciente->distrito         = $validated['distrito'] ?? null;
        $paciente->save();

        return redirect()->route('portal.profile')->with('success', 'Tus datos se actualizaron correctamente.');
    }

    /**
     * Alternar el estado de completado de una tarea (acción del paciente).
     */
    public function toggleTask(Request $request, TareaPaciente $tarea): RedirectResponse
    {
        if ($tarea->paciente_id !== $request->user()->id) {
            abort(403);
        }

        $tarea->completada = !$tarea->completada;
        $tarea->completada_at = $tarea->completada ? now() : null;
        $tarea->save();

        return back()->with('success', $tarea->completada ? '¡Tarea completada! 🌿' : 'Tarea marcada como pendiente.');
    }

    /**
     * Registrar el estado de ánimo del paciente en su sesión (inicio o fin).
     */
    public function savePatientAnimo(Request $request, Cita $cita): RedirectResponse
    {
        if ($cita->paciente_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'momento' => 'required|in:inicial,final',
            'valor'   => 'required|integer|min:1|max:5',
        ]);

        $cita->{'animo_' . $validated['momento']} = $validated['valor'];
        $cita->save();

        return back()->with('success', 'Gracias por compartir cómo te sientes. 🌿');
    }

    /**
     * Cancelar una cita por parte del paciente (motivo opcional).
     */
    public function cancelAppointment(Request $request, Cita $cita): RedirectResponse
    {
        if ($cita->paciente_id !== $request->user()->id) {
            abort(403);
        }

        if (!in_array($cita->estado, [Cita::STATUS_RESERVADA, Cita::STATUS_ATTENTION])) {
            return back()->with('error', 'Esta cita ya no se puede cancelar.');
        }

        $validated = $request->validate([
            'motivo_cancelacion' => 'nullable|string|max:500',
        ]);

        // El CitaObserver libera el/los slot(s) al pasar a cancelada.
        $cita->update([
            'estado'                     => Cita::STATUS_CANCELLED,
            'motivo_cancelacion'         => $validated['motivo_cancelacion'] ?? null,
            'cancelado_por_especialista' => false,
        ]);

        // Notificar al especialista
        if ($cita->especialista?->usuario_id) {
            \App\Models\Notificacion::crear(
                $cita->especialista->usuario_id,
                \App\Models\Notificacion::TIPO_CANCELACION,
                'Cita cancelada por el paciente',
                ($request->user()->nombre ?? 'Un paciente') . ' canceló su cita.' .
                    (filled($validated['motivo_cancelacion'] ?? null) ? ' Motivo: ' . $validated['motivo_cancelacion'] : ''),
                route('doctor.schedule', [], false)
            );
        }

        return back()->with('success', 'Tu cita fue cancelada.');
    }

    /**
     * Marcar todas las notificaciones del paciente como leídas.
     */
    public function marcarNotificacionesLeidas(Request $request): RedirectResponse
    {
        \App\Models\Notificacion::where('usuario_id', $request->user()->id)
            ->where('leida', false)
            ->update(['leida' => true]);

        return back();
    }

    /**
     * Mostrar el selector de horario para solicitar una reprogramación.
     */
    public function rescheduleCreate(Request $request, Cita $cita): View|RedirectResponse
    {
        if ($cita->paciente_id !== $request->user()->id) {
            abort(403);
        }

        // Solo se pueden reprogramar citas activas (no las ya reprogramadas/canceladas)
        if (!in_array($cita->estado, [Cita::STATUS_RESERVADA, Cita::STATUS_ATTENTION])) {
            return redirect()->route('portal.index')->with('error', 'Esta cita no se puede reprogramar.');
        }

        // Evitar solicitudes duplicadas
        if ($cita->solicitudesReprogramacion()->where('estado', SolicitudReprogramacion::ESTADO_PENDIENTE)->exists()) {
            return redirect()->route('portal.index')->with('info', 'Ya tienes una solicitud de reprogramación pendiente para esta cita.');
        }

        $cita->load('especialista.usuario', 'disponibilidad');

        $fecha            = $request->input('fecha', now()->toDateString());
        $cantidadSesiones = $cita->cantidad_sesiones ?? 1;
        $duracion         = $cita->duracion_minutos ?? 60;

        $availability = $this->medicalBookingService->getAvailableSlots(
            $cita->especialista_id, $fecha, 'standard', $cantidadSesiones, $duracion
        );

        $availableDates = $this->medicalBookingService->getAvailableDates(
            $cita->especialista_id, $fecha, $cantidadSesiones, $duracion
        );

        return view('portal.reschedule', compact('cita', 'availability', 'availableDates', 'fecha'));
    }

    /**
     * Registrar la solicitud de reprogramación con el horario elegido por el paciente.
     */
    public function rescheduleStore(Request $request, Cita $cita): RedirectResponse
    {
        if ($cita->paciente_id !== $request->user()->id) {
            abort(403);
        }

        // No se puede solicitar reprogramar una cita ya reprogramada/cancelada/realizada
        if (!in_array($cita->estado, [Cita::STATUS_RESERVADA, Cita::STATUS_ATTENTION])) {
            return redirect()->route('portal.index')->with('error', 'Esta cita no se puede reprogramar.');
        }

        $validated = $request->validate([
            'slot_id'          => 'required|string',
            'fecha_solicitada' => 'required|date',
            'hora_solicitada'  => 'required',
            'motivo'           => 'nullable|string|max:1000',
        ], [
            'slot_id.required' => 'Selecciona un horario disponible.',
        ]);

        if ($cita->solicitudesReprogramacion()->where('estado', SolicitudReprogramacion::ESTADO_PENDIENTE)->exists()) {
            return redirect()->route('portal.index')->with('info', 'Ya tienes una solicitud pendiente para esta cita.');
        }

        SolicitudReprogramacion::create([
            'cita_id'          => $cita->id,
            'paciente_id'      => $cita->paciente_id,
            'especialista_id'  => $cita->especialista_id,
            'slot_id'          => $validated['slot_id'],
            'fecha_solicitada' => $validated['fecha_solicitada'],
            'hora_solicitada'  => $validated['hora_solicitada'],
            'motivo'           => $validated['motivo'] ?? null,
            'estado'           => SolicitudReprogramacion::ESTADO_PENDIENTE,
        ]);

        // Notificar al especialista
        if ($cita->especialista?->usuario_id) {
            \App\Models\Notificacion::crear(
                $cita->especialista->usuario_id,
                \App\Models\Notificacion::TIPO_REPROGRAMACION,
                'Nueva solicitud de reprogramación',
                ($request->user()->nombre ?? 'Un paciente') . ' solicitó cambiar su cita para el ' .
                    \Carbon\Carbon::parse($validated['fecha_solicitada'])->locale('es')->isoFormat('D [de] MMMM') . ' a las ' .
                    \Carbon\Carbon::parse($validated['hora_solicitada'])->format('h:i A') . '.',
                route('doctor.dashboard', [], false)
            );
        }

        return redirect()->route('portal.index')->with('success', 'Solicitud enviada. Tu especialista la revisará y te confirmará el cambio.');
    }

    /**
     * Guardar la calificación de una cita.
     */
    public function rateAppointment(Request $request, \App\Models\Cita $cita): RedirectResponse
    {
        // Validar que la cita pertenece al paciente y está 'realizada'
        if ($cita->paciente_id !== $request->user()->id) {
            abort(403);
        }

        if ($cita->estado !== \App\Models\Cita::STATUS_COMPLETED) {
            return back()->with('error', 'Solo puedes calificar sesiones finalizadas.');
        }

        $validated = $request->validate([
            'calificacion' => 'required|integer|min:1|max:5',
            'comentario_paciente' => 'nullable|string|max:1000',
        ]);

        $cita->update($validated);

        return back()->with('success', '¡Gracias por tu feedback! Tu opinión nos ayuda a mejorar.');
    }
}
