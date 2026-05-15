<?php

namespace Database\Seeders;

use App\Models\AdmissionProcess;
use App\Models\Career;
use App\Models\Student;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Alumnos de demostración con apoderados (1000 por proceso 2025-I y 2025-II).
 * Idempotente: borra solo filas del dominio de correo demo antes de insertar.
 */
class StudentBulkDemoSeeder extends Seeder
{
    private const EMAIL_SUFFIX = '@alumno-demo.cpu';

    public function run(): void
    {
        if (! filter_var(env('SEED_DEMO_STUDENTS', true), FILTER_VALIDATE_BOOL)) {
            return;
        }

        $schedule = DB::table('academic_cycle_shifts')->orderBy('id')->first(['id', 'academic_cycle_id']);
        if ($schedule === null) {
            $this->command?->warn('StudentBulkDemoSeeder: sin programación académica; ejecute AcademicDemoDataSeeder antes.');

            return;
        }
        $scheduleId = (int) $schedule->id;
        $academicCycleId = (int) $schedule->academic_cycle_id;

        $processes2025 = AdmissionProcess::query()
            ->whereYear('start_date', 2025)
            ->orderBy('start_date')
            ->pluck('id')
            ->values()
            ->all();

        $p2025I = $processes2025[0] ?? null;
        $p2025II = $processes2025[1] ?? null;

        if ($p2025I === null || $p2025II === null) {
            $this->command?->warn('StudentBulkDemoSeeder: se requieren dos procesos con inicio en 2025.');

            return;
        }

        $this->purgeDemoStudents();

        $careerIds = Career::query()->where('status', true)->orderBy('id')->pluck('id')->all();
        if ($careerIds === []) {
            return;
        }

        $schoolIds = $this->ensureDemoSchools(30);
        $now = now();

        $guardianRows = [];
        for ($i = 1; $i <= 2000; $i++) {
            $guardianRows[] = [
                'first_name' => 'Apoderado',
                'last_name' => 'Semilla',
                'mother_last_name' => 'Demo',
                'dni' => sprintf('8%07d', $i),
                'phone' => sprintf('%09d', min(950_000_000 + $i, 999_999_999)),
                'relationship' => $i % 2 === 0 ? 'mother' : 'father',
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        foreach (array_chunk($guardianRows, 500) as $chunk) {
            DB::table('guardians')->insert($chunk);
        }

        $guardianIds = DB::table('guardians')
            ->whereBetween('dni', ['80000001', '80002000'])
            ->orderBy('id')
            ->pluck('id')
            ->all();

        if (count($guardianIds) !== 2000) {
            $this->command?->warn('StudentBulkDemoSeeder: conteo de apoderados inesperado, abortando.');

            return;
        }

        $firstNames = ['Juan', 'María', 'Carlos', 'Ana', 'Luis', 'Rosa', 'Pedro', 'Lucía', 'Jorge', 'Carmen', 'Miguel', 'Patricia', 'Andrés', 'Daniela', 'Fernando', 'Gabriela', 'Ricardo', 'Valeria', 'Diego', 'Sofía'];
        $lastNames = ['Pérez', 'García', 'Rodríguez', 'López', 'Martínez', 'Hernández', 'González', 'Sánchez', 'Ramírez', 'Torres', 'Flores', 'Rivera', 'Castillo', 'Vargas', 'Mendoza', 'Silva', 'Reyes', 'Cruz', 'Morales', 'Ortiz'];

        $studentRows = [];
        for ($i = 0; $i < 2000; $i++) {
            $n = $i + 1;
            $processId = $i < 1000 ? $p2025I : $p2025II;
            $proc = $i < 1000
                ? ['2025-01-10', '2025-03-20']
                : ['2025-04-01', '2025-06-30'];
            $regDate = $this->randomDateBetween($proc[0], $proc[1]);

            $dni = sprintf('%08d', 20_000_000 + $n);
            $careerId = $careerIds[$i % count($careerIds)];
            $schoolId = $schoolIds[$i % count($schoolIds)];
            $status = $this->weightedStatus($i);

            $studentRows[] = [
                'first_name' => $firstNames[$i % count($firstNames)],
                'last_name' => $lastNames[$i % count($lastNames)],
                'mother_last_name' => $lastNames[($i + 3) % count($lastNames)],
                'dni' => $dni,
                'birth_date' => $this->randomDateBetween('2006-01-01', '2009-12-31'),
                'gender' => $i % 3 === 0 ? 'female' : 'male',
                'phone' => sprintf('%09d', min(960_000_000 + $n, 999_999_999)),
                'address' => 'Av. Demo '.$n.' — Chiclayo',
                'email' => 'alumno.'.$n.self::EMAIL_SUFFIX,
                'registration_date' => $regDate,
                'guardian_id' => $guardianIds[$i],
                'school_id' => $schoolId,
                'career_id' => $careerId,
                'academic_cycle_id' => $academicCycleId,
                'academic_cycle_shift_id' => $scheduleId,
                'status' => $status,
                'admission_process_id' => $processId,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($studentRows, 250) as $chunk) {
            DB::table('students')->insert($chunk);
        }

        $count = DB::table('students')->where('academic_cycle_shift_id', $scheduleId)->count();
        DB::table('academic_cycle_shifts')->where('id', $scheduleId)->update(['enrolled' => $count]);
    }

    private function purgeDemoStudents(): void
    {
        $emails = DB::table('students')->where('email', 'like', '%'.self::EMAIL_SUFFIX)->pluck('id');
        if ($emails->isEmpty()) {
            return;
        }
        DB::table('students')->whereIn('id', $emails)->delete();
        DB::table('guardians')->whereBetween('dni', ['80000001', '80002000'])->delete();
        DB::table('schools')->where('name', 'like', 'Colegio semilla %')->delete();
    }

    /**
     * @return list<int>
     */
    private function ensureDemoSchools(int $count): array
    {
        $now = now();
        $rows = [];
        for ($i = 1; $i <= $count; $i++) {
            $rows[] = [
                'name' => 'Colegio semilla '.str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                'department' => 'Lambayeque',
                'province' => 'Chiclayo',
                'district' => 'Chiclayo',
                'graduation_year' => 2025,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        foreach (array_chunk($rows, 50) as $chunk) {
            DB::table('schools')->insert($chunk);
        }

        return DB::table('schools')
            ->where('name', 'like', 'Colegio semilla %')
            ->orderBy('name')
            ->pluck('id')
            ->all();
    }

    private function randomDateBetween(string $start, string $end): string
    {
        $s = strtotime($start);
        $e = strtotime($end);
        $t = random_int($s, $e);

        return date('Y-m-d', $t);
    }

    private function weightedStatus(int $index): string
    {
        $r = $index % 10;

        return match (true) {
            $r < 7 => Student::STATUS_ACTIVE,
            $r < 9 => Student::STATUS_PENDING,
            default => Student::STATUS_REJECTED,
        };
    }
}
