<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Http\Requests\Academic\ImportAcademicFileRequest;
use App\Http\Requests\Academic\MoveStudentRequest;
use App\Models\StudentClassroomAssignment;
use App\Services\Academic\AcademicDistributionService;
use App\Services\Academic\ClassroomService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DistributionController extends Controller
{
    public function index(Request $request, ClassroomService $classrooms, AcademicDistributionService $service): View
    {
        $cycleId = $this->cycleId($request, $classrooms);
        $filters = [
            'search' => mb_substr(trim((string) $request->query('search', '')), 0, 100),
            'classroom_id' => $this->optionalInt($request->query('classroom_id')),
            'per_page' => $this->optionalInt($request->query('per_page')) ?? 50,
        ];

        return view('academic.distribution.index', [
            'cycles' => $classrooms->cycles(),
            'cycleId' => $cycleId,
            'filters' => $filters,
            'dashboard' => $cycleId === null ? null : $service->dashboard($cycleId, $filters),
            'activeClassrooms' => $cycleId === null ? collect() : $service->activeClassrooms($cycleId),
        ]);
    }

    public function import(ImportAcademicFileRequest $request, AcademicDistributionService $service): RedirectResponse
    {
        $file = $request->file('file');
        $preview = $service->previewPlacementScores($request->integer('academic_cycle_id'), $file);
        $path = $file->store('academic-imports');

        session([
            'academic_placement_import' => [
                'academic_cycle_id' => $request->integer('academic_cycle_id'),
                'path' => $path,
                'extension' => mb_strtolower($file->getClientOriginalExtension()),
            ],
        ]);

        return back()
            ->with('success', "Vista previa generada: {$preview['validos']} filas validas, {$preview['omitidos']} omitidas.")
            ->with('placement_preview', $preview);
    }

    public function confirmImport(Request $request, AcademicDistributionService $service): RedirectResponse
    {
        $data = session('academic_placement_import');
        if (! is_array($data) || ! Storage::exists((string) ($data['path'] ?? ''))) {
            return back()->with('warning', 'La vista previa venció. Vuelve a cargar el archivo.');
        }

        $report = $service->importPlacementScoresFromPath(
            (int) $data['academic_cycle_id'],
            Storage::path((string) $data['path']),
            (string) $data['extension'],
            $request->user(),
        );
        Storage::delete((string) $data['path']);
        session()->forget('academic_placement_import');

        return back()->with('success', "Importacion finalizada: {$report['importados']} notas registradas, {$report['omitidos']} filas omitidas.")
            ->with('import_errors', $report['errores']);
    }

    public function distribute(Request $request, AcademicDistributionService $service): RedirectResponse
    {
        $request->validate([
            'academic_cycle_id' => ['required', 'integer', 'exists:academic_cycles,id'],
            'regenerate' => ['nullable', 'boolean'],
        ]);

        $report = $service->distribute($request->integer('academic_cycle_id'), $request->user(), $request->boolean('regenerate'));

        return back()->with('success', "Distribucion completada: {$report['asignados']} alumnos asignados, {$report['sin_cupo']} sin cupo.");
    }

    public function move(MoveStudentRequest $request, AcademicDistributionService $service): RedirectResponse
    {
        $service->moveStudent($request->integer('academic_cycle_id'), $request->integer('student_id'), $request->integer('classroom_id'), $request->user(), $request->validated('reason'));

        return back()->with('success', 'Alumno movido correctamente. La distribucion quedo bloqueada para este alumno.');
    }

    public function toggleLock(StudentClassroomAssignment $assignment, AcademicDistributionService $service): RedirectResponse
    {
        $service->toggleLock($assignment);

        return back()->with('success', $assignment->fresh()?->distribution_locked ? 'Alumno bloqueado para conservar su aula.' : 'Alumno desbloqueado para redistribucion.');
    }

    private function cycleId(Request $request, ClassroomService $classrooms): ?int
    {
        $selected = $this->optionalInt($request->query('academic_cycle_id'));
        if ($selected !== null) {
            return $selected;
        }

        return $classrooms->cycles()->first()?->id;
    }

    private function optionalInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }
        $i = filter_var($value, FILTER_VALIDATE_INT);

        return $i === false ? null : $i;
    }
}
