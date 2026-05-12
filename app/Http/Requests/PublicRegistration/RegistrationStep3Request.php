<?php

namespace App\Http\Requests\PublicRegistration;

use Illuminate\Foundation\Http\FormRequest;

class RegistrationStep3Request extends FormRequest
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
            'school.name' => ['required', 'string', 'max:255'],
            'school.department' => ['required', 'string', 'max:120'],
            'school.province' => ['required', 'string', 'max:120'],
            'school.district' => ['required', 'string', 'max:120'],
            'school.graduation_year' => ['required', 'integer', 'digits:4', 'min:1990', 'max:2100'],
            'botcheck' => ['prohibited'],
        ];
    }
}
