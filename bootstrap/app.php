<?php

use App\Http\Middleware\EnsureAcademicCyclesModuleAccess;
use App\Http\Middleware\EnsureAcademicManagementModuleAccess;
use App\Http\Middleware\EnsureReportsModuleAccess;
use App\Http\Middleware\EnsureStaffModuleAccess;
use App\Http\Middleware\EnsureStudentsModuleAccess;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            SecurityHeaders::class,
        ]);

        $middleware->alias([
            'staff.module' => EnsureStaffModuleAccess::class,
            'academic-cycles.module' => EnsureAcademicCyclesModuleAccess::class,
            'academic-management.module' => EnsureAcademicManagementModuleAccess::class,
            'students.module' => EnsureStudentsModuleAccess::class,
            'reports.module' => EnsureReportsModuleAccess::class,
            'permission' => PermissionMiddleware::class,
            'role' => RoleMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);
        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(fn () => route('dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
