<?php

namespace App\Http\Requests\PublicRegistration;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegistrationStep2Request extends FormRequest
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
            'guardian.first_name' => ['required', 'string', 'max:120'],
            'guardian.last_name' => ['required', 'string', 'max:120'],
            'guardian.mother_last_name' => ['required', 'string', 'max:120'],
            'guardian.dni' => ['required', 'digits:8'],
            'guardian.phone' => ['required', 'digits:9'],
            'guardian.relationship' => ['required', 'string', Rule::in(['father', 'mother', 'uncle', 'aunt', 'guardian'])],
            'botcheck' => ['prohibited'],
        ];
    }
}
