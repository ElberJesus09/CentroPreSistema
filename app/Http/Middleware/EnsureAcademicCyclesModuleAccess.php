<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAcademicCyclesModuleAccess
{
    /**
     * Bloquea el modulo academic cycles para roles sin permiso (403).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user === null || ! $user->canAccessAcademicCyclesModule()) {
            abort(Response::HTTP_FORBIDDEN, 'Unauthorized');
        }

        return $next($request);
    }
}
