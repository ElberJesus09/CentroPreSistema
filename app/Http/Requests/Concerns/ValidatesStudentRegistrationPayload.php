<?php

namespace App\Http\Requests\Concerns;

use App\Models\AcademicCycleShift;
use App\Models\Student;
use Illuminate\Validation\Rule;

trait ValidatesStudentRegistrationPayload
{
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
            'student.birth_date' => ['required', 'date'],
            'student.gender' => ['required', 'string', Rule::in(['male', 'female'])],
            'student.phone' => ['required', 'digits:9'],
            'student.address' => ['required', 'string', 'max:500'],
            'student.email' => ['required', 'email:rfc', 'max:255'],

            'guardian.first_name' => ['required', 'string', 'max:120'],
            'guardian.last_name' => ['required', 'string', 'max:120'],
            'guardian.mother_last_name' => ['required', 'string', 'max:120'],
            'guardian.dni' => ['required', 'digits:8'],
            'guardian.phone' => ['required', 'digits:9'],
            'guardian.relationship' => ['required', 'string', Rule::in(['father', 'mother', 'uncle', 'aunt', 'guardian'])],

            'school.name' => ['required', 'string', 'max:255'],
            'school.department' => ['required', 'string', 'max:120'],
            'school.province' => ['required', 'string', 'max:120'],
            'school.district' => ['required', 'string', 'max:120'],
            'school.graduation_year' => ['required', 'integer', 'digits:4', 'min:1990', 'max:2100'],
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
}
