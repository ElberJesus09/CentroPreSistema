<?php

namespace App\Http\Requests\Concerns;

use App\Models\AcademicCycleShift;
use App\Models\Student;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

trait ValidatesStudentRegistrationPayload
{
    private const int MINIMUM_STUDENT_AGE = 15;

    private const int GUARDIAN_REQUIRED_UNTIL_AGE = 18;

    /**
     * @return array<string, mixed>
     */
    protected function studentGuardianSchoolRules(?int $ignoreStudentId = null): array
    {
        return [
            'student.first_name' => ['required', 'string', 'max:120'],
            'student.last_name' => ['required', 'string', 'max:120'],
            'student.mother_last_name' => ['required', 'string', 'max:120'],
            'student.dni' => [
                'required',
                'digits:8',
                $this->uniqueStudentDniForSelectedCycle($ignoreStudentId),
            ],
            'student.birth_date' => $this->studentBirthDateRules(),
            'student.gender' => ['required', 'string', Rule::in(['male', 'female'])],
            'student.phone' => ['required', 'digits:9'],
            'student.address' => ['required', 'string', 'max:500'],
            'student.email' => ['required', 'email:rfc', 'max:255'],
            'student.payment_voucher_number' => [
                'required',
                'string',
                'max:40',
                'regex:/^\d+$/',
                $this->uniquePaymentVoucherNumber($ignoreStudentId),
            ],
            'student.payment_agency_number' => ['required', 'digits:4'],
            'student.payment_date' => ['required', 'date', 'before_or_equal:today'],

            ...$this->guardianRules(),

            'school.name' => ['required', 'string', 'max:255'],
            'school.department' => ['required', 'string', 'max:120'],
            'school.province' => ['required', 'string', 'max:120'],
            'school.district' => ['required', 'string', 'max:120'],
            'school.graduation_year' => ['required', 'integer', 'digits:4', 'min:1990', 'max:2100'],
        ];
    }

