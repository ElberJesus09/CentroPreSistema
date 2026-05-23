<?php

namespace App\Http\Controllers;

use App\Http\Requests\AcademicCycle\StoreAcademicCycleRequest;
use App\Http\Requests\AcademicCycle\UpdateAcademicCycleRequest;
use App\Models\AcademicCycle;
use App\Services\AcademicCycleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AcademicCycleController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(AcademicCycle::class, 'academic_cycle');
    }

    public function index(AcademicCycleService $academicCycleService): View
    {
        return view('academic-cycles.cycles.index', [
            'cycles' => $academicCycleService->paginateCycles(),
        ]);
    }

    public function create(): View
    {
        return view('academic-cycles.cycles.create');
    }

    public function store(StoreAcademicCycleRequest $request, AcademicCycleService $academicCycleService): RedirectResponse
    {
        $academicCycleService->createCycle($request->validated());

        return redirect()->route('academic-cycles.cycles.index')->with('success', 'Ciclo académico creado correctamente.');
    }

    public function edit(AcademicCycle $academic_cycle): View
    {
        return view('academic-cycles.cycles.edit', [
            'cycle' => $academic_cycle,
        ]);
    }

    public function update(UpdateAcademicCycleRequest $request, AcademicCycle $academic_cycle, AcademicCycleService $academicCycleService): RedirectResponse
    {
        $academicCycleService->updateCycle($academic_cycle, $request->validated());

        return redirect()->route('academic-cycles.cycles.index')->with('success', 'Ciclo académico actualizado correctamente.');
    }

    public function destroy(AcademicCycle $academic_cycle, AcademicCycleService $academicCycleService): RedirectResponse
    {
        try {
            $academicCycleService->deleteCycle($academic_cycle);
        } catch (ValidationException $e) {
            return redirect()->route('academic-cycles.cycles.index')->withErrors($e->errors());
        }

        return redirect()->route('academic-cycles.cycles.index')->with('success', 'Ciclo académico eliminado correctamente.');
    }
}
