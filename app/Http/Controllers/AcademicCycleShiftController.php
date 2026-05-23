<?php

namespace App\Http\Controllers;

use App\Http\Requests\AcademicCycleShift\StoreAcademicCycleShiftRequest;
use App\Http\Requests\AcademicCycleShift\UpdateAcademicCycleShiftRequest;
use App\Models\AcademicCycleShift;
use App\Services\AcademicCycleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AcademicCycleShiftController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(AcademicCycleShift::class, 'schedule', [
            'except' => ['index'],
        ]);
    }

    public function index(AcademicCycleService $academicCycleService): View
    {
        $this->authorize('viewAny', AcademicCycleShift::class);

        return view('academic-cycles.index', [
            'schedules' => $academicCycleService->paginateSchedules(),
        ]);
    }

    public function create(AcademicCycleService $academicCycleService): View
    {
        return view('academic-cycles.schedules.create', [
            'cycles' => $academicCycleService->cyclesForForms(),
            'campuses' => $academicCycleService->campusesForForms(),
            'shifts' => $academicCycleService->shiftsForForms(),
        ]);
    }

    public function store(StoreAcademicCycleShiftRequest $request, AcademicCycleService $academicCycleService): RedirectResponse
    {
        $academicCycleService->createSchedule($request->validated());

        return redirect()->route('academic-cycles.index')->with('success', 'Programación de turno registrada correctamente.');
    }

    public function edit(AcademicCycleShift $schedule, AcademicCycleService $academicCycleService): View
    {
        return view('academic-cycles.schedules.edit', [
            'schedule' => $schedule->load(['academicCycle', 'campus', 'shift']),
            'cycles' => $academicCycleService->cyclesForForms(),
            'campuses' => $academicCycleService->campusesForForms(),
            'shifts' => $academicCycleService->shiftsForForms(),
        ]);
    }

    public function update(UpdateAcademicCycleShiftRequest $request, AcademicCycleShift $schedule, AcademicCycleService $academicCycleService): RedirectResponse
    {
        $academicCycleService->updateSchedule($schedule, $request->validated());

        return redirect()->route('academic-cycles.index')->with('success', 'Programación actualizada correctamente.');
    }

    public function destroy(AcademicCycleShift $schedule, AcademicCycleService $academicCycleService): RedirectResponse
    {
        try {
            $academicCycleService->deleteSchedule($schedule);
        } catch (ValidationException $e) {
            return redirect()->route('academic-cycles.index')->withErrors($e->errors());
        }

        return redirect()->route('academic-cycles.index')->with('success', 'Programación eliminada correctamente.');
    }
}
