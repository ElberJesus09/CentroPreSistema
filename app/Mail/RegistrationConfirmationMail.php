<?php

namespace App\Mail;

use App\Models\ExamSetting;
use App\Models\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RegistrationConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array{enrollment_form: string, regulations: string}  $pdfAbsolutePaths
     */
    public function __construct(
        public Student $student,
        public ExamSetting $examSetting,
        public array $pdfAbsolutePaths,
        public string $enrollmentAttachmentName,
        public string $regulationsAttachmentName,
    ) {
        //
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Confirmación de inscripción — '.config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.registration.confirmation',
            with: [
                'student' => $this->student,
                'exam' => $this->examSetting,
            ],
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromPath($this->pdfAbsolutePaths['enrollment_form'])
                ->as($this->enrollmentAttachmentName)
                ->withMime('application/pdf'),
            Attachment::fromPath($this->pdfAbsolutePaths['regulations'])
                ->as($this->regulationsAttachmentName)
                ->withMime('application/pdf'),
        ];
    }
}
