<?php

namespace App\Services\Academic;

use App\Models\Classroom;
use App\Models\ClassroomMovement;
use App\Models\Evaluation;
use App\Models\Grade;
use App\Models\Staff;
use App\Models\Student;
use App\Models\StudentClassroomAssignment;
use App\Support\Academic\AcademicGroupCatalog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AcademicDistributionService
{
    private const int MIN_GROUP_REMAINDER_TO_KEEP_SEPARATE = 20;

    public function __construct(private readonly AcademicFileParser $parser) {}

    public function dashboard(int $academicCycleId, array $filters = []): array
    {
        return [
            'classrooms' => Classroom::query()
                ->where('academic_cycle_id', $academicCycleId)
                ->withCount([
                    'assignments' => fn ($query) => $query->whereHas('student', fn ($student) => $student->where('status', Student::STATUS_ACTIVE)),
                ])
                ->orderBy('academic_priority')
                ->get(),
            'students' => $this->assignmentsQuery($academicCycleId, $filters)->paginate($this->perPage($filters))->withQueryString(),
            'movements' => ClassroomMovement::query()
                ->where('academic_cycle_id', $academicCycleId)
                ->with(['student:id,first_name,last_name,mother_last_name,dni', 'fromClassroom:id,name', 'toClassroom:id,name', 'movedBy:id,first_name,last_name'])
                ->latest('id')
                ->limit(15)
                ->get(),
        ];
    }

    public function importPlacementScores(int $academicCycleId, UploadedFile $file, Staff $staff): array
    {
        return $this->importPlacementRows($academicCycleId, $this->parser->rows($file), $staff);
    }

    public function importPlacementScoresFromPath(int $academicCycleId, string $path, string $extension, Staff $staff): array
    {
        return $this->importPlacementRows($academicCycleId, $this->parser->rowsFromPath($path, $extension), $staff);
    }

    public function previewPlacementScores(int $academicCycleId, UploadedFile $file): array
    {
        return $this->analyzePlacementRows($academicCycleId, $this->parser->rows($file), false);
    }

    /**
     * @param  array<int, array<int, string>>  $rows
     * @return array<string, mixed>
     */
    private function importPlacementRows(int $academicCycleId, array $rows, Staff $staff): array
    {
        $analysis = $this->analyzePlacementRows($academicCycleId, $rows, true, $staff);

        Log::info('Importación de examen de ubicación', ['staff_id' => $staff->id, 'academic_cycle_id' => $academicCycleId, 'report' => $analysis]);

        return $analysis;
    }

    /**
     * @param  array<int, array<int, string>>  $rows
     * @return array<string, mixed>
     */
    private function analyzePlacementRows(int $academicCycleId, array $rows, bool $persist, ?Staff $staff = null): array
    {
        $report = ['importados' => 0, 'errores' => [], 'omitidos' => 0];
        $report['validos'] = 0;
        $report['muestra'] = [];
        $seen = [];
        $validRows = [];

        $evaluation = Evaluation::query()
            ->where('academic_cycle_id', $academicCycleId)
            ->where(fn ($query) => $query->where('type', Evaluation::TYPE_PLACEMENT)->orWhere('name', 'Examen de ubicación'))
            ->first();

        if ($evaluation === null && ! $persist) {
            $evaluation = new Evaluation([
                'name' => 'Examen de ubicación',
                'type' => Evaluation::TYPE_PLACEMENT,
            ]);
        }

        if ($evaluation === null) {
            $evaluation = Evaluation::query()->create([
                'academic_cycle_id' => $academicCycleId,
                'name' => 'Examen de ubicación',
                'type' => Evaluation::TYPE_PLACEMENT,
                'weight' => 0,
                'counts_for_average' => false,
                'rounding_decimals' => 2,
                'status' => true,
                'created_by' => $staff->id,
            ]);
        }

        $candidateDnis = collect($rows)
            ->map(fn (array $row): string => trim((string) ($row[0] ?? '')))
            ->filter(fn (string $dni): bool => preg_match('/^\d{8}$/', $dni) === 1)
            ->unique()
            ->values();

        $students = Student::query()
            ->where('academic_cycle_id', $academicCycleId)
            ->whereIn('dni', $candidateDnis)
            ->get(['id', 'first_name', 'last_name', 'mother_last_name', 'dni', 'status'])
            ->keyBy('dni');

        $callback = function () use ($rows, $academicCycleId, $staff, $evaluation, $persist, &$report, &$seen, &$validRows, $students): void {
            foreach ($rows as $index => $row) {
                $line = $index + 2;
                $dni = trim((string) ($row[0] ?? ''));
                $scoreRaw = trim((string) ($row[1] ?? ''));

                if ($dni === '' && $scoreRaw === '') {
                    $report['omitidos']++;

                    continue;
                }

                $error = $this->validateScoreRow($dni, $scoreRaw, $seen);
                if ($error !== null) {
                    $report['errores'][] = "Fila {$line}: {$error}";

                    continue;
                }
                $seen[$dni] = true;

                $student = $students[$dni] ?? null;
                if ($student === null) {
                    $report['errores'][] = "Fila {$line}: el DNI {$dni} no existe en el ciclo seleccionado.";

                    continue;
                }
                if ($student->status !== Student::STATUS_ACTIVE) {
                    $report['errores'][] = "Fila {$line}: el alumno con DNI {$dni} no está activo. Solo se importan notas de ubicación para alumnos activos.";

                    continue;
                }

                $report['validos']++;
                $validRows[] = [
                    'student_id' => $student->id,
                    'score' => (float) $scoreRaw,
                ];
                if (count($report['muestra']) < 20) {
                    $report['muestra'][] = [
                        'fila' => $line,
                        'dni' => $dni,
                        'alumno' => $student->fullName(),
                        'nota' => (float) $scoreRaw,
                    ];
                }
            }

            if ($persist && $validRows !== []) {
                $now = now();
                foreach (array_chunk($validRows, 500) as $chunk) {
                    Grade::query()->upsert(
                        array_map(fn (array $row): array => [
                            'student_id' => $row['student_id'],
                            'evaluation_id' => $evaluation->id,
                            'score' => $row['score'],
                            'created_by' => $staff?->id,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ], $chunk),
                        ['student_id', 'evaluation_id'],
                        ['score', 'created_by', 'updated_at'],
                    );

                    StudentClassroomAssignment::query()->upsert(
                        array_map(fn (array $row): array => [
                            'student_id' => $row['student_id'],
                            'academic_cycle_id' => $academicCycleId,
                            'placement_score' => $row['score'],
                            'assigned_by' => $staff?->id,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ], $chunk),
                        ['student_id', 'academic_cycle_id'],
                        ['placement_score', 'assigned_by', 'updated_at'],
                    );
                }
                $report['importados'] = count($validRows);
            }
        };

        $persist ? DB::transaction($callback) : $callback();

        return $report;
    }

    public function distribute(int $academicCycleId, Staff $staff, bool $regenerate = false, bool $respectAcademicGroups = false): array
    {
        return DB::transaction(function () use ($academicCycleId, $staff, $regenerate, $respectAcademicGroups): array {
            $classrooms = Classroom::query()
                ->where('academic_cycle_id', $academicCycleId)
                ->where('status', true)
                ->withCount('assignments')
                ->orderBy('academic_priority')
                ->lockForUpdate()
                ->get();

            if ($classrooms->isEmpty()) {
                throw ValidationException::withMessages(['academic_cycle_id' => ['No hay aulas activas para distribuir alumnos.']]);
            }

            if ($regenerate) {
                StudentClassroomAssignment::query()
                    ->where('academic_cycle_id', $academicCycleId)
                    ->whereHas('student', fn ($query) => $query->where('status', Student::STATUS_ACTIVE))
                    ->where('distribution_locked', false)
                    ->update(['classroom_id' => null]);
            }

            $students = StudentClassroomAssignment::query()
                ->where('academic_cycle_id', $academicCycleId)
                ->whereHas('student', fn ($query) => $query->where('status', Student::STATUS_ACTIVE))
                ->where('distribution_locked', false)
                ->whereNotNull('placement_score')
                ->where(function ($q): void {
                    $q->whereNull('classroom_id')->orWhereDoesntHave('classroom');
                })
                ->with('student:id,first_name,last_name,mother_last_name,dni,career_id', 'student.career:id,code')
                ->orderByDesc('placement_score')
                ->orderBy('student_id')
                ->lockForUpdate()
                ->get();

            $usage = StudentClassroomAssignment::query()
                ->where('academic_cycle_id', $academicCycleId)
                ->whereHas('student', fn ($query) => $query->where('status', Student::STATUS_ACTIVE))
                ->whereNotNull('classroom_id')
                ->select('classroom_id', DB::raw('count(*) as total'))
                ->groupBy('classroom_id')
                ->pluck('total', 'classroom_id');

            [$assigned, $withoutCapacity] = $respectAcademicGroups
                ? $this->distributeRespectingAcademicGroups($students, $classrooms, $usage, $staff)
                : $this->distributeByScore($students, $classrooms, $usage, $staff);

            Log::info('Distribución académica ejecutada', ['staff_id' => $staff->id, 'academic_cycle_id' => $academicCycleId, 'assigned' => $assigned, 'respect_academic_groups' => $respectAcademicGroups]);

            return ['asignados' => $assigned, 'sin_cupo' => $withoutCapacity];
        });
    }

    private function distributeByScore($students, Collection $classrooms, $usage, Staff $staff): array
    {
        $assigned = 0;
        $withoutCapacity = 0;

        foreach ($students as $assignment) {
            $target = $classrooms->first(function (Classroom $classroom) use (&$usage): bool {
                return (int) ($usage[$classroom->id] ?? 0) < (int) $classroom->capacity;
            });

            if ($target === null) {
                $withoutCapacity++;

                continue;
            }

            $this->assignToClassroom($assignment, $target, $usage, $staff);
            $assigned++;
        }

        return [$assigned, $withoutCapacity];
    }

    private function distributeRespectingAcademicGroups($students, Collection $classrooms, $usage, Staff $staff): array
    {
        $pending = $this->pendingStudentsByAcademicGroup($students);
        $closedClassroomIds = [];
        $assigned = 0;

        foreach (array_keys($pending) as $group) {
            while (count($pending[$group]) > 0) {
                $target = $this->nextAvailableClassroom($classrooms, $usage, $closedClassroomIds);
                if ($target === null) {
                    return [$assigned, $this->pendingStudentCount($pending)];
                }

                $available = (int) $target->capacity - (int) ($usage[$target->id] ?? 0);
                $selected = array_splice($pending[$group], 0, $available);

                if (count($pending[$group]) === 0 && count($selected) < $available && count($selected) < self::MIN_GROUP_REMAINDER_TO_KEEP_SEPARATE) {
                    $selected = $this->fillWithCompatibleGroups($selected, $pending, $group, $available);
                    $selected = $this->fillWithAnyGroup($selected, $pending, $group, $available);
                }

                foreach ($selected as $assignment) {
                    $this->assignToClassroom($assignment, $target, $usage, $staff);
                    $assigned++;
                }

                $closedClassroomIds[$target->id] = true;
            }
        }

        return [$assigned, 0];
    }

    private function pendingStudentsByAcademicGroup($students): array
    {
        $groupOrder = array_keys(AcademicGroupCatalog::groups());
        $pending = array_fill_keys([...$groupOrder, 'ungrouped'], []);

        $students
            ->sort(function (StudentClassroomAssignment $a, StudentClassroomAssignment $b) use ($groupOrder): int {
                $order = array_flip($groupOrder);
                $leftGroup = AcademicGroupCatalog::groupForCareerCode($a->student?->career?->code) ?? 'ungrouped';
                $rightGroup = AcademicGroupCatalog::groupForCareerCode($b->student?->career?->code) ?? 'ungrouped';

                return [
                    $order[$leftGroup] ?? 999,
                    -1 * (float) $a->placement_score,
                    (int) $a->student_id,
                ] <=> [
                    $order[$rightGroup] ?? 999,
                    -1 * (float) $b->placement_score,
                    (int) $b->student_id,
                ];
            })
            ->each(function (StudentClassroomAssignment $assignment) use (&$pending): void {
                $group = AcademicGroupCatalog::groupForCareerCode($assignment->student?->career?->code) ?? 'ungrouped';
                $pending[$group][] = $assignment;
            });

        return $pending;
    }

    private function fillWithCompatibleGroups(array $selected, array &$pending, string $group, int $capacity): array
    {
        foreach (AcademicGroupCatalog::compatibleGroups($group) as $compatible) {
            while (count($selected) < $capacity && count($pending[$compatible] ?? []) > 0) {
                $selected[] = array_shift($pending[$compatible]);
            }
        }

        return $selected;
    }

    private function fillWithAnyGroup(array $selected, array &$pending, string $group, int $capacity): array
    {
        foreach (array_keys($pending) as $candidate) {
            if ($candidate === $group || in_array($candidate, AcademicGroupCatalog::compatibleGroups($group), true)) {
                continue;
            }

            while (count($selected) < $capacity && count($pending[$candidate]) > 0) {
                $selected[] = array_shift($pending[$candidate]);
            }
        }

        return $selected;
    }

    private function nextAvailableClassroom(Collection $classrooms, $usage, array $closedClassroomIds): ?Classroom
    {
        return $classrooms->first(function (Classroom $classroom) use ($usage, $closedClassroomIds): bool {
            return ! isset($closedClassroomIds[$classroom->id])
                && (int) ($usage[$classroom->id] ?? 0) < (int) $classroom->capacity;
        });
    }

    private function assignToClassroom(StudentClassroomAssignment $assignment, Classroom $classroom, $usage, Staff $staff): void
    {
        $assignment->update([
            'classroom_id' => $classroom->id,
            'assigned_by' => $staff->id,
            'assigned_at' => now(),
        ]);
        $usage[$classroom->id] = (int) ($usage[$classroom->id] ?? 0) + 1;
    }

    private function pendingStudentCount(array $pending): int
    {
        return array_sum(array_map('count', $pending));
    }

    public function moveStudent(int $academicCycleId, int $studentId, int $classroomId, Staff $staff, ?string $reason = null): void
    {
        DB::transaction(function () use ($academicCycleId, $studentId, $classroomId, $staff, $reason): void {
            $classroom = Classroom::query()->where('academic_cycle_id', $academicCycleId)->lockForUpdate()->findOrFail($classroomId);
            if (! $classroom->status) {
                throw ValidationException::withMessages(['classroom_id' => ['No se puede mover alumnos a un aula inactiva.']]);
            }

            $student = Student::query()->where('academic_cycle_id', $academicCycleId)->findOrFail($studentId);
            if ($student->status !== Student::STATUS_ACTIVE) {
                throw ValidationException::withMessages(['student_id' => ['Solo se puede asignar aula a alumnos activos.']]);
            }

            $used = StudentClassroomAssignment::query()
                ->where('academic_cycle_id', $academicCycleId)
                ->where('classroom_id', $classroomId)
                ->where('student_id', '!=', $studentId)
                ->whereHas('student', fn ($query) => $query->where('status', Student::STATUS_ACTIVE))
                ->lockForUpdate()
                ->count();
            if ($used >= $classroom->capacity) {
                throw ValidationException::withMessages(['classroom_id' => ['El aula seleccionada ya alcanzó su capacidad máxima.']]);
            }

            $assignment = StudentClassroomAssignment::query()
                ->where('academic_cycle_id', $academicCycleId)
                ->where('student_id', $studentId)
                ->lockForUpdate()
                ->firstOrFail();

            $from = $assignment->classroom_id;
            $assignment->update([
                'classroom_id' => $classroomId,
                'distribution_locked' => true,
                'assigned_by' => $staff->id,
                'assigned_at' => now(),
            ]);

            ClassroomMovement::query()->create([
                'student_id' => $studentId,
                'academic_cycle_id' => $academicCycleId,
                'from_classroom_id' => $from,
                'to_classroom_id' => $classroomId,
                'moved_by' => $staff->id,
                'reason' => $reason === null ? null : mb_substr(trim(strip_tags($reason)), 0, 500),
            ]);
        });
    }

    public function toggleLock(StudentClassroomAssignment $assignment): void
    {
        $assignment->update(['distribution_locked' => ! $assignment->distribution_locked]);
    }

    public function assignments(int $academicCycleId, array $filters = []): LengthAwarePaginator
    {
        return $this->assignmentsQuery($academicCycleId, $filters)->paginate($this->perPage($filters))->withQueryString();
    }

    public function activeClassrooms(int $academicCycleId): Collection
    {
        return Classroom::query()->where('academic_cycle_id', $academicCycleId)->where('status', true)->orderBy('academic_priority')->get();
    }

    private function assignmentsQuery(int $academicCycleId, array $filters)
    {
        return StudentClassroomAssignment::query()
            ->where('academic_cycle_id', $academicCycleId)
            ->whereHas('student', fn ($query) => $query->where('status', Student::STATUS_ACTIVE))
            ->with(['student.career:id,name,code', 'student.schedule.shift:id,name', 'classroom:id,name,code,capacity,status'])
            ->when($filters['classroom_id'] ?? null, fn ($q, int $id) => $q->where('classroom_id', $id))
            ->when($filters['academic_group'] ?? null, function ($q, string $group): void {
                $codes = AcademicGroupCatalog::careerCodesByGroup()[$group] ?? [];
                $q->whereHas('student.career', fn ($career) => $career->whereIn('code', $codes));
            })
            ->when(($filters['search'] ?? '') !== '', function ($q) use ($filters): void {
                $search = $filters['search'];
                $q->whereHas('student', fn ($s) => $s
                    ->where('dni', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('mother_last_name', 'like', "%{$search}%"));
            })
            ->orderByDesc('placement_score')
            ->orderBy('student_id');
    }

    private function validateScoreRow(string $dni, string $scoreRaw, array $seen): ?string
    {
        if (! preg_match('/^\d{8}$/', $dni)) {
            return 'DNI inválido. Debe contener 8 dígitos.';
        }
        if (isset($seen[$dni])) {
            return "DNI duplicado en el archivo: {$dni}.";
        }
        if (! is_numeric($scoreRaw)) {
            return 'La nota debe ser numérica.';
        }
        $score = (float) $scoreRaw;
        if ($score < 0 || $score > 20) {
            return 'La nota debe estar entre 0 y 20.';
        }

        return null;
    }

    private function perPage(array $filters): int
    {
        $perPage = (int) ($filters['per_page'] ?? 50);

        return in_array($perPage, [50, 100, 200], true) ? $perPage : 50;
    }
}
