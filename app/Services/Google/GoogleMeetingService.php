<?php

namespace App\Services\Google;

use App\Models\Usuario;
use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use Google\Service\Calendar\EventAttendee;
use Google\Service\Calendar\ConferenceData;
use Google\Service\Calendar\CreateConferenceRequest;
use Google\Service\Calendar\ConferenceSolutionKey;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

/**
 * Servicio para gestionar la integración con Google Calendar y Google Meet.
 * Crea eventos con soporte para invitados (attendees) y notificaciones automáticas de Calendar.
 */
class GoogleMeetingService
{
    protected Client $client;

    public function __construct()
    {
        $this->client = new Client();
        $this->client->setClientId(config('services.google.client_id'));
        $this->client->setClientSecret(config('services.google.client_secret'));
        $this->client->setRedirectUri(config('services.google.redirect_uri'));
        $this->client->addScope(Calendar::CALENDAR_EVENTS);
        $this->client->setAccessType('offline');
        $this->client->setPrompt('consent');
    }

    /**
     * Obtiene el cliente de Google configurado para un usuario específico.
     * Refresca el token automáticamente si ha expirado.
     */
    public function getClientForUser(Usuario $user): Client
    {
        $this->client->setAccessToken([
            'access_token'  => $user->google_access_token,
            'refresh_token' => $user->google_refresh_token,
            'expires_in'    => $user->google_token_expires_at->getTimestamp() - now()->getTimestamp(),
        ]);

        if ($this->client->isAccessTokenExpired()) {
            Log::info('Google Token expired for user ' . $user->id . '. Attempting refresh.');
            if ($user->google_refresh_token) {
                $newToken = $this->client->fetchAccessTokenWithRefreshToken($user->google_refresh_token);
                
                if (isset($newToken['error'])) {
                    Log::error('Error refreshing Google token: ' . $newToken['error']);
                    throw new Exception('Error al refrescar el token de Google: ' . $newToken['error']);
                }

                $user->update([
                    'google_access_token'     => $newToken['access_token'],
                    'google_token_expires_at' => now()->addSeconds($newToken['expires_in']),
                ]);
                Log::info('Google Token refreshed successfully for user ' . $user->id);
            } else {
                Log::warning('No refresh token available for user ' . $user->id);
                throw new Exception('El token de Google ha expirado y no hay token de refresco disponible.');
            }
        }

        return $this->client;
    }

    /**
     * Crea un evento en Google Calendar (con o sin enlace de Google Meet) y envía
     * invitaciones automáticas a todos los asistentes (paciente y especialista).
     *
     * Se usa para TODAS las citas: las virtuales incluyen un enlace de Meet;
     * las presenciales quedan igualmente registradas en el calendario de ambos.
     *
     * @param Usuario $user      El usuario especialista (propietario del calendario Google).
     * @param array   $data      Datos del evento (title, description, start_time, end_time, attendees[]).
     * @param bool    $withMeet  Si debe generarse una conferencia de Google Meet.
     * @return array{event_id: ?string, meet_url: ?string}
     */
    public function createEvent(Usuario $user, array $data, bool $withMeet = true): array
    {
        $client  = $this->getClientForUser($user);
        $service = new Calendar($client);

        // --- Construir lista de asistentes (attendees) para invitaciones automáticas ---
        $attendees = [];
        if (!empty($data['attendees']) && is_array($data['attendees'])) {
            foreach ($data['attendees'] as $email) {
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $attendee = new EventAttendee();
                    $attendee->setEmail(trim($email));
                    $attendees[] = $attendee;
                }
            }
        }

        // --- Construir el evento de Google Calendar ---
        $eventPayload = [
            'summary'     => $data['title']       ?? 'Nueva Reunión',
            'description' => $data['description'] ?? '',
            'start'       => [
                'dateTime' => Carbon::parse($data['start_time'])->toRfc3339String(),
                'timeZone' => config('app.timezone'),
            ],
            'end'         => [
                'dateTime' => Carbon::parse($data['end_time'])->toRfc3339String(),
                'timeZone' => config('app.timezone'),
            ],
        ];

        // Solo las sesiones virtuales generan una sala de Google Meet
        if ($withMeet) {
            $eventPayload['conferenceData'] = new ConferenceData([
                'createRequest' => new CreateConferenceRequest([
                    'requestId'             => uniqid(),
                    'conferenceSolutionKey' => new ConferenceSolutionKey(['type' => 'hangoutsMeet']),
                ]),
            ]);
        }

        $event = new Event($eventPayload);

        // Agregar los asistentes al evento para que reciban invitación por correo
        if (!empty($attendees)) {
            $event->setAttendees($attendees);
        }

        Log::info('Creating Google Calendar event for user ' . $user->id, [
            'start'     => $data['start_time'],
            'end'       => $data['end_time'],
            'with_meet' => $withMeet,
            'attendees' => collect($attendees)->map(fn($a) => $a->getEmail())->toArray(),
        ]);

        // 'sendUpdates' => 'all' hace que Google notifique por correo/calendario a
        // todos los asistentes (paciente y especialista).
        $optParams = ['sendUpdates' => 'all'];
        if ($withMeet) {
            $optParams['conferenceDataVersion'] = 1;
        }

        try {
            $createdEvent = $service->events->insert('primary', $event, $optParams);

            // Extraer el enlace de la sesión de video (Meet) si aplica
            $meetUrl = null;
            if ($withMeet && $createdEvent->getConferenceData() && $createdEvent->getConferenceData()->getEntryPoints()) {
                foreach ($createdEvent->getConferenceData()->getEntryPoints() as $entryPoint) {
                    if ($entryPoint->getEntryPointType() === 'video') {
                        $meetUrl = $entryPoint->getUri();
                        break;
                    }
                }
            }

            return [
                'event_id' => $createdEvent->getId(),
                'meet_url' => $meetUrl,
            ];
        } catch (Exception $e) {
            Log::error('Error al crear evento en Google Calendar: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Crea una reunión en Google Meet (evento de Calendar + enlace de video).
     * Wrapper de compatibilidad: exige que se genere el enlace de Meet.
     *
     * @return string El enlace de Google Meet.
     */
    public function createMeeting(Usuario $user, array $data): string
    {
        $result = $this->createEvent($user, $data, true);

        if (empty($result['meet_url'])) {
            throw new Exception('No se pudo generar el enlace de Google Meet.');
        }

        return $result['meet_url'];
    }
}
