<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        foreach ($roles as $rule) {
            // Acepta "dept:ti" además de nombres de rol directos
            if (str_starts_with($rule, 'dept:')) {
                if ($user->department === substr($rule, 5)) {
                    return $next($request);
                }
            } elseif ($user->rol === $rule) {
                return $next($request);
            }
        }

        abort(403, 'No tienes permiso para acceder a esta sección.');
    }
}
