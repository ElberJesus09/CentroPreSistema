<?php

namespace App\Services;

use App\Models\AcademicCycle;
use App\Models\Career;
use App\Models\Shift;
use App\Models\Student;
use BaconQrCode\Renderer\GDLibRenderer;
use BaconQrCode\Writer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class StudentCardService
{
    private const int MAX_CARDS = 300;

    /**
     * @param  array{student?: string|null, academic_cycle_id?: int|null, career_id?: int|null, shift_id?: int|null}  $filters
     * @return Collection<int, Student>
     */
    public function studentsForCards(array $filters): Collection
    {
        $specific = trim((string) ($filters['student'] ?? ''));

        $query = Student::query()
            ->with([
                'career:id,name,code',
                'academicCycle:id,name',
                'schedule:id,academic_cycle_id,shift_id',
                'schedule.academicCycle:id,name',
                'schedule.shift:id,name',
            ]);

        if ($specific !== '') {
            $query->where(function (Builder $q) use ($specific): void {
                if (ctype_digit($specific)) {
                    $q->where('dni', $specific)
                        ->orWhere($q->getModel()->getQualifiedKeyName(), (int) $specific);
                } else {
                    $q->whereRaw('1 = 0');
                }
            });
        } else {
            $query
                ->when($filters['academic_cycle_id'] ?? null, fn (Builder $q, int $id) => $q->where('academic_cycle_id', $id))
                ->when($filters['career_id'] ?? null, fn (Builder $q, int $id) => $q->where('career_id', $id))
                ->when($filters['shift_id'] ?? null, function (Builder $q, int $id): void {
                    $q->whereHas('schedule', fn (Builder $schedule) => $schedule->where('shift_id', $id));
                });
        }

        $students = $query
            ->orderBy('last_name')
            ->orderBy('mother_last_name')
            ->orderBy('first_name')
            ->limit(self::MAX_CARDS + 1)
            ->get();

        if ($students->count() > self::MAX_CARDS) {
            throw ValidationException::withMessages([
                'filters' => ['El reporte supera '.self::MAX_CARDS.' carnets. Use filtros mas especificos.'],
            ]);
        }

        if ($students->isEmpty()) {
            throw ValidationException::withMessages([
                'filters' => ['No se encontraron alumnos para generar carnets.'],
            ]);
        }

        return $students;
    }

    /**
     * @param  Collection<int, Student>  $students
     * @return array<int, array{student: Student, qr_path: string}>
     */
    public function buildCardPayload(Collection $students): array
    {
        $directory = storage_path('app/tmp/student-cards');
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $this->deleteStaleQrFiles($directory);

        $writer = new Writer(new GDLibRenderer(180));
        $cards = [];

        foreach ($students as $student) {
            $path = $directory.DIRECTORY_SEPARATOR.'qr-'.$student->id.'-'.Str::ulid().'.png';
            $writer->writeFile($this->qrText($student), $path);

            $cards[] = [
                'student' => $student,
                'qr_path' => $path,
            ];
        }

        return $cards;
    }

    /**
     * @param  array<int, array{qr_path: string}>  $cards
     */
    public function deleteQrFiles(array $cards): void
    {
        foreach ($cards as $card) {
            if (is_file($card['qr_path'])) {
                @unlink($card['qr_path']);
            }
        }
    }

    private function deleteStaleQrFiles(string $directory): void
    {
        foreach (glob($directory.DIRECTORY_SEPARATOR.'qr-*.png') ?: [] as $path) {
            if (is_file($path) && filemtime($path) !== false && filemtime($path) < now()->subDay()->getTimestamp()) {
                @unlink($path);
            }
        }
    }

    /** @return Collection<int, AcademicCycle> */
    public function cycles(): Collection
    {
        return AcademicCycle::query()
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->get(['id', 'name', 'start_date']);
    }

    /** @return Collection<int, Career> */
    public function careers(): Collection
    {
        return Career::query()
            ->orderBy('name')
            ->get(['id', 'name', 'code']);
    }

    /** @return Collection<int, Shift> */
    public function shifts(): Collection
    {
        return Shift::query()
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    private function qrText(Student $student): string
    {
        return implode("\n", [
            'CPU UNPRG',
            'ALUMNO: '.$this->plainText($student->fullName()),
            'DNI: '.$this->plainText($student->dni),
            'TURNO: '.$this->plainText($student->schedule?->shift?->name ?? '---'),
            'CICLO: '.$this->plainText($student->academicCycle?->name ?? $student->schedule?->academicCycle?->name ?? '---'),
            'CARRERA: '.$this->plainText($student->career?->name ?? '---'),
            'CORREO: '.$this->plainText($student->email),
        ]);
    }

    private function plainText(mixed $value): string
    {
        return mb_strtoupper(Str::ascii((string) ($value ?: '---')), 'UTF-8');
    }
}
