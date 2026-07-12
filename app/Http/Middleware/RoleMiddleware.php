<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

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
        if (!$request->user() || !$request->user()->rol || $request->user()->rol->nombre !== $role) {
            abort(403, 'Acceso denegado: No tienes el rol necesario.');
        }

        return $next($request);
    }
}
