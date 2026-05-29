<?php

use App\Http\Requests\PublicRegistration\RegistrationStep1Request;
use App\Http\Requests\PublicRegistration\RegistrationStep2Request;
use App\Http\Requests\PublicRegistration\RegistrationStep3Request;
use App\Http\Requests\PublicRegistration\RegistrationStep4Request;
use App\Http\Requests\Student\StoreStudentRequest;
use App\Models\AcademicCycle;
use App\Models\AcademicCycleShift;
use App\Models\Campus;
use App\Models\Career;
use App\Models\ExamSetting;
use App\Models\Guardian;
use App\Models\Shift;
use App\Services\PublicRegistrationCompletionService;
use App\Services\StudentService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
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

function publicStep1Payload(string $birthDate): array
{
    return [
        'student' => [
            'first_name' => 'Ana',
            'last_name' => 'Perez',
            'mother_last_name' => 'Lopez',
            'dni' => '76543210',
            'birth_date' => $birthDate,
            'gender' => 'female',
            'phone' => '987654321',
            'address' => 'Av. Principal 123',
            'email' => 'ana@example.test',
        ],
        'address_department' => 'Lambayeque',
        'address_province' => 'Chiclayo',
        'address_district' => 'Chiclayo',
        'address_line' => 'Av. Principal 123',
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

function makeScheduleWithCapacity(string $cycleName, string $startDate, int $capacity): array
{
    [, $schedule] = makeScheduleForCycle($cycleName, $startDate);
    $schedule->update(['capacity' => $capacity]);

    return [$schedule->academicCycle, $schedule->fresh()];
}

function makeAlternativeScheduleForCycle(AcademicCycle $cycle): AcademicCycleShift
{
    $campus = Campus::query()->create([
        'name' => 'Sede Alterna '.$cycle->name,
        'address' => 'Av. Alterna',
        'status' => true,
    ]);

    $shift = Shift::query()->create([
        'name' => 'Turno Alterno '.$cycle->name,
        'status' => true,
    ]);

    return AcademicCycleShift::query()->create([
        'academic_cycle_id' => $cycle->id,
        'campus_id' => $campus->id,
        'shift_id' => $shift->id,
        'capacity' => 30,
        'enrolled' => 0,
        'status' => true,
    ]);
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

test('adult students can register without guardian', function () {
    Carbon::setTestNow('2026-05-26');

    try {
        $career = Career::query()->create([
            'name' => 'Contabilidad Test',
            'code' => 'CT',
            'status' => true,
        ]);
        [, $schedule] = makeScheduleForCycle('2033-I', '2033-01-01');

        $payload = registrationPayload($schedule->id, $career->id, '10101010');
        $payload['student']['birth_date'] = '2008-05-26';
        $payload['guardian'] = [];

        $student = app(StudentService::class)->registerStudent($payload);

        expect($student->guardian_id)->toBeNull()
            ->and($student->guardian)->toBeNull()
            ->and(Guardian::query()->count())->toBe(0);
    } finally {
        Carbon::setTestNow();
    }
});

test('student dni cannot register twice in the same academic cycle with different shifts', function () {
    $career = Career::query()->create([
        'name' => 'Enfermeria Test',
        'code' => 'ET',
        'status' => true,
    ]);
    [$cycle, $morningSchedule] = makeScheduleForCycle('2026-II', '2026-08-01');
    $afternoonSchedule = makeAlternativeScheduleForCycle($cycle);

    $service = app(StudentService::class);
    $service->registerStudent(registrationPayload($morningSchedule->id, $career->id, '99887766'));

    expect(fn () => $service->registerStudent(registrationPayload($afternoonSchedule->id, $career->id, '99887766')))
        ->toThrow(ValidationException::class);
});

test('admin registration returns spanish message for invalid birth date', function () {
    Carbon::setTestNow('2026-05-26');

    try {
        $request = new RegistrationStep1Request;
        $validator = Validator::make(
            publicStep1Payload('2026-01-01'),
            ['student.birth_date' => $request->rules()['student.birth_date']],
            $request->messages(),
        );

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->first('student.birth_date'))->toBe('Fecha de nacimiento incorrecta. El estudiante debe tener al menos 15 años.');
    } finally {
        Carbon::setTestNow();
    }
});

test('public payment step returns spanish message for duplicated voucher', function () {
    $career = Career::query()->create([
        'name' => 'Psicologia Test',
        'code' => 'PT',
        'status' => true,
    ]);
    [, $schedule] = makeScheduleForCycle('2030-I', '2030-01-01');

    $service = app(StudentService::class);
    $service->registerStudent(registrationPayload($schedule->id, $career->id, '12344321'));

    $request = new RegistrationStep4Request;
    $payload = [
        'career_id' => $career->id,
        'academic_cycle_shift_id' => $schedule->id,
        'student' => [
            'payment_voucher_number' => '12344321'.$schedule->id,
            'payment_agency_number' => '0230',
            'payment_date' => '2026-01-15',
        ],
    ];

    $validator = Validator::make($payload, $request->rules(), $request->messages());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('student.payment_voucher_number'))->toBe('Este número de voucher ya fue registrado.');
});

test('default unique validation message is spanish', function () {
    Career::query()->create([
        'name' => 'x',
        'code' => 'XX',
        'status' => true,
    ]);

    $validator = Validator::make(['name' => 'x'], ['name' => 'unique:careers,name']);

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('name'))->toContain('ya está en uso');
});

