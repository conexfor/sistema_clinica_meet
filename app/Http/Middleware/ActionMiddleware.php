<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ActionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string  $action
     */
    public function handle(Request $request, Closure $next, string $action, string ...$additionalActions): Response
    {
        $user = $request->user();
        $requiredActions = collect(array_merge([$action], $additionalActions))
            ->flatMap(fn ($item) => explode(',', $item))
            ->map(fn ($item) => trim($item))
            ->filter()
            ->all();

        if (!$user || !method_exists($user, 'hasAction')) {
            return redirect()->route('dashboard');
        }

        $allowed = collect($requiredActions)->contains(fn ($requiredAction) => $user->hasAction($requiredAction));
        if (!$allowed) {
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}
