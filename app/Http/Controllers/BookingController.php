<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Carbon\Carbon; // Added Carbon import

use App\Models\Disponibilidad;
use App\Models\Cita;
use App\Services\Booking\BookingService;
use App\Services\Booking\MedicalBookingService;
use App\Services\Google\GoogleMeetingService;
use App\Http\Requests\StoreBookingRequest;
use Illuminate\Http\RedirectResponse;

class BookingController extends Controller
{
    public function __construct(
        protected BookingService $bookingService,
        protected MedicalBookingService $medicalBookingService,
        protected GoogleMeetingService $googleMeetingService
    ) {}

    /**
     * Paso 1: Seleccionar Servicio y Duración.
     */
    public function selectService(): View
    {
        $servicios = \App\Models\Servicio::active()->with('modalidades')->get();
        return view('booking.service', compact('servicios'));
    }

    /**
     * Registrar al paciente en la lista de espera cuando no hay horario disponible.
     */
    public function joinWaitlist(Request $request, \App\Services\Waitlist\WaitlistService $waitlist): RedirectResponse
    {
        $validated = $request->validate([
            'especialista_id' => 'nullable|exists:especialistas,id',
            'servicio_id'     => 'nullable|exists:servicios,id',
            'modalidad_id'    => 'nullable|exists:modalidades,id',
            'notas'           => 'nullable|string|max:500',
        ]);

        $user = $request->user();

        // Evitar duplicados pendientes del mismo paciente para el mismo especialista.
        $yaEnLista = \App\Models\ListaEspera::where('paciente_id', $user->id)
            ->where('estado', \App\Models\ListaEspera::ESTADO_PENDIENTE)
            ->where('especialista_id', $validated['especialista_id'] ?? null)
            ->exists();

        if ($yaEnLista) {
            return back()->with('success', 'Ya estás en la lista de espera. Te avisaremos cuando haya un cupo.');
        }

        $entry = \App\Models\ListaEspera::create([
            'paciente_id'     => $user->id,
            'nombre'          => $user->nombre,
            'telefono'        => $user->telefono ?? null,
            'email'           => $user->email,
            'especialista_id' => $validated['especialista_id'] ?? null,
            'servicio_id'     => $validated['servicio_id'] ?? null,
            'modalidad_id'    => $validated['modalidad_id'] ?? null,
            'notas'           => $validated['notas'] ?? null,
            'estado'          => \App\Models\ListaEspera::ESTADO_PENDIENTE,
        ]);

        // Avisar a los administradores que un paciente se sumó a la lista de espera.
        $waitlist->notifyNewEntry($entry);

        return back()->with('success', 'Te agregamos a la lista de espera. Te contactaremos cuando se libere un horario.');
    }

    /**
     * Paso 2: Seleccionar Especialista según el servicio.
     */
    public function selectSpecialist(Request $request): View|RedirectResponse
    {
        $servicioId = $request->input('servicio_id');
        $sessions = (int)$request->input('sessions', 1);

        if (!$servicioId) {
            return redirect()->route('booking.service')->with('error', 'Por favor selecciona un servicio.');
        }

        $servicio = \App\Models\Servicio::findOrFail($servicioId);
        $originalEspecialistas = $servicio->especialistas()->with('usuario')->get();
        
        $duracion = $servicio->duracion ?? 60;
        
        // Obtener la modalidad seleccionada para el servicio específico
        $modalidadesSeleccionadas = $request->input('modalidad_id', []);
        $modalidadId = $modalidadesSeleccionadas[$servicioId] ?? null;

        // Filtrar especialistas que tengan al menos un espacio para el bloque solicitado en los próximos 30 días
        $especialistas = $originalEspecialistas->filter(function ($especialista) use ($sessions, $duracion) {
            return $this->medicalBookingService->hasAvailabilityInWindow($especialista->id, $sessions, $duracion, 30);
        });

        return view('booking.specialist', compact('especialistas', 'servicio', 'duracion', 'sessions', 'modalidadId'));
    }

