<?php

namespace App\Http\Controllers;

use App\Http\Requests\Campus\StoreCampusRequest;
use App\Http\Requests\Campus\UpdateCampusRequest;
use App\Models\Campus;
use App\Services\AcademicCycleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CampusController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Campus::class, 'campus');
    }

    public function index(AcademicCycleService $academicCycleService): View
    {
        return view('academic-cycles.campuses.index', [
            'campuses' => $academicCycleService->paginateCampuses(),
        ]);
    }

    public function create(): View
    {
        return view('academic-cycles.campuses.create');
    }

    public function store(StoreCampusRequest $request, AcademicCycleService $academicCycleService): RedirectResponse
    {
        $academicCycleService->createCampus($request->validated());

        return redirect()->route('academic-cycles.campuses.index')->with('success', 'Sede creada correctamente.');
    }

    public function edit(Campus $campus): View
    {
        return view('academic-cycles.campuses.edit', [
            'campus' => $campus,
        ]);
    }

    public function update(UpdateCampusRequest $request, Campus $campus, AcademicCycleService $academicCycleService): RedirectResponse
    {
        $academicCycleService->updateCampus($campus, $request->validated());

        return redirect()->route('academic-cycles.campuses.index')->with('success', 'Sede actualizada correctamente.');
    }

    public function destroy(Campus $campus, AcademicCycleService $academicCycleService): RedirectResponse
    {
        try {
            $academicCycleService->deleteCampus($campus);
        } catch (ValidationException $e) {
            return redirect()->route('academic-cycles.campuses.index')->withErrors($e->errors());
        }

        return redirect()->route('academic-cycles.campuses.index')->with('success', 'Sede eliminada correctamente.');
    }
}
