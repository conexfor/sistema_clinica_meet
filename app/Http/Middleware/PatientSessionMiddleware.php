<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Carbon;

class PatientSessionMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): RedirectResponse|\Illuminate\Http\Response
    {
        $user = $request->user();

        if ($user && $user->isPatient()) {
            Config::set('session.expire_on_close', true);

            $lastActivity = $request->session()->get('last_activity');
            $now = Carbon::now();

            if ($lastActivity) {
                $last = Carbon::parse($lastActivity);
                if ($last->diffInMinutes($now) >= 15) {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    return redirect()->route('login')
                        ->with('info', 'Sesión cerrada por inactividad. Vuelve a iniciar sesión para continuar.');
                }
            }

            $request->session()->put('last_activity', $now->toDateTimeString());
        }

        return $next($request);
    }
}
