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

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'school.name.required' => 'Ingrese el nombre del colegio.',
            'school.department.required' => 'Ingrese el departamento del colegio.',
            'school.province.required' => 'Ingrese la provincia del colegio.',
            'school.district.required' => 'Ingrese el distrito del colegio.',
            'school.graduation_year.required' => 'Ingrese el año de egreso.',
            'school.graduation_year.integer' => 'El año de egreso debe ser un número entero.',
            'school.graduation_year.digits' => 'El año de egreso debe contener exactamente 4 dígitos.',
            'school.graduation_year.min' => 'El año de egreso no puede ser menor a 1990.',
            'school.graduation_year.max' => 'El año de egreso no puede ser mayor a 2100.',
        ];
    }
}
