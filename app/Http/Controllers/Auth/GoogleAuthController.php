<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Google\Client;
use Google\Service\Calendar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;

/**
 * Controlador para manejar la autenticación con Google OAuth 2.0.
 */
class GoogleAuthController extends Controller
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
     * Redirige al usuario a la página de consentimiento de Google.
     */
    public function redirectToGoogle()
    {
        return redirect()->away($this->client->createAuthUrl());
    }

    /**
     * Maneja el callback de Google después de la autorización.
     */
    public function handleGoogleCallback(Request $request)
    {
        if ($request->has('error')) {
            return redirect()->route('dashboard')->with('error', 'Error al autenticar con Google: ' . $request->error);
        }

        try {
            $token = $this->client->fetchAccessTokenWithAuthCode($request->code);
            
            if (isset($token['error'])) {
                throw new Exception('Error al obtener el token: ' . $token['error']);
            }

            /** @var Usuario $user */
            $user = Auth::user();
            
            $user->update([
                'google_access_token'    => $token['access_token'],
                'google_refresh_token'   => $token['refresh_token'] ?? $user->google_refresh_token,
                'google_token_expires_at' => now()->addSeconds($token['expires_in']),
            ]);

            $route = $user->rol->nombre === 'doctor' ? 'doctor.profile' : 'portal.index';
            return redirect()->route($route)->with('success', 'Cuenta de Google conectada correctamente. Las próximas sesiones virtuales se agendarán en tu calendario.');

        } catch (Exception $e) {
            return redirect()->route('dashboard')->with('error', 'Ocurrió un error inesperado al conectar con Google.');
        }
    }
}
