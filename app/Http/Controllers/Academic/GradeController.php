<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Http\Requests\Academic\ImportAcademicFileRequest;
use App\Http\Requests\Academic\StoreEvaluationRequest;
use App\Http\Requests\Academic\UpdateEvaluationRequest;
use App\Models\Evaluation;
use App\Services\Academic\GradeService;
use App\Services\ReportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class GradeController extends Controller
{
    public function index(Request $request, GradeService $service, ReportService $reportService): View
    {
        $cycleId = $this->cycleId($request, $service);
        $filters = [
            'search' => mb_substr(trim((string) $request->query('search', '')), 0, 100),
            'career_id' => $this->optionalInt($request->query('career_id')),
            'shift_id' => $this->optionalInt($request->query('shift_id')),
            'classroom_id' => $this->optionalInt($request->query('classroom_id')),
            'status' => $this->optionalString($request->query('status')),
            'per_page' => $this->optionalInt($request->query('per_page')) ?? 50,
        ];

        return view('academic.grades.index', [
            'cycles' => $service->cycles(),
            'cycleId' => $cycleId,
            'filters' => $filters,
            'overview' => $cycleId === null ? null : $service->overview($cycleId, $filters),
            'careers' => $reportService->filterCareerOptions(),
            'shifts' => $reportService->filterShiftOptions(),
            'classrooms' => $cycleId === null ? collect() : $service->classrooms($cycleId),
        ]);
    }

    public function storeEvaluation(StoreEvaluationRequest $request, GradeService $service): RedirectResponse
    {
        $service->createEvaluation($request->validated(), $request->user());

        return back()->with('success', 'Evaluación registrada correctamente.');
    }

    public function updateEvaluation(UpdateEvaluationRequest $request, Evaluation $evaluation, GradeService $service): RedirectResponse
    {
        $service->updateEvaluation($evaluation, $request->validated());

        return back()->with('success', 'Evaluación actualizada correctamente.');
    }

    public function destroyEvaluation(Evaluation $evaluation, GradeService $service): RedirectResponse
    {
        $this->authorize('delete', $evaluation);
        $service->deleteEvaluation($evaluation);

        return back()->with('success', 'Evaluación eliminada correctamente.');
    }

    public function import(ImportAcademicFileRequest $request, GradeService $service): RedirectResponse
    {
        $file = $request->file('file');
        $preview = $service->previewGrades($request->integer('academic_cycle_id'), $file);
        $path = $file->store('academic-imports');

        session([
            'academic_grades_import' => [
                'academic_cycle_id' => $request->integer('academic_cycle_id'),
                'path' => $path,
                'extension' => mb_strtolower($file->getClientOriginalExtension()),
            ],
        ]);

        return back()
            ->with('success', "Vista previa generada: {$preview['validos']} filas válidas, {$preview['omitidos']} omitidas.")
            ->with('grades_preview', $preview);
    }

    public function confirmImport(Request $request, GradeService $service): RedirectResponse
    {
        $this->authorizeAcademicImport($request);

        $data = session('academic_grades_import');
        if (! is_array($data) || ! Storage::exists((string) ($data['path'] ?? ''))) {
            return back()->with('warning', 'La vista previa venció. Vuelve a cargar el archivo.');
        }

        $report = $service->importGradesFromPath(
            (int) $data['academic_cycle_id'],
            Storage::path((string) $data['path']),
            (string) $data['extension'],
            $request->user(),
        );
        Storage::delete((string) $data['path']);
        session()->forget('academic_grades_import');

        return back()->with('success', "Importación finalizada: {$report['importados']} notas registradas, {$report['omitidos']} filas omitidas.")
            ->with('import_errors', $report['errores']);
    }

    private function cycleId(Request $request, GradeService $service): ?int
    {
        return $this->optionalInt($request->query('academic_cycle_id')) ?? $service->cycles()->first()?->id;
    }

    private function optionalInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }
        $i = filter_var($value, FILTER_VALIDATE_INT);

        return $i === false ? null : $i;
    }

    private function optionalString(mixed $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        return mb_substr(trim($value), 0, 40);
    }

    private function authorizeAcademicImport(Request $request): void
    {
        $user = $request->user();

        abort_unless(
            $user !== null && ($user->can('academic.imports.manage') || $user->isSuperAdmin() || $user->isAdmin()),
            403,
        );
    }
}