test('student registration cannot exceed schedule capacity', function () {
    $career = Career::query()->create([
        'name' => 'Medicina Test',
        'code' => 'MT',
        'status' => true,
    ]);
    [, $schedule] = makeScheduleWithCapacity('2028-I', '2028-01-01', 1);

    $service = app(StudentService::class);
    $service->registerStudent(registrationPayload($schedule->id, $career->id, '11112222'));

    expect(fn () => $service->registerStudent(registrationPayload($schedule->id, $career->id, '33334444')))
        ->toThrow(ValidationException::class);
});

test('public document download links use relative signatures', function () {
    ExamSetting::singleton()->update(['registration_mail_enabled' => false]);

    $career = Career::query()->create([
        'name' => 'Veterinaria Test',
        'code' => 'VT',
        'status' => true,
    ]);
    [, $schedule] = makeScheduleForCycle('2031-I', '2031-01-01');

    $result = app(PublicRegistrationCompletionService::class)
        ->finalize(registrationPayload($schedule->id, $career->id, '44556677'));

    $url = $result->documentDownloads['enrollment_form'] ?? '';
    $request = Request::create('https://portal-publico.test'.$url, 'GET');

    expect($url)->toStartWith('/registration/documents/')
        ->and($request->hasValidSignature(false))->toBeTrue()
        ->and($request->hasValidSignature())->toBeFalse();
});

test('registration mail is disabled by default', function () {
    expect(ExamSetting::singleton()->registration_mail_enabled)->toBeFalse();
});

test('public registration rejects birth dates for students younger than fifteen', function () {
    Carbon::setTestNow('2026-05-26');

    try {
        $request = new RegistrationStep1Request;
        $validator = Validator::make(publicStep1Payload('2026-01-01'), $request->rules(), $request->messages());

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->has('student.birth_date'))->toBeTrue();
    } finally {
        Carbon::setTestNow();
    }
});

