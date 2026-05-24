<?php

namespace App\Services\Academic;

use App\Models\AcademicCycle;
use App\Models\Classroom;
use App\Models\Evaluation;
use App\Models\Grade;
use App\Models\Staff;
use App\Models\Student;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GradeService
{
    public function __construct(private readonly AcademicFileParser $parser) {}

    public function overview(int $academicCycleId, array $filters = []): array
    {
        $this->ensureDefaultEvaluations($academicCycleId);

        $evaluations = Evaluation::query()
            ->where('academic_cycle_id', $academicCycleId)
            ->orderBy('id')
            ->get();

        $students = $this->studentQuery($academicCycleId, $filters)
            ->with([
                'career:id,name',
                'schedule.shift:id,name',
                'classroomAssignments' => fn ($q) => $q->where('academic_cycle_id', $academicCycleId)->with('classroom:id,name,code'),
                'grades' => fn ($q) => $q->whereIn('evaluation_id', $evaluations->pluck('id'))->with('evaluation:id,weight,counts_for_average,rounding_decimals'),
            ])
            ->paginate($this->perPage($filters))
            ->withQueryString();

        $rankings = $this->rankingsForCycle($academicCycleId, $evaluations);

        return [
            'evaluations' => $evaluations,
            'students' => $students,
            'rankings' => $rankings,
        ];
    }

    public function createEvaluation(array $data, Staff $staff): Evaluation
    {
        $data['created_by'] = $staff->id;
        $data['type'] = Str::slug((string) $data['type'], '_') ?: 'regular';

        return Evaluation::query()->create($data);
    }

    public function updateEvaluation(Evaluation $evaluation, array $data): Evaluation
    {
        $data['type'] = Str::slug((string) $data['type'], '_') ?: 'regular';
        $evaluation->update($data);

        return $evaluation->refresh();
    }

    public function deleteEvaluation(Evaluation $evaluation): void
    {
        $evaluation->delete();
    }

    public function importGrades(int $academicCycleId, UploadedFile $file, Staff $staff): array
    {
        return $this->importGradeRows($academicCycleId, $this->parser->rows($file), $staff);
    }

    public function importGradesFromPath(int $academicCycleId, string $path, string $extension, Staff $staff): array
    {
        return $this->importGradeRows($academicCycleId, $this->parser->rowsFromPath($path, $extension), $staff);
    }

    public function previewGrades(int $academicCycleId, UploadedFile $file): array
    {
        return $this->analyzeGradeRows($academicCycleId, $this->parser->rows($file), false);
    }

    /**
     * @param  array<int, array<int, string>>  $rows
     * @return array<string, mixed>
     */
    private function importGradeRows(int $academicCycleId, array $rows, Staff $staff): array
    {
        $report = $this->analyzeGradeRows($academicCycleId, $rows, true, $staff);

        Log::info('Importación masiva de notas', ['staff_id' => $staff->id, 'academic_cycle_id' => $academicCycleId, 'report' => $report]);

        return $report;
    }

    /**
     * @param  array<int, array<int, string>>  $rows
     * @return array<string, mixed>
     */
    private function analyzeGradeRows(int $academicCycleId, array $rows, bool $persist, ?Staff $staff = null): array
    {
        $report = ['importados' => 0, 'errores' => [], 'omitidos' => 0];
        $report['validos'] = 0;
        $report['muestra'] = [];
        $seen = [];
        $evaluations = Evaluation::query()
            ->where('academic_cycle_id', $academicCycleId)
            ->get()
            ->keyBy(fn (Evaluation $evaluation): string => $this->normalizeName($evaluation->name));

        $callback = function () use ($rows, $academicCycleId, $staff, $persist, &$report, &$seen, $evaluations): void {
            foreach ($rows as $index => $row) {
                $line = $index + 2;
                $dni = trim((string) ($row[0] ?? ''));
                $evaluationName = trim((string) ($row[1] ?? ''));
                $scoreRaw = trim((string) ($row[2] ?? ''));

                if ($dni === '' && $evaluationName === '' && $scoreRaw === '') {
                    $report['omitidos']++;

                    continue;
                }

                $key = $dni.'|'.$this->normalizeName($evaluationName);
                $error = $this->validateGradeRow($dni, $evaluationName, $scoreRaw, $seen, $key);
                if ($error !== null) {
                    $report['errores'][] = "Fila {$line}: {$error}";

                    continue;
                }
                $seen[$key] = true;

                $student = Student::query()->where('dni', $dni)->where('academic_cycle_id', $academicCycleId)->first();
                if ($student === null) {
                    $report['errores'][] = "Fila {$line}: el alumno con DNI {$dni} no existe en el ciclo.";

                    continue;
                }
                if ($student->status !== Student::STATUS_ACTIVE) {
                    $report['errores'][] = "Fila {$line}: el alumno con DNI {$dni} no está activo. Solo se importan notas para alumnos activos.";

                    continue;
                }

                $evaluation = $evaluations[$this->normalizeName($evaluationName)] ?? null;
                if ($evaluation === null) {
                    $report['errores'][] = "Fila {$line}: la evaluación {$evaluationName} no existe.";

                    continue;
                }

                $report['validos']++;
                if (count($report['muestra']) < 20) {
                    $report['muestra'][] = [
                        'fila' => $line,
                        'dni' => $dni,
                        'alumno' => $student->fullName(),
                        'evaluacion' => $evaluation->name,
                        'nota' => (float) $scoreRaw,
                    ];
                }

                if ($persist) {
                    Grade::query()->updateOrCreate(
                        ['student_id' => $student->id, 'evaluation_id' => $evaluation->id],
                        ['score' => (float) $scoreRaw, 'created_by' => $staff?->id],
                    );
                    $report['importados']++;
                }
            }
        };

        $persist ? DB::transaction($callback) : $callback();

        return $report;
    }

    public function reports(int $academicCycleId, array $filters = []): array
    {
        $this->ensureDefaultEvaluations($academicCycleId);

        $evaluations = Evaluation::query()->where('academic_cycle_id', $academicCycleId)->get();
        $rankings = $this->rankingsForCycle($academicCycleId, $evaluations);
        $rows = $this->studentReportRows($academicCycleId, $filters, $evaluations);

        return [
            'ranking_general' => $rows->sortBy('ranking')->take(100)->values(),
            'ranking_aula' => $rows->groupBy('aula')->map(fn ($group) => $group->sortBy('ranking')->take(20)->values()),
            'ranking_carrera' => $rows->groupBy('carrera')->map(fn ($group) => $group->sortBy('ranking')->take(20)->values()),
            'ranking_turno' => $rows->groupBy('turno')->map(fn ($group) => $group->sortBy('ranking')->take(20)->values()),
            'promedio_general' => round((float) $rows->avg('promedio'), 2),
            'desaprobados' => $rows->where('promedio', '<', 11)->values(),
            'destacados' => $rows->where('promedio', '>=', 16)->values(),
            'rankings' => $rankings,
        ];
    }

    public function exportExcel(int $academicCycleId, array $filters = []): StreamedResponse
    {
        $evaluations = Evaluation::query()->where('academic_cycle_id', $academicCycleId)->get();
        $rows = $this->studentReportRows($academicCycleId, $filters, $evaluations);
        $filename = 'reporte-academico-'.now()->format('Ymd-His').'.xls';

        return response()->streamDownload(function () use ($rows): void {
            echo '<table><thead><tr><th>DNI</th><th>Alumno</th><th>Carrera</th><th>Turno</th><th>Aula</th><th>Promedio</th><th>Ranking</th></tr></thead><tbody>';
            foreach ($rows as $row) {
                echo '<tr><td>'.e($row->dni).'</td><td>'.e($row->alumno).'</td><td>'.e($row->carrera).'</td><td>'.e($row->turno).'</td><td>'.e($row->aula).'</td><td>'.e((string) $row->promedio).'</td><td>'.e((string) $row->ranking).'</td></tr>';
            }
            echo '</tbody></table>';
        }, $filename, ['Content-Type' => 'application/vnd.ms-excel; charset=UTF-8']);
    }

    public function cycles(): Collection
    {
        return AcademicCycle::query()->orderByDesc('start_date')->orderByDesc('id')->get(['id', 'name']);
    }

    public function classrooms(int $academicCycleId): Collection
    {
        return Classroom::query()->where('academic_cycle_id', $academicCycleId)->orderBy('academic_priority')->get(['id', 'name', 'code']);
    }

    private function studentQuery(int $academicCycleId, array $filters): Builder
    {
        return Student::query()
            ->where('academic_cycle_id', $academicCycleId)
            ->where('status', Student::STATUS_ACTIVE)
            ->when($filters['career_id'] ?? null, fn ($q, int $id) => $q->where('career_id', $id))
            ->when($filters['classroom_id'] ?? null, fn ($q, int $id) => $q->whereHas('classroomAssignments', fn ($a) => $a->where('academic_cycle_id', $academicCycleId)->where('classroom_id', $id)))
            ->when($filters['shift_id'] ?? null, fn ($q, int $id) => $q->whereHas('schedule', fn ($s) => $s->where('shift_id', $id)))
            ->when(($filters['search'] ?? '') !== '', function ($q) use ($filters): void {
                $search = $filters['search'];
                $q->where(fn ($inner) => $inner
                    ->where('dni', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('mother_last_name', 'like', "%{$search}%"));
            })
            ->orderBy('last_name')
            ->orderBy('mother_last_name')
            ->orderBy('first_name');
    }

    private function ensureDefaultEvaluations(int $academicCycleId): void
    {
        $defaults = [
            ['name' => 'Examen de ubicación', 'type' => Evaluation::TYPE_PLACEMENT, 'weight' => 0, 'counts_for_average' => false],
            ['name' => 'Examen medio ciclo', 'type' => 'medio_ciclo', 'weight' => 1, 'counts_for_average' => true],
            ['name' => 'Examen final', 'type' => 'final', 'weight' => 1, 'counts_for_average' => true],
        ];

        foreach ($defaults as $default) {
            $exists = Evaluation::query()
                ->where('academic_cycle_id', $academicCycleId)
                ->where(fn ($query) => $query->where('type', $default['type'])->orWhere('name', $default['name']))
                ->exists();

            if (! $exists) {
                Evaluation::query()->create([
                    'academic_cycle_id' => $academicCycleId,
                    'name' => $default['name'],
                    'type' => $default['type'],
                    'weight' => $default['weight'],
                    'counts_for_average' => $default['counts_for_average'],
                    'rounding_decimals' => 2,
                    'status' => true,
                ]);
            }
        }
    }

    private function rankingsForCycle(int $academicCycleId, Collection $evaluations): array
    {
        $rows = Student::query()
            ->where('academic_cycle_id', $academicCycleId)
            ->where('status', Student::STATUS_ACTIVE)
            ->with(['grades' => fn ($q) => $q->whereIn('evaluation_id', $evaluations->pluck('id'))->with('evaluation')])
            ->get(['id'])
            ->mapWithKeys(fn (Student $student) => [$student->id => $this->average($student, $evaluations)])
            ->sortDesc();

        $rankings = [];
        $position = 1;
        foreach ($rows as $studentId => $average) {
            $rankings[(int) $studentId] = ['promedio' => $average, 'ranking' => $position++];
        }

        return $rankings;
    }

    private function studentReportRows(int $academicCycleId, array $filters, Collection $evaluations)
    {
        $rankings = $this->rankingsForCycle($academicCycleId, $evaluations);

        return $this->studentQuery($academicCycleId, $filters)
            ->with(['career:id,name', 'schedule.shift:id,name', 'classroomAssignments' => fn ($q) => $q->where('academic_cycle_id', $academicCycleId)->with('classroom:id,name')])
            ->limit(2000)
            ->get()
            ->map(function (Student $student) use ($rankings) {
                $assignment = $student->classroomAssignments->first();

                return (object) [
                    'dni' => $student->dni,
                    'alumno' => $student->fullName(),
                    'carrera' => $student->career?->name ?? 'Sin carrera',
                    'turno' => $student->schedule?->shift?->name ?? 'Sin turno',
                    'aula' => $assignment?->classroom?->name ?? 'Sin aula',
                    'promedio' => $rankings[$student->id]['promedio'] ?? 0,
                    'ranking' => $rankings[$student->id]['ranking'] ?? null,
                ];
            });
    }

    private function average(Student $student, Collection $evaluations): float
    {
        $grades = $student->grades->keyBy('evaluation_id');
        $weighted = 0.0;
        $weightSum = 0.0;
        $decimals = 2;

        foreach ($evaluations as $evaluation) {
            if (! $evaluation->counts_for_average) {
                continue;
            }
            $grade = $grades[$evaluation->id] ?? null;
            if ($grade === null) {
                continue;
            }
            $weight = max(0.0, (float) $evaluation->weight);
            $weighted += (float) $grade->score * $weight;
            $weightSum += $weight;
            $decimals = max($decimals, (int) $evaluation->rounding_decimals);
        }

        return $weightSum > 0 ? round($weighted / $weightSum, $decimals) : 0.0;
    }

    private function validateGradeRow(string $dni, string $evaluation, string $scoreRaw, array $seen, string $key): ?string
    {
        if (! preg_match('/^\d{8}$/', $dni)) {
            return 'DNI inválido. Debe contener 8 dígitos.';
        }
        if ($evaluation === '') {
            return 'La evaluación es obligatoria.';
        }
        if (isset($seen[$key])) {
            return 'Nota duplicada en el archivo para el mismo alumno y evaluación.';
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

    private function normalizeName(string $value): string
    {
        return Str::slug(trim($value), '_');
    }

    private function perPage(array $filters): int
    {
        $perPage = (int) ($filters['per_page'] ?? 50);

        return in_array($perPage, [50, 100, 200], true) ? $perPage : 50;
    }
}
