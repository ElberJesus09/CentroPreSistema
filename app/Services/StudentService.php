<?php

namespace App\Services;

use App\Models\AcademicCycleShift;
use App\Models\Career;
use App\Models\Guardian;
use App\Models\School;
use App\Models\Student;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StudentService
{
    /** Turnos con cupo y activos (formulario publico). */
    public function availableSchedulesWithRelations(): Collection
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

    /** Carreras activas para selects. */
    public function activeCareers(): Collection
    {
        return Career::query()
            ->where('status', true)
            ->orderBy('name')
            ->get();
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
                if ($current !== null) {
                    $q->orWhereKey($current->academic_cycle_shift_id);
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
        return DB::transaction(function () use ($validated) {
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
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function updateStudent(Student $student, array $validated): Student
    {
        return DB::transaction(function () use ($student, $validated) {
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
}