test('public registration returns spanish messages for numeric fields', function () {
    $step1 = new RegistrationStep1Request;
    $step1Payload = publicStep1Payload('2008-01-15');
    $step1Payload['student']['dni'] = '123';
    $step1Payload['student']['phone'] = '987';

    $step1Validator = Validator::make($step1Payload, $step1->rules(), $step1->messages());

    expect($step1Validator->fails())->toBeTrue()
        ->and($step1Validator->errors()->first('student.dni'))->toBe('El DNI del estudiante debe contener exactamente 8 dígitos.')
        ->and($step1Validator->errors()->first('student.phone'))->toBe('El celular del estudiante debe contener exactamente 9 dígitos.');

    $step2 = new RegistrationStep2Request;
    $step2Validator = Validator::make([
        'guardian' => [
            'first_name' => 'Maria',
            'last_name' => 'Lopez',
            'mother_last_name' => 'Rios',
            'dni' => '123',
            'phone' => '987',
            'relationship' => 'mother',
        ],
    ], $step2->rules(), $step2->messages());

    expect($step2Validator->fails())->toBeTrue()
        ->and($step2Validator->errors()->first('guardian.dni'))->toBe('El DNI del apoderado debe contener exactamente 8 dígitos.')
        ->and($step2Validator->errors()->first('guardian.phone'))->toBe('El celular del apoderado debe contener exactamente 9 dígitos.');

    $step3 = new RegistrationStep3Request;
    $step3Validator = Validator::make([
        'school' => [
            'name' => 'Colegio Test',
            'department' => 'Lambayeque',
            'province' => 'Chiclayo',
            'district' => 'Chiclayo',
            'graduation_year' => '20',
        ],
    ], $step3->rules(), $step3->messages());

    expect($step3Validator->fails())->toBeTrue()
        ->and($step3Validator->errors()->first('school.graduation_year'))->toBe('El año de egreso debe contener exactamente 4 dígitos.');

    $step4 = new RegistrationStep4Request;
    $step4Validator = Validator::make([
        'career_id' => 1,
        'academic_cycle_shift_id' => 1,
        'student' => [
            'payment_voucher_number' => 'ABC123',
            'payment_agency_number' => '12',
            'payment_date' => '2026-01-15',
        ],
    ], $step4->rules(), $step4->messages());

    expect($step4Validator->fails())->toBeTrue()
        ->and($step4Validator->errors()->first('student.payment_voucher_number'))->toBe('El número de voucher solo debe contener dígitos.')
        ->and($step4Validator->errors()->first('student.payment_agency_number'))->toBe('El número de agencia debe contener exactamente 4 dígitos.');
});

test('public guardian step does not require guardian for adult students', function () {
    Carbon::setTestNow('2026-05-26');

    try {
        $this->withSession([
            'public_registration' => [
                'student' => publicStep1Payload('2008-05-26')['student'],
            ],
        ])->post('/registration/step/2', [
            'guardian' => [],
        ])
            ->assertRedirect('/registration/step/3')
            ->assertSessionHas('public_registration.guardian', []);
    } finally {
        Carbon::setTestNow();
    }
});

test('adult students can skip guardian even with partial guardian input', function () {
    Carbon::setTestNow('2026-05-26');

    try {
        $this->withSession([
            'public_registration' => [
                'student' => publicStep1Payload('2008-05-26')['student'],
            ],
        ])->post('/registration/step/2', [
            'skip_guardian' => '1',
            'guardian' => [
                'first_name' => 'Maria',
            ],
        ])
            ->assertRedirect('/registration/step/3')
            ->assertSessionHas('public_registration.guardian', []);
    } finally {
        Carbon::setTestNow();
    }
});

test('minor students cannot skip guardian', function () {
    Carbon::setTestNow('2026-05-26');

    try {
        $this->withSession([
            'public_registration' => [
                'student' => publicStep1Payload('2009-05-27')['student'],
            ],
        ])->from('/registration/step/2')
            ->post('/registration/step/2', [
                'skip_guardian' => '1',
                'guardian' => [],
            ])
            ->assertRedirect('/registration/step/2')
            ->assertSessionHasErrors(['guardian.first_name']);
    } finally {
        Carbon::setTestNow();
    }
});

test('public guardian step keeps optional guardian for adult students', function () {
    Carbon::setTestNow('2026-05-26');

    try {
        $guardian = [
            'first_name' => 'Maria',
            'last_name' => 'Lopez',
            'mother_last_name' => 'Rios',
            'dni' => '12345678',
            'phone' => '987654322',
            'relationship' => 'mother',
        ];

        $this->withSession([
            'public_registration' => [
                'student' => publicStep1Payload('2008-05-26')['student'],
            ],
        ])->post('/registration/step/2', [
            'guardian' => $guardian,
        ])
            ->assertRedirect('/registration/step/3')
            ->assertSessionHas('public_registration.guardian', $guardian);
    } finally {
        Carbon::setTestNow();
    }
});

