<?php

namespace App\Http\Requests\Student;

use App\Http\Requests\Concerns\ValidatesStudentRegistrationPayload;
use App\Models\Student;
use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentRequest extends FormRequest
{
    use ValidatesStudentRegistrationPayload;

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

        return array_merge(
            $this->studentGuardianSchoolRules($student->id),
            $this->adminCareerAndScheduleRules(),
            $this->staffStatusRule(),
        );
    }
}
