<?php

namespace App\Http\Requests\Student;

use App\Models\Student;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Student|null $student */
        $student = $this->route('student');

        return $student !== null && ($this->user()?->can('update', $student) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Student $student */
        $student = $this->route('student');

        return [
            'student.first_name' => ['required', 'string', 'max:120'],
            'student.last_name' => ['required', 'string', 'max:120'],
            'student.mother_last_name' => ['required', 'string', 'max:120'],
            'student.dni' => ['required', 'digits:8', Rule::unique('students', 'dni')->ignore($student->id)],
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

            'career_id' => ['required', 'integer', Rule::exists('careers', 'id')],
            'academic_cycle_shift_id' => ['required', 'integer', Rule::exists('academic_cycle_shifts', 'id')],
            'status' => ['required', 'string', Rule::in([Student::STATUS_PENDING, Student::STATUS_ACTIVE, Student::STATUS_REJECTED])],
        ];
    }
}
