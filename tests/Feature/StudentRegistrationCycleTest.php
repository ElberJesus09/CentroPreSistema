<?php

use App\Models\AcademicCycle;
use App\Models\AcademicCycleShift;
use App\Models\Campus;
use App\Models\Career;
use App\Models\Shift;
use App\Services\StudentService;
use Illuminate\Validation\ValidationException;

function registrationPayload(int $scheduleId, int $careerId, string $dni): array
{
    return [
        'student' => [
            'first_name' => 'Ana',
            'last_name' => 'Perez',
            'mother_last_name' => 'Lopez',
            'dni' => $dni,
            'birth_date' => '2008-01-15',
            'gender' => 'female',
            'phone' => '987654321',
            'address' => 'Av. Principal 123',
            'email' => "ana{$scheduleId}@example.test",
            'payment_voucher_number' => $dni.$scheduleId,
            'payment_agency_number' => '0230',
            'payment_date' => '2026-01-15',
        ],
        'guardian' => [
            'first_name' => 'Maria',
            'last_name' => 'Lopez',
            'mother_last_name' => 'Rios',
            'dni' => '12345678',
            'phone' => '987654322',
            'relationship' => 'mother',
        ],
        'school' => [
            'name' => 'Colegio Test',
            'department' => 'Lambayeque',
            'province' => 'Chiclayo',
            'district' => 'Chiclayo',
            'graduation_year' => 2025,
        ],
        'career_id' => $careerId,
        'academic_cycle_shift_id' => $scheduleId,
        'status' => 'pending',
    ];
}

function makeScheduleForCycle(string $cycleName, string $startDate): array
{
    $cycle = AcademicCycle::query()->create([
        'name' => $cycleName,
        'status' => true,
        'start_date' => $startDate,
        'end_date' => substr($startDate, 0, 4).'-12-31',
    ]);

    $campus = Campus::query()->create([
        'name' => 'Sede '.$cycleName,
        'address' => 'Av. Test',
        'status' => true,
    ]);

    $shift = Shift::query()->create([
        'name' => 'Turno '.$cycleName,
        'status' => true,
    ]);

    $schedule = AcademicCycleShift::query()->create([
        'academic_cycle_id' => $cycle->id,
        'campus_id' => $campus->id,
        'shift_id' => $shift->id,
        'capacity' => 30,
        'enrolled' => 0,
        'status' => true,
    ]);

    return [$cycle, $schedule];
}

test('student dni is unique within the same academic cycle', function () {
    $career = Career::query()->create([
        'name' => 'Ingenieria Test',
        'code' => 'IT',
        'status' => true,
    ]);
    [, $schedule] = makeScheduleForCycle('2026-I', '2026-01-01');

    $service = app(StudentService::class);
    $service->registerStudent(registrationPayload($schedule->id, $career->id, '76543210'));

    expect(fn () => $service->registerStudent(registrationPayload($schedule->id, $career->id, '76543210')))
        ->toThrow(ValidationException::class);
});

test('student dni can register in different academic cycles', function () {
    $career = Career::query()->create([
        'name' => 'Arquitectura Test',
        'code' => 'AT',
        'status' => true,
    ]);
    [, $schedule2026] = makeScheduleForCycle('2026-I', '2026-01-01');
    [, $schedule2027] = makeScheduleForCycle('2027-I', '2027-01-01');

    $service = app(StudentService::class);
    $first = $service->registerStudent(registrationPayload($schedule2026->id, $career->id, '87654321'));
    $second = $service->registerStudent(registrationPayload($schedule2027->id, $career->id, '87654321'));

    expect($first->dni)->toBe($second->dni)
        ->and($first->academic_cycle_id)->not->toBe($second->academic_cycle_id);
});
