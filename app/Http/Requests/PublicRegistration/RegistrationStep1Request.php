<?php

namespace App\Http\Requests\PublicRegistration;

use Illuminate\Foundation\Http\FormRequest;

class RegistrationStep1Request extends FormRequest
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
            'student.first_name' => ['required', 'string', 'max:120'],
            'student.last_name' => ['required', 'string', 'max:120'],
            'student.mother_last_name' => ['required', 'string', 'max:120'],
            'student.dni' => ['required', 'digits:8'],
            'student.birth_date' => ['required', 'date'],
            'student.gender' => ['required', 'string', 'in:male,female'],
            'student.phone' => ['required', 'digits:9'],
            'student.address' => ['required', 'string', 'max:500'],
            'student.email' => ['required', 'email:rfc', 'max:255'],
            'botcheck' => ['prohibited'],
        ];
    }
}
