<?php

namespace App\Http\Controllers;

use App\Http\Requests\Shift\StoreShiftRequest;
use App\Http\Requests\Shift\UpdateShiftRequest;
use App\Models\Shift;
use App\Services\AcademicCycleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ShiftController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Shift::class, 'shift');
    }

    public function index(AcademicCycleService $academicCycleService): View
    {
        return view('academic-cycles.shifts.index', [
            'shifts' => $academicCycleService->paginateShifts(),
        ]);
    }

    public function create(): View
    {
        return view('academic-cycles.shifts.create');
    }

    public function store(StoreShiftRequest $request, AcademicCycleService $academicCycleService): RedirectResponse
    {
        $academicCycleService->createShift($request->validated());

        return redirect()->route('academic-cycles.shifts.index')->with('success', 'Turno creado correctamente.');
    }

    public function edit(Shift $shift): View
    {
        return view('academic-cycles.shifts.edit', [
            'shift' => $shift,
        ]);
    }

    public function update(UpdateShiftRequest $request, Shift $shift, AcademicCycleService $academicCycleService): RedirectResponse
    {
        $academicCycleService->updateShift($shift, $request->validated());

        return redirect()->route('academic-cycles.shifts.index')->with('success', 'Turno actualizado correctamente.');
    }

    public function destroy(Shift $shift, AcademicCycleService $academicCycleService): RedirectResponse
    {
        try {
            $academicCycleService->deleteShift($shift);
        } catch (ValidationException $e) {
            return redirect()->route('academic-cycles.shifts.index')->withErrors($e->errors());
        }

        return redirect()->route('academic-cycles.shifts.index')->with('success', 'Turno eliminado correctamente.');
    }
}
