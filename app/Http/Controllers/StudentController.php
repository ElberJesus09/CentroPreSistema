<?php

namespace App\Http\Controllers;

use App\Http\Requests\Student\StoreStudentRequest;
use App\Http\Requests\Student\UpdateStudentRequest;
use App\Models\Career;
use App\Models\Student;
use App\Services\StudentMailService;
use App\Services\StudentPdfService;
use App\Services\StudentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class StudentController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Student::class, 'student');
    }

    public function index(Request $request, StudentService $studentService): View
    {
        $filters = [
            'search' => mb_substr(trim((string) $request->query('search', '')), 0, 100),
            'year' => $this->optionalInt($request->query('year')),
            'academic_cycle_id' => $this->optionalInt($request->query('academic_cycle_id')),
        ];

        return view('students.index', [
            'students' => $studentService->paginateStudents(filters: $filters),
            'filterYears' => $studentService->studentFilterYears(),
            'filterCycles' => $studentService->studentFilterCycles(),
            'filters' => $filters,
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

    public function show(Student $student, StudentService $studentService): View
    {
        return view('students.show', [
            'student' => $studentService->studentForAdminView($student),
        ]);
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

    public function resendRegistrationMail(Request $request, Student $student, StudentMailService $studentMailService): RedirectResponse
    {
        $this->authorize('resendRegistrationMail', $student);
        $staff = $request->user();
        if ($staff === null) {
            abort(403);
        }

        $outcome = $studentMailService->sendManualResend($student, $staff);

        if ($outcome->sent) {
            return redirect()->route('students.index')->with('success', 'Correo de confirmación reenviado correctamente.');
        }

        return redirect()->route('students.index')->with('warning', $outcome->userMessage ?? 'No se pudo reenviar el correo.');
    }

    /** PDFs institucionales (ZIP) sin enviar correo. */
    public function downloadRegistrationDocuments(Student $student, StudentPdfService $studentPdfService): BinaryFileResponse
    {
        $this->authorize('downloadRegistrationDocuments', $student);

        $pack = $studentPdfService->createRegistrationDocumentsZip($student);

        return response()->download($pack['path'], $pack['download_name'])->deleteFileAfterSend(true);
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
