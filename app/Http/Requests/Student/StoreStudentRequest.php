<?php

namespace App\Http\Requests\Student;

use App\Http\Requests\Concerns\ValidatesStudentRegistrationPayload;
use App\Models\Student;
use Illuminate\Foundation\Http\FormRequest;

class StoreStudentRequest extends FormRequest
{
    use ValidatesStudentRegistrationPayload;

    public function authorize(): bool
    {
        if ($this->user() === null) {
            return true;
        }

        return $this->user()->can('create', Student::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return array_merge(
            $this->studentGuardianSchoolRules(),
            $this->adminCareerAndScheduleRules(),
            $this->staffStatusRule(),
        );
    }
}
