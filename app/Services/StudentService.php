<?php

namespace App\Services;

use App\Models\AcademicCycleShift;
use App\Models\Campus;
use App\Models\Career;
use App\Models\Guardian;
use App\Models\School;
use App\Models\Student;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StudentService
{
    /** Cache catalogos publicos (segundos). */
    private const int CACHE_CATALOG_LONG = 600;

    /** Turnos publicos: TTL corto para reflejar cupos sin saturar DB. */
    private const int CACHE_PUBLIC_SCHEDULES = 45;

    /** Turnos con cupo y activos (consulta directa, sin cache). */
    public function availableSchedulesWithRelations(): Collection
    {
        return $this->queryPublicAvailableSchedules();
    }

    /** Turnos publicos con cache breve. */
    public function cachedPublicAvailableSchedules(): Collection
    {
        return $this->rememberEloquentCollection(
            'students.catalog.schedules.public.v1',
            self::CACHE_PUBLIC_SCHEDULES,
            fn (): Collection => $this->queryPublicAvailableSchedules(),
        );
    }

    /** Carreras activas con cache. */
    public function cachedActiveCareers(): Collection
    {
        return $this->rememberEloquentCollection(
            'students.catalog.careers.active.v2',
            self::CACHE_CATALOG_LONG,
            fn (): Collection => $this->queryActiveCareers(),
        );
    }

    /** Sedes activas con cache. */
    public function cachedActiveCampuses(): Collection
    {
        return $this->rememberEloquentCollection(
            'students.catalog.campuses.active.v1',
            self::CACHE_CATALOG_LONG,
            fn (): Collection => $this->queryActiveCampuses(),
        );
    }

    /** Carreras activas para selects (alias hacia cache). */
    public function activeCareers(): Collection
    {
        return $this->cachedActiveCareers();
    }

    /** Listado administrativo con relaciones. */
    public function paginateStudents(int $perPage = 15): LengthAwarePaginator
    {
        return Student::query()
            ->with(['career', 'schedule.academicCycle', 'schedule.campus', 'schedule.shift'])
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    /**
     * Turnos disponibles para alta/edición staff (incluye turno actual aunque este lleno).
     *
     * @return Collection<int, AcademicCycleShift>
     */
    public function schedulesForAdminForms(?Student $current = null): Collection
    {
        return AcademicCycleShift::query()
            ->where(function ($q) use ($current) {
                $q->where(function ($inner) {
                    $inner->where('status', true)
                        ->whereColumn('enrolled', '<', 'capacity');
                });
                if ($current !== null && $current->academic_cycle_shift_id !== null) {
                    $q->orWhere(
                        $q->getModel()->getQualifiedKeyName(),
                        $current->academic_cycle_shift_id,
                    );
                }
            })
            ->with(['academicCycle', 'campus', 'shift'])
            ->orderByDesc('academic_cycle_id')
            ->get();
    }

    /** Etiqueta legible para select de turnos. */
    public function scheduleOptionLabel(AcademicCycleShift $row): string
    {
        $cycle = $row->academicCycle?->name ?? '—';
        $campus = $row->campus?->name ?? '—';
        $shift = $row->shift?->name ?? '—';
        $left = max(0, $row->capacity - $row->enrolled);

        return "{$cycle} — {$campus} — {$shift} ({$left} cupos)";
    }

    /**
     * Registro de alumno + apoderado + colegio; incrementa matriculados bajo bloqueo.
     *
     * @param  array<string, mixed>  $validated
     */
    public function registerStudent(array $validated): Student
    {
        $validated = $this->sanitizeRegistrationPayload($validated);

        $student = DB::transaction(function () use ($validated) {
            $scheduleId = (int) $validated['academic_cycle_shift_id'];
            $schedule = AcademicCycleShift::query()->lockForUpdate()->findOrFail($scheduleId);
            $this->assertScheduleAcceptsEnrollment($schedule);

            $guardian = Guardian::query()->create($validated['guardian']);
            $school = School::query()->create($validated['school']);

            $status = $validated['status'] ?? Student::STATUS_PENDING;

            $student = Student::query()->create([
                ...$validated['student'],
                'career_id' => (int) $validated['career_id'],
                'academic_cycle_shift_id' => $scheduleId,
                'guardian_id' => $guardian->id,
                'school_id' => $school->id,
                'registration_date' => now()->toDateString(),
                'status' => $status,
            ]);

            $schedule->increment('enrolled');

            return $student->fresh()->load(['guardian', 'school', 'career', 'schedule.academicCycle', 'schedule.campus', 'schedule.shift']);
        });

        $this->forgetPublicScheduleCatalogCache();

        return $student;
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function updateStudent(Student $student, array $validated): Student
    {
        $validated = $this->sanitizeRegistrationPayload($validated);

        $student = DB::transaction(function () use ($student, $validated) {
            $student->load(['guardian', 'school']);
            $newScheduleId = (int) $validated['academic_cycle_shift_id'];

            if ($newScheduleId !== (int) $student->academic_cycle_shift_id) {
                $oldId = (int) $student->academic_cycle_shift_id;
                $ids = [$oldId, $newScheduleId];
                sort($ids);
                $locked = [];
                foreach ($ids as $id) {
                    $locked[$id] = AcademicCycleShift::query()->lockForUpdate()->findOrFail($id);
                }
                $oldSchedule = $locked[$oldId];
                $newSchedule = $locked[$newScheduleId];
                $this->assertScheduleAcceptsEnrollment($newSchedule);
                $oldSchedule->decrement('enrolled');
                $newSchedule->increment('enrolled');
            }

            $student->guardian?->update($validated['guardian']);
            $student->school?->update($validated['school']);

            $student->fill([
                ...$validated['student'],
                'career_id' => (int) $validated['career_id'],
                'academic_cycle_shift_id' => $newScheduleId,
                'status' => $validated['status'],
            ]);
            $student->save();

            return $student->fresh()->load(['guardian', 'school', 'career', 'schedule.academicCycle', 'schedule.campus', 'schedule.shift']);
        });

        $this->forgetPublicScheduleCatalogCache();

        return $student;
    }

    /** Elimina alumno, libera cupo y limpia apoderado/colegio asociados. */
    public function deleteStudent(Student $student): void
    {
        DB::transaction(function () use ($student) {
            $schedule = AcademicCycleShift::query()->lockForUpdate()->findOrFail($student->academic_cycle_shift_id);
            $guardianId = $student->guardian_id;
            $schoolId = $student->school_id;

            $schedule->decrement('enrolled');
            $student->delete();

            Guardian::query()->whereKey($guardianId)->delete();
            School::query()->whereKey($schoolId)->delete();
        });

        $this->forgetPublicScheduleCatalogCache();
    }

    /** Verifica turno activo y con cupo antes de incrementar. */
    private function assertScheduleAcceptsEnrollment(AcademicCycleShift $schedule): void
    {
        if (! $schedule->status) {
            throw ValidationException::withMessages([
                'academic_cycle_shift_id' => ['El turno seleccionado no esta activo.'],
            ]);
        }

        if ($schedule->enrolled >= $schedule->capacity) {
            throw ValidationException::withMessages([
                'academic_cycle_shift_id' => ['No hay vacantes disponibles en el turno seleccionado.'],
            ]);
        }
    }

    /**
     * @return Collection<int, AcademicCycleShift>
     */
    private function queryPublicAvailableSchedules(): Collection
    {
        return AcademicCycleShift::query()
            ->where('status', true)
            ->whereColumn('enrolled', '<', 'capacity')
            ->with(['academicCycle', 'campus', 'shift'])
            ->orderByDesc('academic_cycle_id')
            ->orderBy('campus_id')
            ->orderBy('shift_id')
            ->get();
    }

    /**
     * @return Collection<int, Career>
     */
    private function queryActiveCareers(): Collection
    {
        return Career::query()
            ->where('status', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * @return Collection<int, Campus>
     */
    private function queryActiveCampuses(): Collection
    {
        return Campus::query()
            ->where('status', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * Sanitiza strings anidados (XSS / ruido) antes de persistir.
     *
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function sanitizeRegistrationPayload(array $validated): array
    {
        foreach (['student', 'guardian', 'school'] as $section) {
            if (! isset($validated[$section]) || ! is_array($validated[$section])) {
                continue;
            }
            foreach ($validated[$section] as $key => $value) {
                if (! is_string($value)) {
                    continue;
                }
                $max = match (true) {
                    $section === 'student' && $key === 'email' => 255,
                    $section === 'school' && $key === 'name' => 255,
                    default => 500,
                };
                $validated[$section][$key] = $this->sanitizePlainString($value, $max);
            }
        }

        if (isset($validated['student']['email']) && is_string($validated['student']['email'])) {
            $validated['student']['email'] = mb_strtolower(trim($validated['student']['email']));
        }

        return $validated;
    }

    private function sanitizePlainString(string $value, int $max): string
    {
        return mb_substr(trim(strip_tags($value)), 0, $max);
    }

    /** Invalida cache de turnos publicos tras cambios de cupos. */
    private function forgetPublicScheduleCatalogCache(): void
    {
        Cache::forget('students.catalog.schedules.public.v1');
    }

    /**
     * Cache de colecciones Eloquent: si el valor deserializado no es una Collection
     * (p. ej. __PHP_Incomplete_Class tras cambios de clases), se invalida y se recalcula.
     *
     * @param  callable(): Collection  $callback
     */
    private function rememberEloquentCollection(string $key, int $ttl, callable $callback): Collection
    {
        $cached = Cache::get($key);

        if ($cached instanceof Collection) {
            return $cached;
        }

        if ($cached !== null) {
            Cache::forget($key);
        }

        $fresh = $callback();
        Cache::put($key, $fresh, $ttl);

        return $fresh;
    }
}
