<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStaffModuleAccess
{
    /**
     * Bloquea el modulo staff para roles sin permiso (403).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user === null || ! $user->canAccessStaffModule()) {
            abort(Response::HTTP_FORBIDDEN, 'Unauthorized');
        }

        return $next($request);
    }
}
