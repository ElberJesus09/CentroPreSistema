<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\PublicRegistration\FinalizePublicRegistrationRequest;
use App\Http\Requests\PublicRegistration\RegistrationStep1Request;
use App\Http\Requests\PublicRegistration\RegistrationStep2Request;
use App\Http\Requests\PublicRegistration\RegistrationStep3Request;
use App\Http\Requests\PublicRegistration\RegistrationStep4Request;
use App\Models\AcademicCycleShift;
use App\Models\Career;
use App\Models\ExamSetting;
use App\Models\Student;
use App\Services\PublicRegistrationCompletionService;
use App\Services\StudentPdfService;
use App\Services\StudentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class RegistrationWizardController extends Controller
{
    private const string SESSION_KEY = 'public_registration';

    public function start(): RedirectResponse
    {
        return redirect()->route('registration.step.show', ['step' => 1]);
    }

    /** Wizard multi-paso: muestra paso o redirige si falta informacion previa. */
    public function show(Request $request, StudentService $studentService, int $step): View|RedirectResponse
    {
        if ($step < 1 || $step > 5) {
            return redirect()->route('registration.step.show', ['step' => 1]);
        }

        if ($request->boolean('reset')) {
            $request->session()->forget(self::SESSION_KEY);

            return redirect()->route('registration.step.show', ['step' => 1])
                ->with('success', 'Formulario reiniciado.');
        }

        $draft = $request->session()->get(self::SESSION_KEY, []);
        $redirect = $this->ensurePrerequisites($step, $draft);
        if ($redirect !== null) {
            return $redirect;
        }

        $careers = $studentService->cachedActiveCareers();
        $schedules = $studentService->cachedPublicAvailableSchedules();

        $previewCareer = null;
        $previewSchedule = null;
        if ($step === 5) {
            $previewCareer = isset($draft['career_id']) ? Career::query()->find($draft['career_id']) : null;
            $previewSchedule = isset($draft['academic_cycle_shift_id'])
                ? AcademicCycleShift::query()->with(['academicCycle', 'campus', 'shift'])->find($draft['academic_cycle_shift_id'])
                : null;
        }

        return view('public.registration.wizard', [
            'step' => $step,
            'draft' => $draft,
            'careers' => $careers,
            'schedules' => $schedules,
            'previewCareer' => $previewCareer,
            'previewSchedule' => $previewSchedule,
        ]);
    }

    public function storeStep1(RegistrationStep1Request $request, StudentService $studentService): RedirectResponse
    {
        $draft = $request->session()->get(self::SESSION_KEY, []);
        $draft['student'] = $request->validated('student');

        $profile = $studentService->profileForDni((string) $draft['student']['dni']);
        if (is_array($profile)) {
            $draft['guardian'] ??= $profile['guardian'] ?? null;
            $draft['school'] ??= $profile['school'] ?? null;
        }

        $request->session()->put(self::SESSION_KEY, $draft);

        return redirect()->route('registration.step.show', ['step' => 2]);
    }

    public function lookupDni(Request $request, StudentService $studentService): JsonResponse
    {
        $dni = (string) $request->query('dni', '');
        $profile = $studentService->profileForDni($dni);

        return response()->json([
            'found' => $profile !== null,
            'profile' => $profile,
        ]);
    }

    public function storeStep2(RegistrationStep2Request $request): RedirectResponse
    {
        $draft = $request->session()->get(self::SESSION_KEY, []);
        if (! isset($draft['student'])) {
            return redirect()->route('registration.step.show', ['step' => 1]);
        }
        $draft['guardian'] = $request->validated('guardian');
        $request->session()->put(self::SESSION_KEY, $draft);

        return redirect()->route('registration.step.show', ['step' => 3]);
    }

    public function storeStep3(RegistrationStep3Request $request): RedirectResponse
    {
        $draft = $request->session()->get(self::SESSION_KEY, []);
        if (! isset($draft['student'], $draft['guardian'])) {
            return redirect()->route('registration.step.show', ['step' => 1]);
        }
        $draft['school'] = $request->validated('school');
        $request->session()->put(self::SESSION_KEY, $draft);

        return redirect()->route('registration.step.show', ['step' => 4]);
    }

    public function storeStep4(RegistrationStep4Request $request): RedirectResponse
    {
        $draft = $request->session()->get(self::SESSION_KEY, []);
        if (! isset($draft['student'], $draft['guardian'], $draft['school'])) {
            return redirect()->route('registration.step.show', ['step' => 1]);
        }
        $validated = $request->validated();
        $draft['career_id'] = (int) $validated['career_id'];
        $draft['academic_cycle_shift_id'] = (int) $validated['academic_cycle_shift_id'];
        $draft['student'] = array_merge($draft['student'], $validated['student']);
        $request->session()->put(self::SESSION_KEY, $draft);

        return redirect()->route('registration.step.show', ['step' => 5]);
    }

    /** Persistencia final: registro + documentos + correo (delegado a servicios). */
    public function finish(
        FinalizePublicRegistrationRequest $request,
        PublicRegistrationCompletionService $completionService,
    ): RedirectResponse {
        $result = $completionService->finalize($request->validated());
        $request->session()->forget(self::SESSION_KEY);

        $flash = [
            'student_name' => $result->student->fullName(),
            'mail_sent' => $result->mailOutcome->sent,
            'mail_message' => $result->mailOutcome->userMessage,
            'document_downloads' => $result->documentDownloads,
        ];

        return redirect()->route('registration.complete')->with('registration_complete', $flash);
    }

    /** Pantalla de confirmacion tras inscripcion exitosa. */
    public function complete(Request $request): View|RedirectResponse
    {
        $payload = $request->session()->pull('registration_complete');
        if (! is_array($payload)) {
            return redirect()->route('registration.start');
        }

        return view('public.registration.complete', [
            'summary' => $payload,
            'exam' => ExamSetting::singleton(),
        ]);
    }

    public function downloadDocument(Student $student, string $document, StudentPdfService $studentPdfService): Response
    {
        return $studentPdfService->downloadRegistrationDocument($student, $document);
    }

    /**
     * @param  array<string, mixed>  $draft
     */
    private function ensurePrerequisites(int $step, array $draft): ?RedirectResponse
    {
        if ($step >= 2 && ! isset($draft['student'])) {
            return redirect()->route('registration.step.show', ['step' => 1]);
        }
        if ($step >= 3 && ! isset($draft['guardian'])) {
            return redirect()->route('registration.step.show', ['step' => 2]);
        }
        if ($step >= 4 && ! isset($draft['school'])) {
            return redirect()->route('registration.step.show', ['step' => 3]);
        }
        if ($step >= 5 && (! isset($draft['career_id'], $draft['academic_cycle_shift_id']))) {
            return redirect()->route('registration.step.show', ['step' => 4]);
        }

        return null;
    }
}
