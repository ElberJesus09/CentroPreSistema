<?php

namespace App\Http\Requests\PublicRegistration;

use App\Http\Requests\Concerns\ValidatesStudentRegistrationPayload;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegistrationStep1Request extends FormRequest
{
    use ValidatesStudentRegistrationPayload {
        messages as registrationMessages;
    }

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $department = trim((string) $this->input('address_department', ''));
        $province = trim((string) $this->input('address_province', ''));
        $district = trim((string) $this->input('address_district', ''));
        $line = trim((string) $this->input('address_line', ''));

        $this->merge([
            'address_department' => $department,
            'address_province' => $province,
            'address_district' => $district,
            'address_line' => $line,
            'student' => array_merge($this->input('student', []), [
                'address' => collect([$line, $district, $province, $department])
                    ->filter(fn (string $part): bool => $part !== '')
                    ->implode(', '),
            ]),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $locations = $this->locations();

        return [
            'student.first_name' => ['required', 'string', 'max:120'],
            'student.last_name' => ['required', 'string', 'max:120'],
            'student.mother_last_name' => ['required', 'string', 'max:120'],
            'student.dni' => ['required', 'digits:8'],
            'student.birth_date' => $this->studentBirthDateRules(),
            'student.gender' => ['required', 'string', 'in:male,female'],
            'student.phone' => ['required', 'digits:9'],
            'student.address' => ['required', 'string', 'max:500'],
            'student.email' => ['required', 'email:rfc', 'max:255'],
            'address_department' => ['required', 'string', Rule::in(array_keys($locations))],
            'address_province' => [
                'required',
                'string',
                function (string $attribute, mixed $value, \Closure $fail) use ($locations): void {
                    $department = (string) $this->input('address_department', '');
                    if (! isset($locations[$department]) || ! array_key_exists((string) $value, $locations[$department])) {
                        $fail('Seleccione una provincia válida para el departamento elegido.');
                    }
                },
            ],
            'address_district' => [
                'required',
                'string',
                function (string $attribute, mixed $value, \Closure $fail) use ($locations): void {
                    $department = (string) $this->input('address_department', '');
                    $province = (string) $this->input('address_province', '');
                    $districts = $locations[$department][$province] ?? [];
                    if (! in_array((string) $value, $districts, true)) {
                        $fail('Seleccione un distrito válido para la provincia elegida.');
                    }
                },
            ],
            'address_line' => ['required', 'string', 'max:300'],
            'botcheck' => ['prohibited'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return array_merge($this->registrationMessages(), [
            'address_department.required' => 'Seleccione el departamento de domicilio.',
            'address_department.in' => 'Seleccione un departamento válido.',
            'address_province.required' => 'Seleccione la provincia de domicilio.',
            'address_district.required' => 'Seleccione el distrito de domicilio.',
            'address_line.required' => 'Ingrese la calle, jirón, avenida o referencia del domicilio.',
            'address_line.max' => 'La dirección del domicilio no debe superar 300 caracteres.',
        ]);
    }

    /**
     * @return array<string, array<string, list<string>>>
     */
    private function locations(): array
    {
        $locations = config('peru_locations', []);

        return is_array($locations) ? $locations : [];
    }
}
