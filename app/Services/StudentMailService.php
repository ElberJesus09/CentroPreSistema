<?php

namespace App\Services;

use App\Mail\RegistrationConfirmationMail;
use App\Models\ExamSetting;
use App\Models\Staff;
use App\Models\Student;
use App\Models\StudentMailLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Envío SMTP de confirmación de inscripción (Resend) con PDFs adjuntos.
 * Cola futura: ver SendRegistrationConfirmationJob y config('student_mail.queue_enabled').
 */
class StudentMailService
{
    public function __construct(
        private readonly StudentPdfService $studentPdfService,
    ) {}

    public function sendAutomaticRegistrationConfirmation(Student $student): StudentMailSendOutcome
    {
        return $this->dispatchRegistrationMail(
            $student,
            StudentMailLog::CHANNEL_REGISTRATION_AUTO,
            null,
        );
    }

    public function sendManualResend(Student $student, Staff $actor): StudentMailSendOutcome
    {
        return $this->dispatchRegistrationMail(
            $student,
            StudentMailLog::CHANNEL_REGISTRATION_MANUAL_RESEND,
            $actor,
        );
    }

    private function dispatchRegistrationMail(
        Student $student,
        string $channel,
        ?Staff $actor,
    ): StudentMailSendOutcome {
        $student->loadMissing([
            'guardian',
            'school',
            'career',
            'schedule.academicCycle',
            'schedule.campus',
            'schedule.shift',
        ]);

        $email = $student->email;
        if (! is_string($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->recordPrecheckFailure(
                $student,
                $channel,
                $actor,
                'Dirección de correo del alumno no válida.',
                ['recipient' => $email],
                'La dirección de correo del postulante no es válida.',
            );
        }

        $paths = [];
        try {
            $paths = $this->studentPdfService->buildRegistrationAttachmentFiles($student);
            if (! $this->attachmentsLookValid($paths)) {
                return $this->recordPrecheckFailure(
                    $student,
                    $channel,
                    $actor,
                    'No se generaron los documentos PDF adjuntos.',
                    [],
                    'No se pudieron generar los documentos PDF. Inténtelo más tarde o contacte a secretaría.',
                );
            }

            $exam = ExamSetting::singleton();
            $mailable = new RegistrationConfirmationMail(
                $student,
                $exam,
                $paths,
                (string) config('student_mail.attachment_enrollment_filename'),
                (string) config('student_mail.attachment_regulations_filename'),
            );

            Mail::to($email)->send($mailable);
        } catch (Throwable $e) {
            Log::channel('student_mail')->error('registration_mail_failed', [
                'student_id' => $student->id,
                'channel' => $channel,
                'triggered_by_staff_id' => $actor?->id,
                'exception' => $e::class,
                'message' => $e->getMessage(),
            ]);

            $this->persistLog(
                $student,
                $channel,
                StudentMailLog::STATUS_FAILED,
                $e->getMessage(),
                $actor,
                ['recipient' => $email],
            );

            return StudentMailSendOutcome::failure(
                $this->friendlySmtpFailureMessage($e),
            );
        } finally {
            $this->studentPdfService->deleteIfExists($paths);
        }

        $this->persistLog(
            $student,
            $channel,
            StudentMailLog::STATUS_SUCCEEDED,
            null,
            $actor,
            ['recipient' => $email],
        );

        Log::channel('student_mail')->info('registration_mail_sent', [
            'student_id' => $student->id,
            'channel' => $channel,
            'triggered_by_staff_id' => $actor?->id,
        ]);

        return StudentMailSendOutcome::success();
    }

    /** Mensaje al usuario según error SMTP (p. ej. límites de prueba de Resend). */
    private function friendlySmtpFailureMessage(Throwable $e): string
    {
        $raw = $e->getMessage();
        $lower = mb_strtolower($raw);

        if (str_contains($lower, 'resend.com/domains')
            || (str_contains($lower, '550') && str_contains($lower, 'testing emails'))) {
            return 'Resend solo permite enviar correos de prueba a la dirección de su cuenta hasta que verifique un dominio en resend.com/domains y use MAIL_FROM_ADDRESS con ese dominio.';
        }

        return 'No se pudo enviar el correo en este momento. Verifique la configuración SMTP o reintente más tarde.';
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    private function recordPrecheckFailure(
        Student $student,
        string $channel,
        ?Staff $actor,
        string $reason,
        array $metadata,
        string $userMessage,
    ): StudentMailSendOutcome {
        Log::channel('student_mail')->warning('registration_mail_precheck_failed', [
            'student_id' => $student->id,
            'channel' => $channel,
            'reason' => $reason,
            'metadata' => $metadata,
        ]);

        $this->persistLog($student, $channel, StudentMailLog::STATUS_FAILED, $reason, $actor, $metadata);

        return StudentMailSendOutcome::failure($userMessage);
    }

    /**
     * @param  array<string, string>  $paths
     */
    private function attachmentsLookValid(array $paths): bool
    {
        foreach (['enrollment_form', 'regulations'] as $key) {
            $path = $paths[$key] ?? null;
            if (! is_string($path) || ! is_file($path) || filesize($path) === 0) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    private function persistLog(
        Student $student,
        string $channel,
        string $status,
        ?string $errorMessage,
        ?Staff $actor,
        array $metadata,
    ): void {
        StudentMailLog::query()->create([
            'student_id' => $student->id,
            'channel' => $channel,
            'status' => $status,
            'error_message' => $errorMessage,
            'triggered_by_staff_id' => $actor?->id,
            'metadata' => $metadata,
        ]);
    }
}