test('public registration builds student address from peru location fields', function () {
    $this->post('/registration/step/1', publicStep1Payload('2008-01-15'))
        ->assertRedirect('/registration/step/2')
        ->assertSessionHas('public_registration.student.address', 'Av. Principal 123, Chiclayo, Chiclayo, Lambayeque')
        ->assertSessionHas('public_registration.student_address.address_department', 'Lambayeque')
        ->assertSessionHas('public_registration.student_address.address_province', 'Chiclayo')
        ->assertSessionHas('public_registration.student_address.address_district', 'Chiclayo')
        ->assertSessionHas('public_registration.student_address.address_line', 'Av. Principal 123');
});

test('public registration returns spanish messages for empty student required fields', function () {
    $step1 = new RegistrationStep1Request;
    $payload = publicStep1Payload('');
    $payload['student']['first_name'] = '';
    $payload['student']['last_name'] = '';
    $payload['student']['mother_last_name'] = '';
    $payload['student']['birth_date'] = '';
    $payload['student']['email'] = '';

    $validator = Validator::make($payload, $step1->rules(), $step1->messages());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('student.first_name'))->toBe('Ingrese los nombres del estudiante.')
        ->and($validator->errors()->first('student.last_name'))->toBe('Ingrese el apellido paterno del estudiante.')
        ->and($validator->errors()->first('student.mother_last_name'))->toBe('Ingrese el apellido materno del estudiante.')
        ->and($validator->errors()->first('student.birth_date'))->toBe('Ingrese la fecha de nacimiento del estudiante.')
        ->and($validator->errors()->first('student.email'))->toBe('Ingrese el correo electrónico del estudiante.');
});

test('public registration required messages do not fall back to english', function () {
    $requests = [
        new RegistrationStep1Request,
        new RegistrationStep2Request,
        new RegistrationStep3Request,
        new RegistrationStep4Request,
    ];

    $messages = collect($requests)
        ->flatMap(function ($request) {
            $validator = Validator::make([], $request->rules(), $request->messages());

            expect($validator->fails())->toBeTrue();

            return $validator->errors()->all();
        })
        ->values();

    $messages->each(function (string $message): void {
        expect($message)->not->toContain(' field is required')
            ->and($message)->not->toContain('The ')
            ->and($message)->not->toContain('student.')
            ->and($message)->not->toContain('guardian.')
            ->and($message)->not->toContain('school.');
    });
});

test('student registration stores names and address in uppercase preserving accents', function () {
    $career = Career::query()->create([
        'name' => 'Traduccion Test',
        'code' => 'TT',
        'status' => true,
    ]);
    [, $schedule] = makeScheduleForCycle('2032-I', '2032-01-01');

    $payload = registrationPayload($schedule->id, $career->id, '55667788');
    $payload['student']['first_name'] = 'josé maría';
    $payload['student']['last_name'] = 'muñoz';
    $payload['student']['mother_last_name'] = 'cañete';
    $payload['student']['address'] = 'av. los héroes 123, san josé';
    $payload['guardian']['first_name'] = 'maría josé';
    $payload['guardian']['last_name'] = 'peña';
    $payload['guardian']['mother_last_name'] = 'ñique';
    $payload['school']['name'] = 'colegio señor de sipán';
    $payload['school']['district'] = 'josé leonardo ortiz';

    $student = app(StudentService::class)->registerStudent($payload);

    expect($student->first_name)->toBe('JOSÉ MARÍA')
        ->and($student->last_name)->toBe('MUÑOZ')
        ->and($student->mother_last_name)->toBe('CAÑETE')
        ->and($student->address)->toBe('AV. LOS HÉROES 123, SAN JOSÉ')
        ->and($student->guardian->first_name)->toBe('MARÍA JOSÉ')
        ->and($student->guardian->last_name)->toBe('PEÑA')
        ->and($student->guardian->mother_last_name)->toBe('ÑIQUE')
        ->and($student->school->name)->toBe('COLEGIO SEÑOR DE SIPÁN')
        ->and($student->school->district)->toBe('JOSÉ LEONARDO ORTIZ');
});

