<?php

namespace App\Http\Controllers;

use App\Http\Requests\Student\StoreStudentRequest;
use App\Services\StudentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PreRegistrationController extends Controller
{
    /** Formulario publico de postulacion. */
    public function create(StudentService $studentService): View
    {
        return view('public.pre-registration', [
            'schedules' => $studentService->availableSchedulesWithRelations(),
            'careers' => $studentService->activeCareers(),
        ]);
    }

    /** Registro sin cuenta de usuario. */
    public function store(StoreStudentRequest $request, StudentService $studentService): RedirectResponse
    {
        $studentService->registerStudent($request->validated());

        return redirect()
            ->route('pre-registration.create')
            ->with('success', 'Su postulacion fue registrada correctamente. Nos pondremos en contacto.');
    }
}
