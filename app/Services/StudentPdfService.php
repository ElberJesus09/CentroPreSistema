<?php

namespace App\Services;

use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use ZipArchive;

class StudentPdfService
{
    /**
     * Genera PDFs temporales para adjuntar al correo de confirmacion.
     *
     * @return array{enrollment_form: string, regulations: string} rutas absolutas
     */
    public function buildRegistrationAttachmentFiles(Student $student): array
    {
        $this->loadRegistrationRelations($student);

        $directory = storage_path('app/tmp/registration-mail');
        if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
            throw new InvalidArgumentException('No se pudo crear la carpeta temporal para PDF.');
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

    public function downloadRegistrationDocument(Student $student, string $document): Response
    {
        $this->loadRegistrationRelations($student);

        [$view, $filename] = match ($document) {
            'enrollment_form' => ['pdf.enrollment-form', (string) config('student_mail.attachment_enrollment_filename')],
            'regulations' => ['pdf.institutional-regulations', (string) config('student_mail.attachment_regulations_filename')],
            default => abort(404),
        };

        return Pdf::loadView($view, ['student' => $student])
            ->setPaper('a4')
            ->download($filename);
    }

    /**
     * Crea un ZIP con ficha y reglamento; elimina los PDF temporales tras empaquetar.
     *
     * @return array{path: string, download_name: string}
     */
    public function createRegistrationDocumentsZip(Student $student): array
    {
        $paths = $this->buildRegistrationAttachmentFiles($student);

        $directory = storage_path('app/tmp/registration-mail');
        if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
            $this->deleteIfExists($paths);
            throw new InvalidArgumentException('No se pudo crear la carpeta temporal para ZIP.');
        }

        $zipPath = $directory.DIRECTORY_SEPARATOR.'pack-'.Str::ulid().'.zip';
        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            $this->deleteIfExists($paths);
            throw new RuntimeException('No se pudo crear el archivo ZIP de documentos.');
        }

        $zip->addFile(
            $paths['enrollment_form'],
            (string) config('student_mail.attachment_enrollment_filename'),
        );
        $zip->addFile(
            $paths['regulations'],
            (string) config('student_mail.attachment_regulations_filename'),
        );
        $zip->close();

        $this->deleteIfExists($paths);

        $safeDni = preg_replace('/[^0-9A-Za-z_-]+/', '', (string) $student->dni) ?: 'sin-dni';
        $downloadName = "inscripcion-{$safeDni}-{$student->id}.zip";

        return [
            'path' => $zipPath,
            'download_name' => $downloadName,
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

    private function loadRegistrationRelations(Student $student): void
    {
        $student->loadMissing([
            'guardian',
            'school',
            'career',
            'schedule.academicCycle',
            'schedule.campus',
            'schedule.shift',
        ]);
    }
}
