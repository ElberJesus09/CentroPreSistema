<?php

namespace App\Http\Requests\ExamSettings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExamSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && ($user->isSuperAdmin() || $user->isAdmin());
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'exam_date' => ['nullable', 'date'],
            'exam_time' => ['nullable', 'string', 'max:64'],
            'exam_location' => ['nullable', 'string', 'max:500'],
            'institutional_message' => ['nullable', 'string', 'max:5000'],
            'registration_mail_enabled' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $merge = [];
        foreach (['exam_time', 'exam_location', 'institutional_message'] as $key) {
            if ($this->has($key) && is_string($this->input($key))) {
                $merge[$key] = trim(strip_tags($this->input($key)));
            }
        }
        $merge['registration_mail_enabled'] = $this->boolean('registration_mail_enabled');
        $this->merge($merge);
    }
}
