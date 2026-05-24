<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Staff;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ActivityLogService
{
    /** @var array<string, string> */
    private const MODULE_LABELS = [
        'auth' => 'Autenticacion',
        'staff' => 'Empleados',
        'students' => 'Alumnos',
        'academic_cycles' => 'Ciclos académicos',
        'campuses' => 'Sedes',
        'shifts' => 'Turnos',
        'schedules' => 'Programación',
        'exam_settings' => 'Configuracion de examen',
        'reports' => 'Reportes',
        'roles_permissions' => 'Roles y permisos',
        'academic_classrooms' => 'Aulas academicas',
        'academic_evaluations' => 'Evaluaciones academicas',
        'academic_grades' => 'Notas academicas',
    ];

    public function record(
        string $module,
        string $action,
        string $description,
        ?Model $subject = null,
        array $properties = [],
        ?Staff $staff = null,
        ?Request $request = null,
    ): ?ActivityLog {
        $staff ??= auth()->user();

        if (! $staff instanceof Staff) {
            return null;
        }

        $request ??= request();

        return ActivityLog::query()->create([
            'staff_id' => $staff->id,
            'module' => $module,
            'action' => $action,
            'description' => mb_substr($description, 0, 500),
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 500),
            'properties' => $properties === [] ? null : $properties,
        ]);
    }

    public function queryForFilters(array $filters): Builder
    {
        return ActivityLog::query()
            ->with('staff.role')
            ->when($filters['staff_id'] ?? null, fn (Builder $query, int $staffId) => $query->where('staff_id', $staffId))
            ->when($filters['module'] ?? null, fn (Builder $query, string $module) => $query->where('module', $module))
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $date) => $query->where('created_at', '>=', CarbonImmutable::parse($date)->startOfDay()))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $date) => $query->where('created_at', '<=', CarbonImmutable::parse($date)->endOfDay()))
            ->latest('created_at')
            ->latest('id');
    }

    public function paginate(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        return $this->queryForFilters($filters)->paginate($perPage)->withQueryString();
    }

    public function forPdf(array $filters): Collection
    {
        return $this->queryForFilters($filters)
            ->limit(500)
            ->get();
    }

    public function staffOptions(): Collection
    {
        return Staff::query()
            ->with('role')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
    }

    /**
     * @return array<string, string>
     */
    public function moduleOptions(): array
    {
        return self::MODULE_LABELS;
    }

    public function moduleLabel(?string $module): string
    {
        if ($module === null || $module === '') {
            return 'Todos';
        }

        return self::MODULE_LABELS[$module] ?? $module;
    }
}
