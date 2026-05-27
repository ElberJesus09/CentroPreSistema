<?php

namespace App\Providers;

use App\Models\AcademicCycle;
use App\Models\AcademicCycleShift;
use App\Models\Campus;
use App\Models\Classroom;
use App\Models\Evaluation;
use App\Models\ExamSetting;
use App\Models\Grade;
use App\Models\Shift;
use App\Models\Staff;
use App\Models\Student;
use App\Observers\ActivityLogObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->app->setLocale('es');
        $this->app['translator']->setFallback('es');

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Parametro {schedule} en rutas del modulo academic cycles.
        Route::bind('schedule', fn (string $value) => AcademicCycleShift::query()->whereKey($value)->firstOrFail());

        Staff::observe(ActivityLogObserver::class);
        Student::observe(ActivityLogObserver::class);
        AcademicCycle::observe(ActivityLogObserver::class);
        Campus::observe(ActivityLogObserver::class);
        Shift::observe(ActivityLogObserver::class);
        AcademicCycleShift::observe(ActivityLogObserver::class);
        ExamSetting::observe(ActivityLogObserver::class);
        Classroom::observe(ActivityLogObserver::class);
        Evaluation::observe(ActivityLogObserver::class);
        Grade::observe(ActivityLogObserver::class);

        Gate::before(function (Staff $user, string $ability): ?bool {
            $hasTemporaryPermission = $user->temporaryPermissionGrants()
                ->where('expires_at', '>', now())
                ->whereHas('permission', fn ($query) => $query->where('name', $ability))
                ->exists();

            return $hasTemporaryPermission ? true : null;
        });

        $this->configureRegistrationRateLimiters();
    }

    /** Limites para portal publico y login administrativo. */
    private function configureRegistrationRateLimiters(): void
    {
        RateLimiter::for('public-registration', function (Request $request) {
            return Limit::perMinute(20)
                ->by($request->ip())
                ->response(fn () => $this->throttledResponse($request, 'public-registration'));
        });

        RateLimiter::for('public-registration-finish', function (Request $request) {
            return Limit::perMinutes(10, 4)
                ->by($request->ip())
                ->response(fn () => $this->throttledResponse($request, 'public-registration-finish'));
        });

        RateLimiter::for('public-registration-lookup', function (Request $request) {
            return Limit::perMinute(20)
                ->by($request->ip())
                ->response(fn () => $this->throttledResponse($request, 'public-registration-lookup'));
        });

        RateLimiter::for('admin-login', function (Request $request) {
            $username = mb_strtolower((string) $request->input('username', 'anonimo'));

            return Limit::perMinutes(15, 5)
                ->by($request->ip().'|'.$username)
                ->response(fn () => $this->throttledResponse($request, 'admin-login'));
        });

        RateLimiter::for('admin-student-mail-resend', function (Request $request) {
            $user = $request->user();
            $key = $user?->getAuthIdentifier() ?? $request->ip();

            return Limit::perMinute(8)->by((string) $key);
        });

        RateLimiter::for('admin-student-documents-download', function (Request $request) {
            $user = $request->user();
            $key = $user?->getAuthIdentifier() ?? $request->ip();

            return Limit::perMinute(20)->by((string) $key);
        });
    }

    private function throttledResponse(Request $request, string $limiter): Response
    {
        Log::channel('security')->warning('Solicitud bloqueada por limite de tasa.', [
            'limiter' => $limiter,
            'ip' => $request->ip(),
            'path' => $request->path(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 500),
        ]);

        return response('Demasiadas solicitudes. Intente nuevamente en unos minutos.', 429);
    }
}
