<?php

namespace App\Services;

use App\Models\Student;

/**
 * Resultado del flujo publico de inscripcion finalizada.
 */
final class StudentRegistrationCompletionResult
{
    public function __construct(
        public readonly Student $student,
        public readonly StudentMailSendOutcome $mailOutcome,
        /** @var array<string, string> */
        public readonly array $documentDownloads = [],
    ) {}
}
