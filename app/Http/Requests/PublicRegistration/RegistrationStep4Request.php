<?php

namespace App\Http\Requests\PublicRegistration;

use App\Models\AcademicCycleShift;
use App\Models\Student;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

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
            'student.payment_voucher_number' => [
                'required',
                'string',
                'max:40',
                'regex:/^\d+$/',
                Rule::unique('students', 'payment_voucher_number'),
            ],
            'student.payment_agency_number' => ['required', 'digits:4'],
            'student.payment_date' => ['required', 'date', 'before_or_equal:today'],
            'botcheck' => ['prohibited'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $dni = data_get($this->session()->get('public_registration', []), 'student.dni');
            $scheduleId = filter_var($this->input('academic_cycle_shift_id'), FILTER_VALIDATE_INT);

            if (! is_string($dni) || $scheduleId === false) {
                return;
            }

            $cycleId = AcademicCycleShift::query()
                ->whereKey($scheduleId)
                ->value('academic_cycle_id');

            if ($cycleId === null) {
                return;
            }

            $alreadyRegistered = Student::query()
                ->where('dni', $dni)
                ->where('academic_cycle_id', $cycleId)
                ->exists();

            if ($alreadyRegistered) {
                $validator->errors()->add(
                    'academic_cycle_shift_id',
                    'Este DNI ya tiene una inscripción registrada en el ciclo seleccionado. Elija un ciclo diferente.',
                );
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'student.payment_voucher_number.unique' => 'Este número de voucher ya fue registrado.',
            'student.payment_voucher_number.regex' => 'El número de voucher solo debe contener dígitos.',
            'student.payment_date.before_or_equal' => 'La fecha de pago no puede ser posterior a hoy.',
        ];
    }
}
