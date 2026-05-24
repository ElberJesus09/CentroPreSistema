<?php

namespace App\Http\Requests\Staff;

use App\Models\Role;
use App\Models\Staff;
use App\Support\Permissions\PermissionCatalog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateStaffRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (! filled($this->input('password'))) {
            $this->merge([
                'password' => null,
                'password_confirmation' => null,
            ]);
        }
    }

    public function authorize(): bool
    {
        /** @var Staff $staff */
        $staff = $this->route('staff');

        return $this->user()?->can('update', $staff) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Staff $staff */
        $staff = $this->route('staff');

        return [
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['required', 'string', 'max:120'],
            'mother_last_name' => ['required', 'string', 'max:120'],
            'dni' => ['required', 'digits:8', Rule::unique('staff', 'dni')->ignore($staff->id)],
            'phone' => ['required', 'digits:9'],
            'email' => ['required', 'email:rfc', 'max:255', Rule::unique('staff', 'email')->ignore($staff->id)],
            'username' => ['required', 'string', 'max:64', Rule::unique('staff', 'username')->ignore($staff->id)],
            'password' => ['nullable', 'confirmed', Password::min(8)],
            'role_id' => ['required', 'integer', $this->assignableRoleRule()],
            'status' => ['required', 'boolean'],
            'direct_permissions' => ['nullable', 'array'],
            'direct_permissions.*' => ['string', Rule::in(PermissionCatalog::names())],
        ];
    }

    private function assignableRoleRule(): mixed
    {
        /** @var Staff $staff */
        $staff = $this->route('staff');
        $allowed = Role::assignableNamesForActor($this->user());

        if (
            $this->user()?->isSuperAdmin()
            && $staff->role?->name === Role::NAME_SUPER_ADMIN
            && ! in_array(Role::NAME_SUPER_ADMIN, $allowed, true)
        ) {
            $allowed[] = Role::NAME_SUPER_ADMIN;
        }

        return Rule::exists('roles', 'id')
            ->where('status', true)
            ->whereIn('name', $allowed);
    }

    /**
     * @return array<string, mixed>
     */
    public function validatedForUpdate(): array
    {
        $data = $this->validated();
        unset($data['password_confirmation']);
        unset($data['direct_permissions']);
        if (empty($data['password'])) {
            unset($data['password']);
        }

        return $data;
    }

    /**
     * @return list<string>|null
     */
    public function directPermissionNames(): ?array
    {
        if (! ($this->user()?->can('roles.update') || $this->user()?->isSuperAdmin())) {
            return null;
        }

        return array_values($this->validated('direct_permissions', []));
    }
}
