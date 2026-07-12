<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreLoginRequest;
use App\Services\Auth\AuthService;
use App\Models\Usuario;
use App\Models\Rol;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}

    /**
     * Muestra el formulario de inicio de sesión.
     */
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    /**
     * Muestra el formulario de registro.
     */
    public function showRegisterForm(): View
    {
        return view('auth.register');
    }

    /**
     * Maneja la petición de registro.
     */
    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:usuarios',
            'telefono' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $rolPaciente = Rol::where('nombre', Usuario::ROL_PATIENT)->first();

        $user = Usuario::create([
            'nombre' => $validated['nombre'],
            'email' => $validated['email'],
            'telefono' => $validated['telefono'] ?? null,
            'password' => Hash::make($validated['password']),
            'rol_id' => $rolPaciente->id,
        ]);

        // Crear perfil de paciente automáticamente
        \App\Models\Paciente::create([
            'usuario_id' => $user->id,
        ]);

        auth()->login($user);

        return redirect()->route('booking.service');
    }

    /**
     * Maneja la petición de inicio de sesión.
     */
    public function login(StoreLoginRequest $request): RedirectResponse
    {
        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if ($this->authService->attemptLogin($credentials, $remember)) {
            $user = $request->user();

            // Bloquear el acceso a cuentas desactivadas por el administrador.
            if (! $user->esta_activo) {
                $this->authService->logout($request);

                return back()
                    ->with('error', 'Tu cuenta está desactivada. Contacta al administrador.')
                    ->onlyInput('email');
            }

            $request->session()->regenerate();

            // Redirigir según el rol del usuario utilizando los helpers del modelo
            if ($user->isAdmin()) {
                return redirect()->intended(route('admin.dashboard'));
            } elseif ($user->isDoctor()) {
                return redirect()->intended(route('doctor.dashboard'));
            } elseif ($user->isReceptionist()) {
                return redirect()->intended(route('receptionist.dashboard'));
            } else {
                return redirect()->intended(route('portal.index'));
            }
        }

        return back()->withErrors([
            'email' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
        ])->onlyInput('email');
    }

    /**
     * Cierra la sesión del usuario.
     */
    public function logout(Request $request): RedirectResponse
    {
        $this->authService->logout($request);

        return redirect('/');
    }
}
