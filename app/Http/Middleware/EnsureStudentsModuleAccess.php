<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStudentsModuleAccess
{
    /**
     * Solo personal autorizado (403 si no aplica).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user === null || ! $user->canAccessStudentsModule()) {
            abort(Response::HTTP_FORBIDDEN, 'No tienes permiso para acceder a este modulo.');
        }

        return $next($request);
    }
}
