<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cita;
use App\Models\Disponibilidad;
use App\Models\Usuario;
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
            'nombre'            => 'required|string|max:255',
            'email'             => 'required|email|max:255',
            'telefono'          => 'nullable|string|max:255',
            'especialista_id'   => 'required|exists:especialistas,id',
            'disponibilidad_id' => 'required', // Es el ID del slot dinámico (no existe aún en la DB)
            'cantidad_sesiones' => 'required|integer|min:1',
            'modalidad_id'      => 'required|exists:modalidades,id',
            'servicio_id'       => 'required|exists:servicios,id',
        ]);

        // 1. Si no hay paciente_id, lo buscamos o creamos
        if (empty($validated['paciente_id'])) {
            $paciente = $this->receptionistService->searchOrCreatePatient([
                'nombre' => $validated['nombre'],
                'email' => $validated['email'],
                'telefono' => $validated['telefono'],
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
        $pacientes = Usuario::where('rol_id', 4) // Rol paciente (según lo solicitado)
            ->where(function($q) use ($query) {
                $q->where('nombre', 'LIKE', "%{$query}%")
                  ->orWhere('email', 'LIKE', "%{$query}%")
                  ->orWhere('telefono', 'LIKE', "%{$query}%");
            })
            ->get(['id', 'nombre', 'email', 'telefono']);

        return response()->json($pacientes);
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

        // Filtrar por disponibilidad en los próximos 30 días
        $filtered = $especialistas->filter(function($esp) use ($sessions, $duracion) {
            return $this->medicalBookingService->hasAvailabilityInWindow($esp->id, $sessions, $duracion, 30);
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
        // Solo se pueden reprogramar citas activas.
        if (!in_array($cita->estado, [Cita::STATUS_RESERVADA, Cita::STATUS_RESCHEDULED])) {
            return redirect()->route('receptionist.dashboard')
                ->with('error', 'Solo se pueden reprogramar citas reservadas o ya reprogramadas.');
        }

        $cita->load('disponibilidad');

        return redirect()->route('booking.schedule', [
            'reschedule_id'   => $cita->id,
            'especialista_id' => $cita->especialista_id,
            'sessions'        => $cita->cantidad_sesiones ?? 1,
            'duracion'        => $cita->duracion_minutos ?? 60,
            'modalidad_id'    => $cita->modalidad_id,
            'fecha'           => optional($cita->disponibilidad)->fecha
                ? Carbon::parse($cita->disponibilidad->fecha)->toDateString()
                : Carbon::today()->toDateString(),
        ]);
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
