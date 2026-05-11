<?php

namespace App\Http\Requests\Campus;

use App\Models\Campus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCampusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Campus::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('campuses', 'name')],
            'address' => ['nullable', 'string', 'max:500'],
            'status' => ['required', 'boolean'],
        ];
    }
}
