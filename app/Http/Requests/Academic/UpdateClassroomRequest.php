<?php

namespace App\Http\Requests\Academic;

use App\Models\Classroom;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClassroomRequest extends FormRequest
{
    public function authorize(): bool
    {
        $classroom = $this->route('classroom');

        return $classroom instanceof Classroom && ($this->user()?->can('update', $classroom) ?? false);
    }

    public function rules(): array
    {
        $classroom = $this->route('classroom');
        $classroomId = $classroom instanceof Classroom ? $classroom->id : null;

        return [
            'academic_cycle_id' => ['required', 'integer', 'exists:academic_cycles,id'],
            'name' => ['required', 'string', 'max:120'],
            'code' => [
                'required',
                'string',
                'max:32',
                Rule::unique('classrooms', 'code')->where('academic_cycle_id', $this->integer('academic_cycle_id'))->whereNull('deleted_at')->ignore($classroomId),
            ],
            'floor' => ['required', 'integer', 'min:1', 'max:50'],
            'capacity' => ['required', 'integer', 'min:1', 'max:500'],
            'academic_priority' => ['required', 'integer', 'min:1', 'max:999'],
            'status' => ['required', 'boolean'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
