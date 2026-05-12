<?php

namespace App\Http\Requests\PublicRegistration;

use App\Http\Requests\Concerns\ValidatesStudentRegistrationPayload;
use Illuminate\Foundation\Http\FormRequest;

class FinalizePublicRegistrationRequest extends FormRequest
{
    use ValidatesStudentRegistrationPayload;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $draft = $this->session()->get('public_registration', []);
        $this->merge([
            'student' => $draft['student'] ?? [],
            'guardian' => $draft['guardian'] ?? [],
            'school' => $draft['school'] ?? [],
            'career_id' => $draft['career_id'] ?? null,
            'academic_cycle_shift_id' => $draft['academic_cycle_shift_id'] ?? null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return array_merge(
            $this->studentGuardianSchoolRules(),
            $this->publicCareerAndScheduleRules(),
            [
                'botcheck' => ['prohibited'],
            ]
        );
    }
}
