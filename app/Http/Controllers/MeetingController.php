<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Google\GoogleMeetingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;

/**
 * Controlador para gestionar la creación de reuniones.
 */
class MeetingController extends Controller
{
    public function __construct(
        protected GoogleMeetingService $googleMeetingService
    ) {}

    /**
     * Muestra el formulario para crear una reunión.
     */
    public function create()
    {
        /** @var \App\Models\Usuario $user */
        $user = Auth::user();

        // Verificar si el usuario tiene tokens de Google
        $isGoogleConnected = !empty($user->google_access_token);

        return view('meetings.create', compact('isGoogleConnected'));
    }

    /**
     * Procesa la creación de una reunión de Google Meet.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_time'  => 'required|date|after:now',
            'end_time'    => 'required|date|after:start_time',
        ]);

        try {
            /** @var \App\Models\Usuario $user */
            $user = Auth::user();

            if (empty($user->google_access_token)) {
                return redirect()->route('google.auth')->with('warning', 'Primero debes conectar tu cuenta de Google.');
            }

            $meetingUrl = $this->googleMeetingService->createMeeting($user, $request->only([
                'title', 'description', 'start_time', 'end_time'
            ]));

            return redirect()->route('meetings.create')->with([
                'success'     => 'Reunión creada con éxito.',
                'meeting_url' => $meetingUrl
            ]);

        } catch (Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Error al crear la reunión: ' . $e->getMessage());
        }
    }

    /**
     * Genera un enlace de Google Meet para una cita específica.
     */
    public function generateForAppointment(Request $request, int $citaId)
    {
        try {
            /** @var \App\Models\Usuario $user */
            $user = Auth::user();
            $cita = \App\Models\Cita::with(['paciente', 'disponibilidad'])->findOrFail($citaId);

            if (empty($user->google_access_token)) {
                return redirect()->route('google.auth')->with('warning', 'Primero debes conectar tu cuenta de Google.');
            }

            // Datos para el evento de calendar
            $meetingData = [
                'title'       => 'Sesión Terapéutica: ' . ($cita->paciente->nombre ?? 'Paciente'),
                'description' => 'Sesión virtual agendada vía Sia.',
                'start_time'  => $cita->disponibilidad->fecha->toDateString() . ' ' . $cita->disponibilidad->hora_inicio,
                'end_time'    => $cita->disponibilidad->fecha->toDateString() . ' ' . $cita->disponibilidad->hora_fin,
            ];

            $meetingUrl = $this->googleMeetingService->createMeeting($user, $meetingData);

            // Guardar el enlace en la cita
            $cita->meeting_url = $meetingUrl;
            $cita->save();

            return redirect()->back()->with('success', 'Enlace de Meet generado correctamente.');

        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error al generar el enlace: ' . $e->getMessage());
        }
    }
}