    /**
     * Mostrar la vista de selección de horarios.
     */
    public function schedule(Request $request): View|RedirectResponse
    {
        $fecha = $request->input('fecha', Carbon::today()->toDateString());
        $rescheduleId = $request->input('reschedule_id');
        $especialistaId = $request->input('especialista_id');
        $servicioId = $request->input('servicio_id');
        $duracion = (int)$request->input('duracion', 60);
        
        $tipoConsulta = 'standard';
        $cantidadSesiones = (int)$request->input('sessions', 1);
        $modalidadId = $request->input('modalidad_id');

        if (!$especialistaId) {
            return redirect()->route('booking.service')->with('error', 'Selecciona un profesional.');
        }

        // Obtenemos los slots usando la nueva lógica dinámica
        $availability = $this->medicalBookingService->getAvailableSlots(
            $especialistaId,
            $fecha,
            $tipoConsulta,
            $cantidadSesiones,
            $duracion
        );

        // Verificamos si hay slots individuales en caso de que se busquen múltiples sesiones y no haya resultados
        $hasSingleSlots = false;
        if ($cantidadSesiones > 1) {
            $singleAvailability = $this->medicalBookingService->getAvailableSlots(
                $especialistaId, $fecha, $tipoConsulta, 1
            );
            $hasSingleSlots = collect($singleAvailability)->flatten(1)->isNotEmpty();
        }

        $consultationTypes = MedicalBookingService::CONSULTATION_TYPES;
        $selectedType = $consultationTypes[$tipoConsulta] ?? $consultationTypes['standard'];

        // Obtener todas las fechas con disponibilidad en el mes de la fecha seleccionada
        $availableDates = $this->medicalBookingService->getAvailableDates(
            $especialistaId, $fecha, $cantidadSesiones, $duracion
        );

        // Datos para la captura opt-in de lista de espera (Flujo 2).
        $especialista = \App\Models\Especialista::with('usuario')->find($especialistaId);
        $enEspera = \App\Models\ListaEspera::where('paciente_id', $request->user()->id)
            ->where('especialista_id', $especialistaId)
            ->where('estado', \App\Models\ListaEspera::ESTADO_PENDIENTE)
            ->exists();

        return view('booking.schedule', compact(
            'availability', 
            'fecha', 
            'tipoConsulta', 
            'consultationTypes',
            'selectedType',
            'cantidadSesiones',
            'rescheduleId',
            'hasSingleSlots',
            'especialistaId',
            'servicioId',
            'duracion',
            'modalidadId',
            'availableDates',
            'especialista',
            'enEspera'
        ));
    }

    /**
     * Iniciar el proceso de reprogramación.
     */
    public function reschedule(\App\Models\Cita $cita): RedirectResponse
    {
        // Validar que la cita pertenece al usuario autenticado
        if ($cita->paciente_id !== auth()->id()) {
            abort(403);
        }

        return redirect()->route('booking.schedule', [
            'reschedule_id' => $cita->id,
            'sessions' => $cita->cantidad_sesiones,
            'tipo_consulta' => $cita->tipo_consulta ?? 'standard'
        ]);
    }

    /**
     * Mostrar la vista de registro de paciente.
     */
    public function register(): View
    {
        return view('booking.register');
    }

    /**
     * Mostrar la vista de confirmación y pago.
     */
    public function checkout(Request $request): View|RedirectResponse
    {
        $slotId = $request->input('availability_id');
        $rescheduleId = $request->input('reschedule_id');
        $modalidadId = $request->input('modalidad_id');
        $pacienteId = $request->input('paciente_id');

        if (!$slotId) {
            return redirect()->route('booking.schedule', ['reschedule_id' => $rescheduleId])->with('error', 'Por favor, selecciona un horario primero.');
        }

        try {
            // Materializar el slot si es virtual o dinámico médico
            if (str_starts_with($slotId, 'medical:')) {
                $availability = $this->medicalBookingService->materializeMedicalSlot($slotId);
            } else {
                $availability = $this->bookingService->materializeSlot($slotId);
            }
            
            $availability->load('especialista.usuario');
            
            // Metadatos de la consulta
            $consultationTypes = MedicalBookingService::CONSULTATION_TYPES;
            $typeCode = 'standard';
            $baseDuration = null;
            if (str_starts_with($slotId, 'medical:')) {
                $parts = explode(':', $slotId);
                $typeCode = $parts[3] ?? 'standard';
                if (isset($parts[7])) {
                    $cantidadSesionesArray = (int)$request->input('sessions', 1);
                    $baseDuration = (int)($parts[7] / max(1, $cantidadSesionesArray));
                }
            }
            $selectedType = $consultationTypes[$typeCode] ?? $consultationTypes['standard'];
            if (!$baseDuration) {
                $baseDuration = $selectedType['max_duration'];
            }
            $cantidadSesiones = (int)$request->input('sessions', 1);

            // Cargar datos de facturación
            $documentosConfig = \App\Models\ConfiguracionComprobante::all()->keyBy('tipo');
            $metodosPago = [
                'efectivo' => 'Efectivo',
                'contado' => 'Al Contado',
                'tarjeta' => 'Tarjeta Débito/Crédito',
                'transferencia' => 'Transferencia Bancaria',
                'yape' => 'Yape',
                'plin' => 'Plin',
            ];

            $isReceptionist = auth()->user()->isReceptionist();
            $servicio = \App\Models\Servicio::find($request->input('servicio_id'));

            return view('booking.checkout', compact(
                'availability', 
                'cantidadSesiones', 
                'selectedType', 
                'typeCode', 
                'rescheduleId', 
                'modalidadId', 
                'baseDuration', 
                'pacienteId',
                'documentosConfig',
                'metodosPago',
                'isReceptionist',
                'servicio'
            ));
        } catch (\Exception $e) {
            return redirect()->route('booking.schedule', ['reschedule_id' => $rescheduleId])->with('error', 'No se pudo procesar el horario seleccionado: ' . $e->getMessage());
        }
    }

