<?php

namespace App\Jobs;

use App\Models\Student;
use App\Services\StudentMailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Extension futura: envio asincrono con reintentos (activar desde StudentMailService
 * cuando config('student_mail.queue_enabled') pase a true en despliegue).
 */
class SendRegistrationConfirmationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public readonly int $studentId,
    ) {}

    public function handle(StudentMailService $studentMailService): void
    {
        $student = Student::query()->findOrFail($this->studentId);
        $studentMailService->sendAutomaticRegistrationConfirmation($student);
    }
}
