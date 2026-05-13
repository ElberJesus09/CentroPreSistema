<?php

namespace App\Services;

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
        $mailOutcome = $this->studentMailService->sendAutomaticRegistrationConfirmation($student);

        return new StudentRegistrationCompletionResult($student, $mailOutcome);
    }
}
