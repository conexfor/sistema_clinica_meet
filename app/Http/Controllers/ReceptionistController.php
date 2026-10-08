<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cita;
use App\Models\Disponibilidad;
use App\Models\Usuario;
use App\Models\Rol;
use App\Models\Especialista;
use App\Models\Modalidad;
use App\Models\Servicio;
use Carbon\Carbon;

class ReceptionistController extends Controller
{
    public function __construct(
        protected \App\Services\Reception\ReceptionistService $receptionistService,
        protected \App\Services\Booking\MedicalBookingService $medicalBookingService
    ) {}

    /**
     * Muestra el panel de recepción con reportes filtrables.
     */
    public function dashboard(Request $request): \Illuminate\View\View
    {
        $filters = $request->only(['especialista_id', 'estado', 'fecha_desde', 'fecha_hasta', 'disponibles_hoy']);
        
        $reportes = $this->receptionistService->getReports($filters);
        $totalGeneral = $this->receptionistService->getGeneralTotal($filters);
        $stats = $this->receptionistService->getDashboardStats($request->query('period', 'month'));
        
        $especialistas = \App\Models\Especialista::with('usuario')->get();
        $estados = [
            Cita::STATUS_RESERVADA => 'Reservada',
            Cita::STATUS_ATTENTION => 'En atención',
            Cita::STATUS_COMPLETED => 'Realizada',
            Cita::STATUS_RESCHEDULED => 'Reprogramada',
            Cita::STATUS_CANCELLED => 'Cancelada',
        ];

        return view('receptionist.dashboard', compact('reportes', 'totalGeneral', 'especialistas', 'estados', 'stats'));
    }

    /**
     * Vista para crear una nueva reserva.
     */
    public function createBooking(): \Illuminate\View\View
    {
        $especialistas = \App\Models\Especialista::with('usuario')->get();
        $modalidades = \App\Models\Modalidad::all();
        $servicios = \App\Models\Servicio::active()->with('modalidades')->get();
        
        return view('receptionist.booking.create', compact('especialistas', 'modalidades', 'servicios'));
    }

    /**
     * Procesar la creación de la reserva.
     */
    public function storeBooking(Request $request)
    {
        $validated = $request->validate([
            'paciente_id'       => 'nullable|exists:usuarios,id',
            'patient_type'      => 'required|in:simple,account',
            'nombre'            => 'required|string|max:255',
            'email'             => 'nullable|email|max:255',
            'telefono'          => 'required|string|max:255',
            'password'          => 'nullable|string|min:8|confirmed',
            'especialista_id'   => 'required|exists:especialistas,id',
            'disponibilidad_id' => 'required', // Es el ID del slot dinámico (no existe aún en la DB)
            'cantidad_sesiones' => 'required|integer|min:1',
            'modalidad_id'      => 'required|exists:modalidades,id',
            'servicio_id'       => 'required|exists:servicios,id',
        ]);

        if ($validated['patient_type'] === 'account') {
            $request->validate([
                'email' => 'required|email|max:255|unique:usuarios,email',
                'password' => 'required|string|min:8|confirmed',
            ]);
        }

        // 1. Si no hay paciente_id, verificamos si puede registrar pacientes.
        if (empty($validated['paciente_id'])) {
            if (!$request->user()->hasAction('register_patients')) {
                abort(403, 'Acceso denegado: No tienes permiso para registrar nuevos pacientes.');
            }

            $paciente = $this->receptionistService->searchOrCreatePatient([
                'patient_type' => $validated['patient_type'],
                'nombre' => $validated['nombre'],
                'email' => $validated['email'] ?? null,
                'telefono' => $validated['telefono'],
                'password' => $validated['password'] ?? null,
            ]);
            $validated['paciente_id'] = $paciente->id;
        } else {
            // Actualizar datos si el paciente ya existe y se editaron en el formulario
            $usuario = Usuario::find($validated['paciente_id']);
            if ($usuario) {
                $usuario->update([
                    'nombre' => $validated['nombre'],
                    'email' => $validated['email'],
                    'telefono' => $validated['telefono'],
                ]);
            }
        }

        // En lugar de agendar directamente, llevamos al checkout
        $params = [
            'especialista_id'   => $validated['especialista_id'],
            'availability_id'   => $validated['disponibilidad_id'],
            'servicio_id'       => $validated['servicio_id'],
            'modalidad_id'      => $validated['modalidad_id'],
            'sessions'          => $validated['cantidad_sesiones'],
            'paciente_id'       => $validated['paciente_id'],
        ];

        return redirect()->to(route('booking.checkout', $params));
    }

