<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\Console\Output\OutputInterface|\Illuminate\Http\RedirectResponse)  $next
     * @param  string  $role
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        if (!$user || !$user->rol) {
            return redirect()->route('dashboard');
        }

        // Legacy behavior: exact role match
        if ($user->rol->nombre === $role) {
            return $next($request);
        }

        // Support action:... parameters (eg. role:admin but allow if user has matching action)
        // If the requested role is 'admin' but the user has actions that grant access
        // to the specific admin resource, allow it. This keeps routes using role:admin
        // backwards-compatible while allowing fine-grained action grants.
        if ($role === 'admin') {
            $route = $request->route();
            $routeName = $route ? $route->getName() : null;

            // Mapping of admin route fragments to required actions
            $map = [
                'citas' => 'citas_index',
                'pacientes' => 'pacientes_index',
                'servicios' => 'manage_services',
                'categorias' => 'categorias_index',
                'especialistas' => 'manage_specialists',
                'usuarios' => 'manage_users',
                'reportes' => 'view_reports',
                'lista-espera' => 'lista_espera_index',
                'soporte' => 'soporte_index',
                'roles' => 'roles_index',
            ];

            if ($routeName) {
                foreach ($map as $fragment => $requiredAction) {
                    if (Str::startsWith($routeName, 'admin.' . $fragment) || Str::contains($routeName, '.' . $fragment . '.')) {
                        if ($user->rol->hasAnyAction([$requiredAction])) {
                            return $next($request);
                        }
                    }
                }
            }
        }

        return redirect()->route('dashboard');
    }
}
