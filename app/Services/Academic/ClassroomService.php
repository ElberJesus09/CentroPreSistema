<?php

namespace App\Services\Academic;

use App\Models\AcademicCycle;
use App\Models\Classroom;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ClassroomService
{
    public function paginate(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        return Classroom::query()
            ->with('academicCycle:id,name')
            ->withCount([
                'assignments' => fn ($query) => $query->whereHas('student', fn ($student) => $student->where('status', \App\Models\Student::STATUS_ACTIVE)),
            ])
            ->when($filters['academic_cycle_id'] ?? null, fn ($q, int $id) => $q->where('academic_cycle_id', $id))
            ->when(($filters['search'] ?? '') !== '', function ($q) use ($filters): void {
                $search = $filters['search'];
                $q->where(fn ($inner) => $inner->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"));
            })
            ->orderByDesc('academic_cycle_id')
            ->orderBy('academic_priority')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data): Classroom
    {
        return DB::transaction(function () use ($data): Classroom {
            $data = $this->sanitize($data);
            $this->releaseDeletedCode((int) $data['academic_cycle_id'], (string) $data['code']);

            $classroom = Classroom::query()->create($data);
            $this->reorderPriorities($classroom, (int) $data['academic_priority']);

            return $classroom;
        });
    }

    public function update(Classroom $classroom, array $data): Classroom
    {
        return DB::transaction(function () use ($classroom, $data): Classroom {
            $oldCycleId = (int) $classroom->academic_cycle_id;
            $data = $this->sanitize($data);
            $used = $classroom->assignments()->count();
            if ((int) $data['capacity'] < $used) {
                throw ValidationException::withMessages([
                    'capacity' => ['La capacidad no puede ser menor que los alumnos asignados actualmente.'],
                ]);
            }

            $this->releaseDeletedCode((int) $data['academic_cycle_id'], (string) $data['code'], (int) $classroom->id);
            $targetPriority = (int) $data['academic_priority'];
            $classroom->update($data);
            $this->reorderPriorities($classroom->refresh(), $targetPriority);
            if ($oldCycleId !== (int) $classroom->academic_cycle_id) {
                $this->normalizePriorities($oldCycleId);
            }

            return $classroom->refresh();
        });
    }

    public function delete(Classroom $classroom): void
    {
        if ($classroom->assignments()->exists()) {
            throw ValidationException::withMessages([
                'classroom' => ['No se puede eliminar un aula con alumnos asignados.'],
            ]);
        }

        $cycleId = (int) $classroom->academic_cycle_id;
        $classroom->forceFill([
            'code' => $this->deletedCode((string) $classroom->code, (int) $classroom->id),
        ])->save();
        $classroom->delete();
        $this->normalizePriorities($cycleId);
    }

    public function normalizePriorities(int $academicCycleId): void
    {
        Classroom::query()
            ->where('academic_cycle_id', $academicCycleId)
            ->where('status', true)
            ->orderBy('academic_priority')
            ->orderBy('id')
            ->get()
            ->values()
            ->each(fn (Classroom $classroom, int $index) => $classroom->forceFill(['academic_priority' => $index + 1])->save());
    }

    private function reorderPriorities(Classroom $classroom, int $targetPriority): void
    {
        if (! $classroom->status) {
            $this->normalizePriorities((int) $classroom->academic_cycle_id);

            return;
        }

        $classrooms = Classroom::query()
            ->where('academic_cycle_id', $classroom->academic_cycle_id)
            ->where('status', true)
            ->whereKeyNot($classroom->id)
            ->orderBy('academic_priority')
            ->orderBy('id')
            ->get()
            ->values();

        $targetIndex = max(0, min($targetPriority - 1, $classrooms->count()));
        $ordered = $classrooms->all();
        array_splice($ordered, $targetIndex, 0, [$classroom]);

        foreach (array_values($ordered) as $index => $row) {
            $row->forceFill(['academic_priority' => $index + 1])->save();
        }
    }

    public function cycles(): Collection
    {
        return AcademicCycle::query()->orderByDesc('start_date')->orderByDesc('id')->get(['id', 'name']);
    }

    private function sanitize(array $data): array
    {
        foreach (['name', 'code', 'description'] as $key) {
            if (isset($data[$key]) && is_string($data[$key])) {
                $data[$key] = mb_substr(trim(strip_tags($data[$key])), 0, $key === 'description' ? 1000 : 120);
            }
        }
        $data['code'] = mb_strtoupper((string) $data['code']);

        return $data;
    }

    private function releaseDeletedCode(int $academicCycleId, string $code, ?int $ignoreId = null): void
    {
        Classroom::query()
            ->withTrashed()
            ->where('academic_cycle_id', $academicCycleId)
            ->where('code', $code)
            ->whereNotNull('deleted_at')
            ->when($ignoreId !== null, fn ($query) => $query->whereKeyNot($ignoreId))
            ->get()
            ->each(function (Classroom $classroom): void {
                $classroom->forceFill([
                    'code' => $this->deletedCode((string) $classroom->code, (int) $classroom->id),
                ])->save();
            });
    }

    private function deletedCode(string $code, int $id): string
    {
        if (str_ends_with($code, '-ELIMINADA-'.$id)) {
            return $code;
        }

        return mb_substr($code.'-ELIMINADA-'.$id, 0, 32);
    }
}
