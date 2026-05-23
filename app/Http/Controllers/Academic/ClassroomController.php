<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Http\Requests\Academic\StoreClassroomRequest;
use App\Http\Requests\Academic\UpdateClassroomRequest;
use App\Models\Classroom;
use App\Services\Academic\ClassroomService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClassroomController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Classroom::class, 'classroom');
    }

    public function index(Request $request, ClassroomService $service): View
    {
        return view('academic.classrooms.index', [
            'classrooms' => $service->paginate([
                'search' => mb_substr(trim((string) $request->query('search', '')), 0, 100),
                'academic_cycle_id' => $this->optionalInt($request->query('academic_cycle_id')),
            ]),
            'cycles' => $service->cycles(),
            'filters' => $request->only(['search', 'academic_cycle_id']),
        ]);
    }

    public function create(ClassroomService $service): View
    {
        return view('academic.classrooms.create', ['cycles' => $service->cycles()]);
    }

    public function store(StoreClassroomRequest $request, ClassroomService $service): RedirectResponse
    {
        $service->create($request->validated());

        return redirect()->route('academic.classrooms.index')->with('success', 'Aula registrada correctamente.');
    }

    public function edit(Classroom $classroom, ClassroomService $service): View
    {
        return view('academic.classrooms.edit', ['classroom' => $classroom, 'cycles' => $service->cycles()]);
    }

    public function update(UpdateClassroomRequest $request, Classroom $classroom, ClassroomService $service): RedirectResponse
    {
        $service->update($classroom, $request->validated());

        return redirect()->route('academic.classrooms.index')->with('success', 'Aula actualizada correctamente.');
    }

    public function destroy(Classroom $classroom, ClassroomService $service): RedirectResponse
    {
        $service->delete($classroom);

        return redirect()->route('academic.classrooms.index')->with('success', 'Aula eliminada correctamente.');
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
