<?php

namespace App\Http\Requests\AcademicCycle;

use App\Models\AcademicCycle;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAcademicCycleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', AcademicCycle::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:20', Rule::unique('academic_cycles', 'name')],
            'status' => ['required', 'boolean'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ];
    }
}
