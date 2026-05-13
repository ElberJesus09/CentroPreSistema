<?php

namespace App\Services;

use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use InvalidArgumentException;

class StudentPdfService
{
    /**
     * Genera PDFs temporales para adjuntar al correo de confirmacion.
     *
     * @return array{enrollment_form: string, regulations: string} rutas absolutas
     */
    public function buildRegistrationAttachmentFiles(Student $student): array
    {
        $student->loadMissing([
            'guardian',
            'school',
            'career',
            'schedule.academicCycle',
            'schedule.campus',
            'schedule.shift',
        ]);

        $directory = storage_path('app/tmp/registration-mail');
        if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
            throw new InvalidArgumentException('Cannot create temporary PDF directory.');
        }

        $token = (string) Str::ulid();
        $enrollmentPath = $directory.DIRECTORY_SEPARATOR."enrollment-{$token}.pdf";
        $regulationsPath = $directory.DIRECTORY_SEPARATOR."regulations-{$token}.pdf";

        Pdf::loadView('pdf.enrollment-form', ['student' => $student])
            ->setPaper('a4')
            ->save($enrollmentPath);

        Pdf::loadView('pdf.institutional-regulations', ['student' => $student])
            ->setPaper('a4')
            ->save($regulationsPath);

        return [
            'enrollment_form' => $enrollmentPath,
            'regulations' => $regulationsPath,
        ];
    }

    /**
     * @param  array<string, string>  $paths
     */
    public function deleteIfExists(array $paths): void
    {
        foreach ($paths as $path) {
            if (is_string($path) && $path !== '' && is_file($path)) {
                @unlink($path);
            }
        }
    }
}
