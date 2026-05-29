<?php

namespace App\Http\Requests\PublicRegistration;

use App\Http\Requests\Concerns\ValidatesStudentRegistrationPayload;
use Illuminate\Foundation\Http\FormRequest;

class RegistrationStep2Request extends FormRequest
{
    use ValidatesStudentRegistrationPayload;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $draft = $this->session()->get('public_registration', []);
        $birthDate = $draft['student']['birth_date'] ?? null;

        $this->merge([
            'student' => [
                'birth_date' => $birthDate,
            ],
        ]);

        if ($this->boolean('skip_guardian')) {
            $this->merge([
                'guardian' => [],
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            ...$this->guardianRules(),
            'skip_guardian' => ['nullable', 'boolean'],
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
