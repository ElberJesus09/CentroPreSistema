<?php

namespace App\Http\Requests\Academic;

use App\Models\Evaluation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEvaluationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $evaluation = $this->route('evaluation');

        return $evaluation instanceof Evaluation && ($this->user()?->can('update', $evaluation) ?? false);
    }

    public function rules(): array
    {
        $evaluation = $this->route('evaluation');
        $evaluationId = $evaluation instanceof Evaluation ? $evaluation->id : null;

        return [
            'academic_cycle_id' => ['required', 'integer', 'exists:academic_cycles,id'],
            'name' => [
                'required',
                'string',
                'max:120',
                Rule::unique('evaluations', 'name')->where('academic_cycle_id', $this->integer('academic_cycle_id'))->whereNull('deleted_at')->ignore($evaluationId),
            ],
            'type' => ['required', 'string', 'max:40'],
            'weight' => ['required', 'numeric', 'min:0', 'max:100'],
            'counts_for_average' => ['required', 'boolean'],
            'rounding_decimals' => ['required', 'integer', 'min:0', 'max:4'],
            'status' => ['required', 'boolean'],
        ];
    }
}
