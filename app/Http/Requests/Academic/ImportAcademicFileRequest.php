<?php

namespace App\Http\Requests\Academic;

use Illuminate\Foundation\Http\FormRequest;

class ImportAcademicFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canAccessAcademicManagementModule() ?? false;
    }

    public function rules(): array
    {
        return [
            'academic_cycle_id' => ['required', 'integer', 'exists:academic_cycles,id'],
            'file' => ['required', 'file', 'max:10240', 'extensions:csv,txt,xlsx', 'mimes:csv,txt,xlsx'],
        ];
    }
}