    /**
     * Guardar una nueva reserva o actualizar una existente (reprogramación).
     */
    public function store(StoreBookingRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $user = auth()->user();
        
        // Si es recepcionista, el paciente_id viene en el request
        if ($user->isReceptionist() && $request->has('paciente_id')) {
            $data['paciente_id'] = $request->input('paciente_id');
        } else {
            $data['paciente_id'] = $user->id;
        }

        $data['modalidad_id'] = $request->input('modalidad_id');
        $rescheduleId = $request->input('reschedule_id');

        // Buscar el usuario del paciente para asegurar que tenga perfil
        $pacienteUser = \App\Models\Usuario::findOrFail($data['paciente_id']);

        // Asegurar que existe el perfil de paciente
        if (!$pacienteUser->paciente) {
            \App\Models\Paciente::create([
                'usuario_id' => $pacienteUser->id,
            ]);
        }

        try {
            if ($rescheduleId) {
                // Lógica de reprogramación
                $cita = \App\Models\Cita::findOrFail($rescheduleId);
                
                // Validar propiedad
                if ($cita->paciente_id !== $user->id && !$user->isReceptionist()) {
                    throw new \Exception('No tienes permiso para reprogramar esta cita.');
                }

                // Usamos el servicio para manejar la lógica compleja de estados y slots
                $this->bookingService->rescheduleAppointment($cita, $data);

                // El recepcionista vuelve a su panel; el paciente a su portal.
                if ($user->isReceptionist()) {
                    return redirect()->route('receptionist.dashboard')->with('success', '¡Cita reprogramada con éxito!');
                }

                return redirect()->route('portal.index')->with('success', '¡Cita reprogramada con éxito!');
            }

            // Asignar costos si vienen del formulario (Recepcionista) o calcular si no
            if ($request->has('total_manual') && $user->isReceptionist()) {
                $data['total'] = $request->input('total_manual');
                $data['costo'] = $data['total'] / max(1, $data['cantidad_sesiones']);
            } else {
                // Ya se calculan en el servicio o podemos asegurarlos aquí
            }

            // Manejar Comprobante
            if ($request->has('tipo_comprobante')) {
                $compData = \App\Models\ConfiguracionComprobante::getNextCorrelativo($request->input('tipo_comprobante'));
                $data['tipo_comprobante'] = $request->input('tipo_comprobante');
                $data['documento_serie'] = $compData['serie'];
                $data['documento_correlativo'] = $compData['correlativo'];
                $data['metodo_pago'] = $request->input('metodo_pago');
            }

            $cita = $this->bookingService->createAppointment($data);
            
            if ($user->isReceptionist()) {
                return redirect()->route('receptionist.dashboard')->with('success', '¡Cita y pago registrados con éxito!');
            }
            if ($user->isPatient()) {
                return redirect()->route('portal.index')->with('success', '¡Cita reservada con éxito! Aquí tienes el registro de tus sesiones.');
            }
            return redirect()->route('dashboard')->with('success', '¡Cita reservada con éxito!');
        }
        catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Redirección directa al Google Meet para el paciente.
     * Si no existe un enlace, lo genera usando la cuenta del especialista.
     */
    public function joinMeeting(Cita $cita): RedirectResponse
    {
        // Validar que la cita pertenece al paciente autenticado
        if ($cita->paciente_id !== auth()->id()) {
            abort(403);
        }

        // Cargar relaciones necesarias
        $cita->load(['disponibilidad', 'especialista.usuario', 'paciente']);

        // El enlace solo se habilita 30 minutos antes del inicio.
        if ($cita->disponibilidad && now()->lt($cita->start_at->copy()->subMinutes(30))) {
            return redirect()->route('portal.index')
                ->with('info', 'El enlace de la sesión estará disponible 30 minutos antes de su inicio.');
        }

        // Si no tiene enlace, intentar generarlo usando la cuenta del especialista
        if (empty($cita->meeting_url)) {
            $especialistaUser = $cita->especialista->usuario ?? null;

            if (!$especialistaUser || empty($especialistaUser->google_access_token)) {
                return redirect()->route('portal.index')
                    ->with('info', 'El especialista aún no ha configurado Google Meet. Contacta con tu terapeuta.');
            }

            try {
                $meetingUrl = $this->googleMeetingService->createMeeting($especialistaUser, [
                    'title'       => 'Sesión Terapéutica: ' . ($cita->paciente->nombre ?? 'Paciente'),
                    'description' => 'Sesión virtual agendada vía Sia.',
                    'start_time'  => $cita->disponibilidad->fecha->toDateString() . ' ' . $cita->disponibilidad->hora_inicio,
                    'end_time'    => $cita->disponibilidad->fecha->toDateString() . ' ' . $cita->disponibilidad->hora_fin,
                    'attendees'   => array_filter([
                        $cita->paciente->email        ?? null,
                        $especialistaUser->email       ?? null,
                    ]),
                ]);

                $cita->update(['meeting_url' => $meetingUrl]);
            } catch (\Exception $e) {
                return redirect()->route('portal.index')
                    ->with('error', 'No se pudo obtener el enlace de la sesión. Por favor intenta de nuevo.');
            }
        }

        return redirect($cita->meeting_url);
    }
}
