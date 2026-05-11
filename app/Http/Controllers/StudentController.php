<?php

namespace App\Http\Controllers;

use App\Http\Requests\Student\StoreStudentRequest;
use App\Http\Requests\Student\UpdateStudentRequest;
use App\Models\Career;
use App\Models\Student;
use App\Services\StudentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Student::class, 'student');
    }

    public function index(StudentService $studentService): View
    {
        return view('students.index', [
            'students' => $studentService->paginateStudents(),
        ]);
    }

    public function create(StudentService $studentService): View
    {
        return view('students.create', [
            'schedules' => $studentService->schedulesForAdminForms(),
            'careers' => Career::query()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreStudentRequest $request, StudentService $studentService): RedirectResponse
    {
        $studentService->registerStudent($request->validated());

        return redirect()->route('students.index')->with('success', 'Alumno registrado correctamente.');
    }

    public function edit(Student $student, StudentService $studentService): View
    {
        return view('students.edit', [
            'student' => $student->load(['guardian', 'school', 'career', 'schedule.academicCycle', 'schedule.campus', 'schedule.shift']),
            'schedules' => $studentService->schedulesForAdminForms($student),
            'careers' => Career::query()->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateStudentRequest $request, Student $student, StudentService $studentService): RedirectResponse
    {
        $studentService->updateStudent($student, $request->validated());

        return redirect()->route('students.index')->with('success', 'Alumno actualizado correctamente.');
    }

    public function destroy(Student $student, StudentService $studentService): RedirectResponse
    {
        $studentService->deleteStudent($student);

        return redirect()->route('students.index')->with('success', 'Alumno eliminado correctamente.');
    }
}
