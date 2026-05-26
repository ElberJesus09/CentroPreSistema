<?php

namespace App\Services;

use App\Models\ExamSetting;
use Illuminate\Support\Facades\URL;

/**
 * Orquesta el registro publico y el correo de confirmacion (PDF + SMTP).
 */
class PublicRegistrationCompletionService
{
    public function __construct(
        private readonly StudentService $studentService,
        private readonly StudentMailService $studentMailService,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public function finalize(array $validated): StudentRegistrationCompletionResult
    {
        $student = $this->studentService->registerStudent($validated);
        $settings = ExamSetting::singleton();

        if (! $settings->registration_mail_enabled) {
            return new StudentRegistrationCompletionResult(
                $student,
                StudentMailSendOutcome::failure('El envio automatico de correo esta desactivado temporalmente. Descargue sus documentos de inscripcion en esta pantalla.'),
                [
                    'enrollment_form' => URL::temporarySignedRoute(
                        'registration.documents.download',
                        now()->addMinutes(30),
                        ['student' => $student, 'document' => 'enrollment_form'],
                        absolute: false,
                    ),
                    'regulations' => URL::temporarySignedRoute(
                        'registration.documents.download',
                        now()->addMinutes(30),
                        ['student' => $student, 'document' => 'regulations'],
                        absolute: false,
                    ),
                ],
            );
        }

        $mailOutcome = $this->studentMailService->sendAutomaticRegistrationConfirmation($student);

        return new StudentRegistrationCompletionResult($student, $mailOutcome);
    }
}
