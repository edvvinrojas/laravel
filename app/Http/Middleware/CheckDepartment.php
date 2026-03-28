<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckDepartment
{
    public function handle(Request $request, Closure $next, string ...$departments): Response
    {
        $user = auth()->user();
        if (!$user) return redirect()->route('login');

        if ($user->isAdmin()) return $next($request);

        if (!in_array($user->department, $departments)) {
            abort(403, 'No tienes acceso a este departamento.');
        }

        return $next($request);
    }
}
