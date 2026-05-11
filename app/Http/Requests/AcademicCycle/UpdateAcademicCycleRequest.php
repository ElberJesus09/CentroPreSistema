<?php

namespace App\Http\Requests\AcademicCycle;

use App\Models\AcademicCycle;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAcademicCycleRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var AcademicCycle|null $cycle */
        $cycle = $this->route('academic_cycle');

        return $cycle !== null && ($this->user()?->can('update', $cycle) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var AcademicCycle $cycle */
        $cycle = $this->route('academic_cycle');

        return [
            'name' => [
                'required',
                'string',
                'max:20',
                Rule::unique('academic_cycles', 'name')->ignore($cycle->id),
            ],
            'status' => ['required', 'boolean'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ];
    }
}