test('public results returns spanish message for incomplete or long dni', function () {
    ExamSetting::singleton()->update(['public_results_enabled' => true]);

    $this->from('/results')
        ->get('/results?dni=123')
        ->assertRedirect('/results')
        ->assertSessionHasErrors(['dni' => 'El DNI debe contener exactamente 8 dígitos.']);

    $this->from('/results')
        ->get('/results?dni=123456789')
        ->assertRedirect('/results')
        ->assertSessionHasErrors(['dni' => 'El DNI debe contener exactamente 8 dígitos.']);
});

test('public registration returns spanish messages for empty numeric fields', function () {
    $step1 = new RegistrationStep1Request;
    $step1Payload = publicStep1Payload('2008-01-15');
    $step1Payload['student']['dni'] = '';
    $step1Payload['student']['phone'] = '';

    $step1Validator = Validator::make($step1Payload, $step1->rules(), $step1->messages());

    expect($step1Validator->fails())->toBeTrue()
        ->and($step1Validator->errors()->first('student.dni'))->toBe('Ingrese el DNI del estudiante.')
        ->and($step1Validator->errors()->first('student.phone'))->toBe('Ingrese el celular del estudiante.');

    $step2 = new RegistrationStep2Request;
    $step2Validator = Validator::make([
        'guardian' => [
            'first_name' => 'Maria',
            'last_name' => 'Lopez',
            'mother_last_name' => 'Rios',
            'dni' => '',
            'phone' => '',
            'relationship' => 'mother',
        ],
    ], $step2->rules(), $step2->messages());

    expect($step2Validator->fails())->toBeTrue()
        ->and($step2Validator->errors()->first('guardian.dni'))->toBe('Ingrese el DNI del apoderado.')
        ->and($step2Validator->errors()->first('guardian.phone'))->toBe('Ingrese el celular del apoderado.');

    $step3 = new RegistrationStep3Request;
    $step3Validator = Validator::make([
        'school' => [
            'name' => 'Colegio Test',
            'department' => 'Lambayeque',
            'province' => 'Chiclayo',
            'district' => 'Chiclayo',
            'graduation_year' => '',
        ],
    ], $step3->rules(), $step3->messages());

    expect($step3Validator->fails())->toBeTrue()
        ->and($step3Validator->errors()->first('school.graduation_year'))->toBe('Ingrese el año de egreso.');

    $step4 = new RegistrationStep4Request;
    $step4Validator = Validator::make([
        'career_id' => 1,
        'academic_cycle_shift_id' => 1,
        'student' => [
            'payment_voucher_number' => '',
            'payment_agency_number' => '',
            'payment_date' => '2026-01-15',
        ],
    ], $step4->rules(), $step4->messages());

    expect($step4Validator->fails())->toBeTrue()
        ->and($step4Validator->errors()->first('student.payment_voucher_number'))->toBe('Ingrese el número de voucher.')
        ->and($step4Validator->errors()->first('student.payment_agency_number'))->toBe('Ingrese el número de agencia.');
});

test('public results returns spanish message for empty dni', function () {
    ExamSetting::singleton()->update(['public_results_enabled' => true]);

    $this->from('/results')
        ->get('/results?dni=')
        ->assertRedirect('/results')
        ->assertSessionHasErrors(['dni' => 'Ingrese su DNI para consultar sus resultados.']);
});

test('admin registration rejects birth dates for students younger than fifteen', function () {
    Carbon::setTestNow('2026-05-26');

    try {
        $career = Career::query()->create([
            'name' => 'Derecho Test',
            'code' => 'DT',
            'status' => true,
        ]);
        [, $schedule] = makeScheduleForCycle('2029-I', '2029-01-01');

        $payload = registrationPayload($schedule->id, $career->id, '22223333');
        $payload['student']['birth_date'] = '2026-01-01';

        $request = new StoreStudentRequest;
        $request->merge($payload);
        $validator = Validator::make($payload, $request->rules(), $request->messages());

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->has('student.birth_date'))->toBeTrue();
    } finally {
        Carbon::setTestNow();
    }
});