    /**
     * API para buscar pacientes (para el select dinámico)
     */
    public function searchPatients(Request $request)
    {
        $query = $request->get('q');
        $patientRoleId = Rol::where('nombre', Usuario::ROL_PATIENT)->value('id');
        $pacientes = Usuario::where('rol_id', $patientRoleId)
            ->where(function($q) use ($query) {
                $q->where('nombre', 'LIKE', "%{$query}%")
                  ->orWhere('email', 'LIKE', "%{$query}%")
                  ->orWhere('telefono', 'LIKE', "%{$query}%");
            })
            ->get(['id', 'nombre', 'email', 'telefono']);

        return response()->json($pacientes);
    }

    /**
     * Guarda un paciente nuevo desde el modal de recepción.
     */
    public function storePatient(Request $request)
    {
        $validated = $request->validate([
            'patient_type' => 'required|in:simple,account',
            'nombre'       => 'required|string|max:255',
            'telefono'     => 'required|string|max:255',
            'email'        => 'nullable|email|max:255',
            'password'     => 'nullable|string|min:8|confirmed',
        ]);

        if ($validated['patient_type'] === 'account') {
            $request->validate([
                'email' => 'required|email|max:255|unique:usuarios,email',
                'password' => 'required|string|min:8|confirmed',
            ]);
        }

        if (!$request->user()->hasAction('register_patients')) {
            abort(403, 'Acceso denegado: No tienes permiso para registrar nuevos pacientes.');
        }

        $paciente = $this->receptionistService->searchOrCreatePatient([
            'patient_type' => $validated['patient_type'],
            'nombre'       => $validated['nombre'],
            'email'        => $validated['email'] ?? null,
            'telefono'     => $validated['telefono'],
            'password'     => $validated['password'] ?? null,
        ]);

        return response()->json([
            'id' => $paciente->id,
            'nombre' => $paciente->nombre,
            'email' => $paciente->email,
            'telefono' => $paciente->telefono,
        ]);
    }

    /**
     * API para filtrar especialistas por servicio y disponibilidad mensual.
     */
    public function getFilteredEspecialistas(Request $request)
    {
        $servicioId = $request->get('servicio_id');
        $sessions = (int)$request->get('sessions', 1);

        if (!$servicioId) {
            return response()->json([]);
        }

        $servicio = Servicio::findOrFail($servicioId);
        $duracion = $servicio->duracion ?? 60;

        // Filtrar especialistas que ofrecen el servicio
        $especialistas = Especialista::whereHas('servicios', function($q) use ($servicioId) {
            $q->where('servicios.id', $servicioId);
        })->with('usuario')->get();

        // El filtro no debe limitarse al día actual: un especialista debe aparecer si
        // tiene al menos un bloque disponible en cualquier fecha futura dentro de la
        // ventana de reserva, para que el recepcionista o paciente pueda elegir un
        // horario posterior (por ejemplo, el lunes siguiente) con el servicio seleccionado.
        $filtered = $especialistas->filter(function($esp) use ($sessions, $duracion) {
            return $this->medicalBookingService->hasAvailabilityInWindow(
                $esp->id,
                $sessions,
                $duracion,
                60
            );
        })->map(function($esp) {
            return [
                'id' => $esp->id,
                'nombre' => $esp->usuario->nombre,
                'especialidad' => $esp->especialidad,
            ];
        })->values();

        return response()->json($filtered);
    }

    /**
     * API que devuelve las fechas con disponibilidad de un especialista dentro de
     * un mes concreto. Se usa para pintar el calendario del formulario de reserva,
     * marcando únicamente los días que el médico tiene libres (igual que el flujo
     * de reserva del cliente).
     */
    public function getAvailableDates(Request $request)
    {
        $espId = $request->get('especialista_id');
        $mes = $request->get('mes'); // YYYY-MM-DD (cualquier día del mes de referencia)
        $sessions = (int) $request->get('sessions', 1);
        $servicioId = $request->get('servicio_id');

        if (!$espId || !$mes) {
            return response()->json(['dates' => []]);
        }

        $servicio = Servicio::find($servicioId);
        $duracion = $servicio ? ($servicio->duracion ?? 60) : 60;

        $dates = $this->medicalBookingService->getAvailableDates(
            (int) $espId,
            $mes,
            $sessions,
            $duracion
        );

        return response()->json(['dates' => $dates]);
    }

