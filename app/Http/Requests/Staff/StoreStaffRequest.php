<?php

namespace App\Http\Requests\Staff;

use App\Models\Staff;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreStaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Staff::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['required', 'string', 'max:120'],
            'mother_last_name' => ['required', 'string', 'max:120'],
            'dni' => ['required', 'digits:8', Rule::unique('staff', 'dni')],
            'phone' => ['required', 'digits:9'],
            'email' => ['required', 'email:rfc', 'max:255', Rule::unique('staff', 'email')],
            'username' => ['required', 'string', 'max:64', Rule::unique('staff', 'username')],
            'password' => ['required', 'confirmed', Password::min(8)],
            'role_id' => ['required', 'integer', Rule::exists('roles', 'id')],
            'status' => ['required', 'boolean'],
        ];
    }
}