    /**
     * @return array<int, string>
     */
    protected function studentBirthDateRules(): array
    {
        return [
            'required',
            'date',
            'before_or_equal:'.now()->subYears(self::MINIMUM_STUDENT_AGE)->toDateString(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function guardianRules(): array
    {
        $required = $this->guardianIsRequired() || $this->guardianPayloadHasAnyValue();
        $presence = $required ? 'required' : 'nullable';

        return [
            'guardian.first_name' => [$presence, 'string', 'max:120'],
            'guardian.last_name' => [$presence, 'string', 'max:120'],
            'guardian.mother_last_name' => [$presence, 'string', 'max:120'],
            'guardian.dni' => [$presence, 'digits:8'],
            'guardian.phone' => [$presence, 'digits:9'],
            'guardian.relationship' => [$presence, 'string', Rule::in(['father', 'mother', 'uncle', 'aunt', 'guardian'])],
        ];
    }

    protected function guardianIsRequired(): bool
    {
        $birthDate = $this->input('student.birth_date');

        if (! is_string($birthDate) || trim($birthDate) === '') {
            return true;
        }

        try {
            return Carbon::parse($birthDate)->age < self::GUARDIAN_REQUIRED_UNTIL_AGE;
        } catch (\Throwable) {
            return true;
        }
    }

    protected function guardianPayloadHasAnyValue(): bool
    {
        $guardian = $this->input('guardian');

        if (! is_array($guardian)) {
            return false;
        }

        foreach ($guardian as $value) {
            if (is_string($value) && trim($value) !== '') {
                return true;
            }

            if (! is_string($value) && $value !== null && $value !== '') {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'student.first_name.required' => 'Ingrese los nombres del estudiante.',
            'student.last_name.required' => 'Ingrese el apellido paterno del estudiante.',
            'student.mother_last_name.required' => 'Ingrese el apellido materno del estudiante.',
            'student.birth_date.required' => 'Ingrese la fecha de nacimiento del estudiante.',
            'student.birth_date.before_or_equal' => 'Fecha de nacimiento incorrecta. El estudiante debe tener al menos 15 años.',
            'student.gender.required' => 'Seleccione el género del estudiante.',
            'student.gender.in' => 'Seleccione un género válido.',
            'student.dni.required' => 'Ingrese el DNI del estudiante.',
            'student.dni.digits' => 'El DNI del estudiante debe contener exactamente 8 dígitos.',
            'student.phone.required' => 'Ingrese el celular del estudiante.',
            'student.phone.digits' => 'El celular del estudiante debe contener exactamente 9 dígitos.',
            'student.email.required' => 'Ingrese el correo electrónico del estudiante.',
            'student.email.email' => 'Ingrese un correo electrónico válido.',
            'student.address.required' => 'Ingrese la dirección de domicilio del estudiante.',
            'student.dni.unique' => 'Este DNI ya tiene una inscripción registrada en el ciclo seleccionado.',
            'student.payment_voucher_number.required' => 'Ingrese el número de voucher.',
            'student.payment_voucher_number.unique' => 'Este número de voucher ya fue registrado.',
            'student.payment_voucher_number.max' => 'El número de voucher no debe superar 40 caracteres.',
            'student.payment_voucher_number.regex' => 'El número de voucher solo debe contener dígitos.',
            'student.payment_agency_number.required' => 'Ingrese el número de agencia.',
            'student.payment_agency_number.digits' => 'El número de agencia debe contener exactamente 4 dígitos.',
            'student.payment_date.required' => 'Ingrese la fecha de pago.',
            'student.payment_date.date' => 'Ingrese una fecha de pago válida.',
            'student.payment_date.before_or_equal' => 'La fecha de pago no puede ser posterior a hoy.',
            'guardian.first_name.required' => 'Ingrese los nombres del apoderado.',
            'guardian.last_name.required' => 'Ingrese el apellido paterno del apoderado.',
            'guardian.mother_last_name.required' => 'Ingrese el apellido materno del apoderado.',
            'guardian.dni.required' => 'Ingrese el DNI del apoderado.',
            'guardian.dni.digits' => 'El DNI del apoderado debe contener exactamente 8 dígitos.',
            'guardian.phone.required' => 'Ingrese el celular del apoderado.',
            'guardian.phone.digits' => 'El celular del apoderado debe contener exactamente 9 dígitos.',
            'guardian.relationship.required' => 'Seleccione el parentesco del apoderado.',
            'guardian.relationship.in' => 'Seleccione un parentesco válido.',
            'school.name.required' => 'Ingrese el nombre del colegio.',
            'school.department.required' => 'Ingrese el departamento del colegio.',
            'school.province.required' => 'Ingrese la provincia del colegio.',
            'school.district.required' => 'Ingrese el distrito del colegio.',
            'school.graduation_year.required' => 'Ingrese el año de egreso.',
            'school.graduation_year.integer' => 'El año de egreso debe ser un número entero.',
            'school.graduation_year.digits' => 'El año de egreso debe contener exactamente 4 dígitos.',
            'school.graduation_year.min' => 'El año de egreso no puede ser menor a 1990.',
            'school.graduation_year.max' => 'El año de egreso no puede ser mayor a 2100.',
            'career_id.required' => 'Seleccione la carrera postulante.',
            'career_id.exists' => 'Seleccione una carrera válida.',
            'academic_cycle_shift_id.required' => 'Seleccione el ciclo, sede y turno.',
            'academic_cycle_shift_id.exists' => 'Seleccione un ciclo, sede y turno disponible.',
            'status.required' => 'Seleccione el estado del expediente.',
            'status.in' => 'Seleccione un estado válido.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function adminCareerAndScheduleRules(): array
    {
        return [
            'career_id' => ['required', 'integer', Rule::exists('careers', 'id')],
            'academic_cycle_shift_id' => ['required', 'integer', Rule::exists('academic_cycle_shifts', 'id')],
        ];
    }

    /**
     * Carrera activa y turno activo con cupo (portal publico).
     *
     * @return array<string, mixed>
     */
    protected function publicCareerAndScheduleRules(): array
    {
        return [
            'career_id' => ['required', 'integer', Rule::exists('careers', 'id')->where('status', true)],
            'academic_cycle_shift_id' => [
                'required',
                'integer',
                Rule::exists('academic_cycle_shifts', 'id')
                    ->where('status', true)
                    ->where(fn ($query) => $query->whereColumn('enrolled', '<', 'capacity')),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function staffStatusRule(): array
    {
        return [
            'status' => [
                auth()->check() ? 'required' : 'nullable',
                'string',
                Rule::in([Student::STATUS_PENDING, Student::STATUS_ACTIVE, Student::STATUS_REJECTED]),
            ],
        ];
    }

    private function uniqueStudentDniForSelectedCycle(?int $ignoreStudentId = null): mixed
    {
        $scheduleId = filter_var($this->input('academic_cycle_shift_id'), FILTER_VALIDATE_INT);

        if ($scheduleId === false) {
            return Rule::unique('students', 'dni')->where('id', 0);
        }

        $cycleId = AcademicCycleShift::query()
            ->whereKey($scheduleId)
            ->value('academic_cycle_id');

        if ($cycleId === null) {
            return Rule::unique('students', 'dni')->where('id', 0);
        }

        $rule = Rule::unique('students', 'dni')
            ->where('academic_cycle_id', $cycleId);

        return $ignoreStudentId === null ? $rule : $rule->ignore($ignoreStudentId);
    }

    private function uniquePaymentVoucherNumber(?int $ignoreStudentId = null): mixed
    {
        $rule = Rule::unique('students', 'payment_voucher_number');

        return $ignoreStudentId === null ? $rule : $rule->ignore($ignoreStudentId);
    }
}
