<?php

namespace App\Http\Requests\Academic;

use Illuminate\Foundation\Http\FormRequest;

class MoveStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canAccessAcademicManagementModule() ?? false;
    }

    public function rules(): array
    {
        return [
            'academic_cycle_id' => ['required', 'integer', 'exists:academic_cycles,id'],
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'classroom_id' => ['required', 'integer', 'exists:classrooms,id'],
            'reason' => ['nullable', 'string', 'max:500'],
        ];
    }
}
