<?php

namespace App\Http\Requests\PublicRegistration;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegistrationStep4Request extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
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
            'botcheck' => ['prohibited'],
        ];
    }
}