    /**
     * Iniciar la reprogramación de una cita desde recepción.
     * Reutiliza el flujo de reserva (horario -> checkout -> store) conservando
     * el mismo especialista y arrastrando el reschedule_id.
     */
    public function reschedule(Cita $cita)
    {
        // Legacy redirect kept for compatibility. Use rescheduleCreate instead.
        return $this->rescheduleCreate(request(), $cita);
    }

    /**
     * Mostrar el selector de horario para solicitar una reprogramación (desde recepción).
     */
    public function rescheduleCreate(Request $request, Cita $cita)
    {
        // Solo se pueden reprogramar citas activas.
        if (!in_array($cita->estado, [Cita::STATUS_RESERVADA, Cita::STATUS_RESCHEDULED, Cita::STATUS_ATTENTION])) {
            return redirect()->route('receptionist.dashboard')
                ->with('error', 'Solo se pueden reprogramar citas reservadas o ya reprogramadas.');
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

        // Pass any existing pending solicitud so the form can prefill 'solicitante'
        $pending = $cita->solicitudesReprogramacion()->where('estado', \App\Models\SolicitudReprogramacion::ESTADO_PENDIENTE)->latest()->first();

        // Reuse portal.reschedule view but post to receptionist store route
        $formAction = route('receptionist.booking.reschedule.store', ['cita' => $cita->id]);

        return view('portal.reschedule', compact('cita', 'availability', 'availableDates', 'fecha', 'formAction', 'pending'));
    }

    /**
     * Registrar la solicitud de reprogramación creada por la recepción.
     */
    public function rescheduleStore(Request $request, Cita $cita)
    {
        // Permission: receptionist middleware ensures actor
        // No owner check: receptionist can act for any paciente

        if (!in_array($cita->estado, [Cita::STATUS_RESERVADA, Cita::STATUS_RESCHEDULED, Cita::STATUS_ATTENTION])) {
            return redirect()->route('receptionist.dashboard')->with('error', 'Esta cita no se puede reprogramar.');
        }

        $validated = $request->validate([
            'slot_id'          => 'required|string',
            'fecha_solicitada' => 'required|date',
            'hora_solicitada'  => 'required',
            'motivo'           => 'nullable|string|max:1000',
            'solicitante'      => 'nullable|string|in:recepcionista,paciente',
        ], [
            'slot_id.required' => 'Selecciona un horario disponible.',
        ]);

        $existing = $cita->solicitudesReprogramacion()->where('estado', \App\Models\SolicitudReprogramacion::ESTADO_PENDIENTE)->latest()->first();

        if ($existing) {
            if ((string)$existing->slot_id === (string)$validated['slot_id']) {
                return redirect()->route('receptionist.dashboard')->with('info', 'Ya existe una solicitud pendiente para ese horario.');
            }

            // Update existing solicitud with new horario and solicitante
            $existing->update([
                'slot_id' => $validated['slot_id'],
                'fecha_solicitada' => $validated['fecha_solicitada'],
                'hora_solicitada' => $validated['hora_solicitada'],
                'motivo' => $validated['motivo'] ?? $existing->motivo,
                'solicitante' => $validated['solicitante'] ?? $existing->solicitante,
                'solicitante_usuario_id' => $request->user()->id,
            ]);

            // Notify specialist about update
            if ($cita->especialista?->usuario_id) {
                \App\Models\Notificacion::crear(
                    $cita->especialista->usuario_id,
                    \App\Models\Notificacion::TIPO_REPROGRAMACION,
                    'Solicitud de reprogramación actualizada',
                    ($request->user()->nombre ?? 'Recepción') . ' actualizó la solicitud de cambio para la cita ' .
                        ' al ' . \Carbon\Carbon::parse($validated['fecha_solicitada'])->locale('es')->isoFormat('D [de] MMMM') . ' a las ' .
                        \Carbon\Carbon::parse($validated['hora_solicitada'])->format('h:i A') . '.',
                    route('doctor.dashboard', [], false)
                );
            }

            // Notify admins about update
            $adminIds = \App\Models\Usuario::whereHas('rol', function($q){ $q->where('nombre', \App\Models\Usuario::ROL_ADMIN); })->pluck('id');
            foreach ($adminIds as $adminId) {
                \App\Models\Notificacion::crear(
                    $adminId,
                    \App\Models\Notificacion::TIPO_REPROGRAMACION,
                    'Solicitud de reprogramación actualizada',
                    'Se actualizó una solicitud de reprogramación para la cita #' . $cita->id . '.',
                    route('admin.dashboard', [], false)
                );
            }

            return redirect()->route('receptionist.dashboard')->with('success', 'Solicitud actualizada y enviada al especialista.');
        }

        $sol = \App\Models\SolicitudReprogramacion::create([
            'cita_id'          => $cita->id,
            'paciente_id'      => $cita->paciente_id,
            'especialista_id'  => $cita->especialista_id,
            'slot_id'          => $validated['slot_id'],
            'fecha_solicitada' => $validated['fecha_solicitada'],
            'hora_solicitada'  => $validated['hora_solicitada'],
            'motivo'           => $validated['motivo'] ?? null,
            'estado'           => \App\Models\SolicitudReprogramacion::ESTADO_PENDIENTE,
            'solicitante'      => $validated['solicitante'] ?? 'recepcionista',
            'solicitante_usuario_id' => $request->user()->id,
        ]);

        // Notificar al especialista
        if ($cita->especialista?->usuario_id) {
            \App\Models\Notificacion::crear(
                $cita->especialista->usuario_id,
                \App\Models\Notificacion::TIPO_REPROGRAMACION,
                'Nueva solicitud de reprogramación',
                ($request->user()->nombre ?? 'Recepción') . ' solicitó reprogramar la cita para el ' .
                    \Carbon\Carbon::parse($sol->fecha_solicitada)->locale('es')->isoFormat('D [de] MMMM') . ' a las ' .
                    \Carbon\Carbon::parse($sol->hora_solicitada)->format('h:i A') . '.',
                route('doctor.dashboard', [], false)
            );
        }

        // Notificar admins
        $adminIds = \App\Models\Usuario::whereHas('rol', function($q){ $q->where('nombre', \App\Models\Usuario::ROL_ADMIN); })->pluck('id');
        foreach ($adminIds as $adminId) {
            \App\Models\Notificacion::crear(
                $adminId,
                \App\Models\Notificacion::TIPO_REPROGRAMACION,
                'Solicitud de reprogramación creada',
                'Se creó una solicitud de reprogramación por recepción para la cita #' . $cita->id . '.',
                route('admin.dashboard', [], false)
            );
        }

        return redirect()->route('receptionist.dashboard')->with('success', 'Solicitud de reprogramación creada y enviada al especialista para su confirmación.');
    }

    /**
     * Marcar todas las notificaciones del recepcionista como leídas.
     */
    public function marcarNotificacionesLeidas(Request $request): \Illuminate\Http\RedirectResponse
    {
        \App\Models\Notificacion::where('usuario_id', $request->user()->id)
            ->where('leida', false)
            ->update(['leida' => true]);

        return back();
    }

    /**
     * API para buscar disponibilidad (para el flujo de reserva)
     */
    public function getAvailability(Request $request)
    {
        $espId = $request->get('especialista_id');
        $fecha = $request->get('fecha');
        $sessions = (int)$request->get('sessions', 1);
        $servicioId = $request->get('servicio_id');

        // Obtener duración del servicio
        $servicio = \App\Models\Servicio::find($servicioId);
        $duracion = $servicio ? ($servicio->duracion ?? 60) : 60;

        $slots = $this->medicalBookingService->getAvailableSlots(
            $espId,
            $fecha,
            'standard',
            $sessions,
            $duracion
        );

        // Aplanar los slots para que sea un array simple de objetos con id y hora_inicio
        $formattedSlots = [];
        foreach ($slots as $group) {
            foreach ($group as $slot) {
                // Solo necesitamos el primer slot del grupo para agendar (ya que MedicalBookingService asegura la secuencia)
                $formattedSlots[] = [
                    'id' => $slot['id'],
                    'hora_inicio' => Carbon::parse($slot['start'])->format('g:i A'),
                    'duracion_min' => $slot['duration']
                ];
            }
        }

        return response()->json($formattedSlots);
    }
}
