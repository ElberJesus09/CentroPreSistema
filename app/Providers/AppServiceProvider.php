<?php

namespace App\Providers;

use App\Models\AcademicCycle;
use App\Models\AcademicCycleShift;
use App\Models\Campus;
use App\Models\Classroom;
use App\Models\ExamSetting;
use App\Models\Evaluation;
use App\Models\Grade;
use App\Models\Shift;
use App\Models\Staff;
use App\Models\Student;
use App\Observers\ActivityLogObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
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
            return Limit::perMinute(20)->by($request->ip());
        });

        RateLimiter::for('public-registration-finish', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('public-registration-lookup', function (Request $request) {
            return Limit::perMinute(30)->by($request->ip());
        });

        RateLimiter::for('admin-login', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
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
}
