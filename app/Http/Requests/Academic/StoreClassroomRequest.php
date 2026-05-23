<?php

namespace App\Http\Requests\Academic;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClassroomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\Classroom::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'academic_cycle_id' => ['required', 'integer', 'exists:academic_cycles,id'],
            'name' => ['required', 'string', 'max:120'],
            'code' => [
                'required',
                'string',
                'max:32',
                Rule::unique('classrooms', 'code')->where('academic_cycle_id', $this->integer('academic_cycle_id'))->whereNull('deleted_at'),
            ],
            'floor' => ['required', 'integer', 'min:1', 'max:50'],
            'capacity' => ['required', 'integer', 'min:1', 'max:500'],
            'academic_priority' => ['required', 'integer', 'min:1', 'max:999'],
            'status' => ['required', 'boolean'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
