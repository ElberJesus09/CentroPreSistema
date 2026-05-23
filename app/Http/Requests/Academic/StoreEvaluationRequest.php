<?php

namespace App\Http\Requests\Academic;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEvaluationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\Evaluation::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'academic_cycle_id' => ['required', 'integer', 'exists:academic_cycles,id'],
            'name' => [
                'required',
                'string',
                'max:120',
                Rule::unique('evaluations', 'name')->where('academic_cycle_id', $this->integer('academic_cycle_id'))->whereNull('deleted_at'),
            ],
            'type' => ['required', 'string', 'max:40'],
            'weight' => ['required', 'numeric', 'min:0', 'max:100'],
            'counts_for_average' => ['required', 'boolean'],
            'rounding_decimals' => ['required', 'integer', 'min:0', 'max:4'],
            'status' => ['required', 'boolean'],
        ];
    }
}
