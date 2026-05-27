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

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'guardian.first_name.required' => 'Ingrese los nombres del apoderado.',
            'guardian.last_name.required' => 'Ingrese el apellido paterno del apoderado.',
            'guardian.mother_last_name.required' => 'Ingrese el apellido materno del apoderado.',
            'guardian.dni.required' => 'Ingrese el DNI del apoderado.',
            'guardian.dni.digits' => 'El DNI del apoderado debe contener exactamente 8 dígitos.',
            'guardian.phone.required' => 'Ingrese el celular del apoderado.',
            'guardian.phone.digits' => 'El celular del apoderado debe contener exactamente 9 dígitos.',
            'guardian.relationship.required' => 'Seleccione el parentesco del apoderado.',
            'guardian.relationship.in' => 'Seleccione un parentesco válido.',
        ];
    }
}
