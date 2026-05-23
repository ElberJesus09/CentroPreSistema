<?php

namespace App\Services;

use App\Models\AcademicCycle;
use App\Models\AcademicCycleShift;
use App\Models\Campus;
use App\Models\Shift;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class AcademicCycleService
{
    /** Listado principal: programacion ciclo-sede-turno. */
    public function paginateSchedules(int $perPage = 15): LengthAwarePaginator
    {
        return AcademicCycleShift::query()
            ->with(['academicCycle', 'campus', 'shift'])
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    /** Metricas agregadas para dashboard (sin graficos). */
    public function dashboardAcademicMetrics(?int $year = null, ?int $academicCycleId = null): array
    {
        $base = AcademicCycleShift::query()->where('status', true);

        if ($academicCycleId !== null) {
            $base->where('academic_cycle_id', $academicCycleId);
        }

        if ($year !== null) {
            $base->whereHas('academicCycle', function (Builder $query) use ($year): void {
                $query->whereBetween('start_date', ["{$year}-01-01", "{$year}-12-31"]);
            });
        }

        $totalCapacity = (int) (clone $base)->sum('capacity');
        $totalEnrolled = (int) (clone $base)->sum('enrolled');
        $available = max(0, $totalCapacity - $totalEnrolled);
        $occupancy = $totalCapacity > 0
            ? round(($totalEnrolled / $totalCapacity) * 100, 1)
            : 0.0;

        return [
            'active_schedules' => (clone $base)->count(),
            'total_capacity' => $totalCapacity,
            'total_enrolled' => $totalEnrolled,
            'available_spots' => $available,
            'occupancy_percent' => $occupancy,
        ];
    }

    /** Listas para selects de programacion (incluye inactivos para edicion). */
    public function cyclesForForms(): Collection
    {
        return AcademicCycle::query()->orderByDesc('start_date')->get(['id', 'name', 'status']);
    }

    public function campusesForForms(): Collection
    {
        return Campus::query()->orderBy('name')->get(['id', 'name', 'status']);
    }

    public function shiftsForForms(): Collection
    {
        return Shift::query()->orderBy('name')->get(['id', 'name', 'status']);
    }

    // --- Academic cycles CRUD ---

    public function paginateCycles(int $perPage = 15): LengthAwarePaginator
    {
        return AcademicCycle::query()->orderByDesc('id')->paginate($perPage);
    }

    public function createCycle(array $attributes): AcademicCycle
    {
        return AcademicCycle::query()->create($attributes);
    }

    public function updateCycle(AcademicCycle $cycle, array $attributes): AcademicCycle
    {
        $cycle->update($attributes);

        return $cycle->fresh();
    }

    /** No elimina si existen programaciones asociadas. */
    public function deleteCycle(AcademicCycle $cycle): void
    {
        if ($cycle->schedules()->exists()) {
            throw ValidationException::withMessages([
                'delete' => ['No se puede eliminar el ciclo: tiene programaciones de turnos.'],
            ]);
        }

        $cycle->delete();
    }

    // --- Campuses CRUD ---

    public function paginateCampuses(int $perPage = 15): LengthAwarePaginator
    {
        return Campus::query()->orderBy('name')->paginate($perPage);
    }

    public function createCampus(array $attributes): Campus
    {
        return Campus::query()->create($attributes);
    }

    public function updateCampus(Campus $campus, array $attributes): Campus
    {
        $campus->update($attributes);

        return $campus->fresh();
    }

    public function deleteCampus(Campus $campus): void
    {
        if ($campus->schedules()->exists()) {
            throw ValidationException::withMessages([
                'delete' => ['No se puede eliminar la sede: está asignada en programaciones.'],
            ]);
        }

        $campus->delete();
    }

    // --- Shifts CRUD ---

    public function paginateShifts(int $perPage = 15): LengthAwarePaginator
    {
        return Shift::query()->orderBy('name')->paginate($perPage);
    }

    public function createShift(array $attributes): Shift
    {
        return Shift::query()->create($attributes);
    }

    public function updateShift(Shift $shift, array $attributes): Shift
    {
        $shift->update($attributes);

        return $shift->fresh();
    }

    public function deleteShift(Shift $shift): void
    {
        if ($shift->schedules()->exists()) {
            throw ValidationException::withMessages([
                'delete' => ['No se puede eliminar el turno: está asignado en programaciones.'],
            ]);
        }

        $shift->delete();
    }

    // --- Academic cycle shifts (programacion) ---

    public function createSchedule(array $attributes): AcademicCycleShift
    {
        return AcademicCycleShift::query()->create($attributes);
    }

    public function updateSchedule(AcademicCycleShift $schedule, array $attributes): AcademicCycleShift
    {
        $schedule->update($attributes);

        return $schedule->fresh()->load(['academicCycle', 'campus', 'shift']);
    }

    public function deleteSchedule(AcademicCycleShift $schedule): void
    {
        if ($schedule->students()->exists()) {
            throw ValidationException::withMessages([
                'delete' => ['No se puede eliminar la programación: tiene alumnos matriculados.'],
            ]);
        }

        $schedule->delete();
    }

    /** Consultas base reutilizables para reportes futuros. */
    public function schedulesQuery(): Builder
    {
        return AcademicCycleShift::query()->with(['academicCycle', 'campus', 'shift']);
    }
}
