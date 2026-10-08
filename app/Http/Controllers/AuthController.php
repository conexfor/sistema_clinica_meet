<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreLoginRequest;
use App\Services\Auth\AuthService;
use App\Models\Usuario;
use App\Models\Rol;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
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

        $userCandidate = Usuario::where('email', $request->input('email'))->first();
        if ($userCandidate?->isPatient()) {
            $remember = false;
        }

        if ($this->authService->attemptLogin($credentials, $remember)) {
            $user = $request->user();

            // Bloquear el acceso a cuentas desactivadas por el administrador.
            if (! $user->esta_activo) {
                $this->authService->logout($request);

                return back()
                    ->with('error', 'Tu cuenta está desactivada. Contacta al administrador.')
                    ->onlyInput('email');
            }

            // Ensure patient sessions are not persistent when browser closes
            if ($user->isPatient()) {
                $remember = false;
            }

            $request->session()->regenerate();

            // Redirigir al dashboard principal según el rol del usuario
            if ($user->isAdmin()) {
                return redirect()->route('admin.dashboard');
            } elseif ($user->isDoctor()) {
                return redirect()->route('doctor.dashboard');
            } elseif ($user->isReceptionist()) {
                return redirect()->route('receptionist.dashboard');
            } else {
                return redirect()->route('portal.index');
            }
        }

        return back()->withErrors([
            'email' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
        ])->onlyInput('email');
    }

    /**
     * Cierra la sesión del usuario.
     */
    public function profile(Request $request): View
    {
        $usuario = $request->user();

        return view('profile', compact('usuario'));
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $usuario = $request->user();

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', 'unique:usuarios,email,' . $usuario->id],
            'telefono' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8|confirmed',
        ], [
            'email.unique' => 'Este correo ya está registrado por otro usuario.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
        ]);

        $usuario->nombre = $validated['nombre'];
        $usuario->email = $validated['email'];
        $usuario->telefono = $validated['telefono'] ?? null;

        if (!empty($validated['password'])) {
            $usuario->password = $validated['password'];
        }

        $usuario->save();

        return redirect()->route('profile')->with('success', 'Tu perfil se actualizó correctamente.');
    }

    public function logout(Request $request): RedirectResponse
    {
        $this->authService->logout($request);

        return redirect('/');
    }
}
