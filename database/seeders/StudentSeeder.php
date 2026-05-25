<?php

namespace Database\Seeders;

use App\Models\AcademicCycle;
use App\Models\AcademicCycleShift;
use App\Models\Campus;
use App\Models\Career;
use App\Models\Guardian;
use App\Models\School;
use App\Models\Shift;
use App\Models\Student;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StudentSeeder extends Seeder
{
    private const int MORNING_STUDENTS = 50;

    private const int AFTERNOON_STUDENTS = 60;

    public function run(): void
    {
        DB::transaction(function (): void {
            $cycle = AcademicCycle::query()->updateOrCreate(
                ['name' => '2026-I'],
                [
                    'status' => true,
                    'start_date' => '2026-04-01',
                    'end_date' => '2026-08-31',
                ],
            );

            $campus = Campus::query()->firstOrCreate(
                ['name' => 'Centro Preuniversitario Juan Francisco Aguinaga Castro'],
                [
                    'address' => 'Av. Jose Leonardo Ortiz 405, Chiclayo, Peru',
                    'status' => true,
                ],
            );

            $schedules = [
                'morning' => $this->scheduleFor($cycle, $campus, $this->shiftFor('Ma%', 'Manana'), 80),
                'afternoon' => $this->scheduleFor($cycle, $campus, $this->shiftFor('Tar%', 'Tarde'), 90),
            ];

            foreach ($this->students() as $row) {
                $career = Career::query()->where('code', $row['career_code'])->firstOrFail();
                $schedule = $schedules[$row['shift_key']];

                $guardian = Guardian::query()->updateOrCreate(
                    ['dni' => $row['guardian']['dni']],
                    $row['guardian'],
                );

                $school = School::query()->updateOrCreate(
                    [
                        'name' => $row['school']['name'],
                        'department' => $row['school']['department'],
                        'province' => $row['school']['province'],
                        'district' => $row['school']['district'],
                        'graduation_year' => $row['school']['graduation_year'],
                    ],
                    $row['school'],
                );

                Student::query()->updateOrCreate(
                    [
                        'dni' => $row['student']['dni'],
                        'academic_cycle_id' => $cycle->id,
                    ],
                    [
                        ...$row['student'],
                        'guardian_id' => $guardian->id,
                        'school_id' => $school->id,
                        'career_id' => $career->id,
                        'academic_cycle_id' => $cycle->id,
                        'academic_cycle_shift_id' => $schedule->id,
                        'registration_date' => $row['registration_date'],
                        'status' => Student::STATUS_ACTIVE,
                    ],
                );
            }

            foreach ($schedules as $schedule) {
                $schedule->update([
                    'enrolled' => Student::query()
                        ->where('academic_cycle_shift_id', $schedule->id)
                        ->count(),
                ]);
            }
        });
    }

    private function shiftFor(string $prefix, string $fallbackName): Shift
    {
        return Shift::query()
            ->where('name', 'like', $prefix)
            ->orderBy('id')
            ->first()
            ?? Shift::query()->create([
                'name' => $fallbackName,
                'status' => true,
            ]);
    }

    private function scheduleFor(AcademicCycle $cycle, Campus $campus, Shift $shift, int $capacity): AcademicCycleShift
    {
        return AcademicCycleShift::query()->updateOrCreate(
            [
                'academic_cycle_id' => $cycle->id,
                'campus_id' => $campus->id,
                'shift_id' => $shift->id,
            ],
            [
                'capacity' => $capacity,
                'status' => true,
            ],
        );
    }

    /**
     * @return list<array{
     *     shift_key: string,
     *     career_code: string,
     *     registration_date: string,
     *     student: array<string, mixed>,
     *     guardian: array<string, mixed>,
     *     school: array<string, mixed>
     * }>
     */
    private function students(): array
    {
        $students = [
            $this->studentRow(
                1,
                'morning',
                [
                    'first_name' => 'Luis',
                    'last_name' => 'Sanchez',
                    'mother_last_name' => 'Quispe',
                    'dni' => '79000003',
                    'birth_date' => '2001-05-06',
                    'gender' => 'male',
                    'phone' => '977326893',
                    'address' => 'Tacna 401, Chiclayo',
                    'email' => 'c46744615@gmail.com',
                    'payment_voucher_number' => '0521352',
                    'payment_agency_number' => '0301',
                    'payment_date' => '2026-05-13',
                    'career_code' => 'ADM',
                    'guardian_first_name' => 'Alberto',
                    'guardian_last_name' => 'Sanchez',
                    'guardian_mother_last_name' => 'Sanchez',
                    'guardian_dni' => '79000021',
                    'guardian_phone' => '987214320',
                    'relationship' => 'father',
                    'school_name' => 'Basadre',
                    'department' => 'Lambayeque',
                    'province' => 'Chiclayo',
                    'district' => 'Chiclayo',
                    'graduation_year' => 2019,
                ],
            ),
        ];

        for ($i = 2; $i <= self::MORNING_STUDENTS; $i++) {
            $students[] = $this->studentRow($i, 'morning');
        }

        for ($i = self::MORNING_STUDENTS + 1; $i <= self::MORNING_STUDENTS + self::AFTERNOON_STUDENTS; $i++) {
            $students[] = $this->studentRow($i, 'afternoon');
        }

        return $students;
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array{
     *     shift_key: string,
     *     career_code: string,
     *     registration_date: string,
     *     student: array<string, mixed>,
     *     guardian: array<string, mixed>,
     *     school: array<string, mixed>
     * }
     */
    private function studentRow(int $index, string $shiftKey, array $overrides = []): array
    {
        $firstNames = ['Maria', 'Carlos', 'Ana', 'Diego', 'Lucia', 'Jose', 'Valeria', 'Miguel', 'Camila', 'Fernando', 'Sofia', 'Renato', 'Gabriela', 'Andre', 'Paola', 'Kevin', 'Daniela', 'Brayan', 'Karla', 'Rosa'];
        $lastNames = ['Fernandez', 'Torres', 'Castillo', 'Ramirez', 'Paredes', 'Salazar', 'Nunez', 'Ortega', 'Reyes', 'Delgado', 'Morales', 'Vasquez', 'Ibarra', 'Huaman', 'Carrasco', 'Flores', 'Mori', 'Acosta', 'Zapata', 'Soto'];
        $motherLastNames = ['Rojas', 'Mendoza', 'Vargas', 'Lopez', 'Campos', 'Cruz', 'Herrera', 'Silva', 'Aguilar', 'Poma', 'Bravo', 'Mejia', 'Salinas', 'Calle', 'Benites', 'Chavez', 'Espinoza', 'Marin', 'Gomez', 'Leon'];
        $careerCodes = ['ADM', 'DER', 'ISI', 'MED', 'ICV', 'ENF', 'PSI', 'ARC', 'CON', 'ECO', 'IAG', 'MVE', 'ICI', 'CDC', 'AGR', 'CNI', 'IEL', 'EST', 'IQU', 'SOC'];
        $schools = ['Basadre', 'San Jose', 'Manuel Pardo', 'Pedro Ruiz Gallo', 'Karl Weiss', 'Santa Angela', 'Aplicacion UNPRG', 'Nicolas La Torre'];
        $districts = ['Chiclayo', 'Jose Leonardo Ortiz', 'La Victoria', 'Pimentel', 'Monsefu', 'Reque', 'Pomalca', 'Lambayeque'];

        $gender = $index % 2 === 0 ? 'female' : 'male';
        $paymentDay = (($index - 1) % 18) + 1;
        $birthYear = 2000 + ($index % 4);
        $careerCode = $careerCodes[($index - 1) % count($careerCodes)];
        $firstName = $firstNames[($index - 1) % count($firstNames)];
        $lastName = $lastNames[($index - 1) % count($lastNames)];
        $motherLastName = $motherLastNames[($index - 1) % count($motherLastNames)];

        $data = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'mother_last_name' => $motherLastName,
            'dni' => $this->demoDniFor($index),
            'birth_date' => sprintf('%d-%02d-%02d', $birthYear, (($index - 1) % 12) + 1, (($index - 1) % 27) + 1),
            'gender' => $gender,
            'phone' => sprintf('9774%05d', $index),
            'address' => sprintf('Calle %d, %s', 100 + $index, $districts[($index - 1) % count($districts)]),
            'email' => sprintf('alumno%03d@example.com', $index),
            'payment_voucher_number' => sprintf('053%04d', $index),
            'payment_agency_number' => sprintf('%04d', 300 + ($index % 20)),
            'payment_date' => sprintf('2026-05-%02d', $paymentDay),
            'career_code' => $careerCode,
            'guardian_first_name' => $gender === 'male' ? 'Alberto' : 'Rosa',
            'guardian_last_name' => $lastName,
            'guardian_mother_last_name' => $motherLastName,
            'guardian_dni' => sprintf('7920%04d', $index),
            'guardian_phone' => sprintf('9872%05d', $index),
            'relationship' => $gender === 'male' ? 'father' : 'mother',
            'school_name' => $schools[($index - 1) % count($schools)],
            'department' => 'Lambayeque',
            'province' => $districts[($index - 1) % count($districts)] === 'Lambayeque' ? 'Lambayeque' : 'Chiclayo',
            'district' => $districts[($index - 1) % count($districts)],
            'graduation_year' => 2018 + ($index % 4),
        ];

        $data = [...$data, ...$overrides];

        return [
            'shift_key' => $shiftKey,
            'career_code' => $data['career_code'],
            'registration_date' => $data['payment_date'],
            'student' => [
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'mother_last_name' => $data['mother_last_name'],
                'dni' => $data['dni'],
                'birth_date' => $data['birth_date'],
                'gender' => $data['gender'],
                'phone' => $data['phone'],
                'address' => $data['address'],
                'email' => $data['email'],
                'payment_voucher_number' => $data['payment_voucher_number'],
                'payment_agency_number' => $data['payment_agency_number'],
                'payment_date' => $data['payment_date'],
            ],
            'guardian' => [
                'first_name' => $data['guardian_first_name'],
                'last_name' => $data['guardian_last_name'],
                'mother_last_name' => $data['guardian_mother_last_name'],
                'dni' => $data['guardian_dni'],
                'phone' => $data['guardian_phone'],
                'relationship' => $data['relationship'],
            ],
            'school' => [
                'name' => $data['school_name'],
                'department' => $data['department'],
                'province' => $data['province'],
                'district' => $data['district'],
                'graduation_year' => $data['graduation_year'],
            ],
        ];
    }

    private function demoDniFor(int $index): string
    {
        return match ($index) {
            1 => '79000003',
            2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18 => sprintf('790000%02d', $index + 2),
            19 => '79000039',
            20 => '79000041',
            default => sprintf('7910%04d', $index),
        };
    }
}
