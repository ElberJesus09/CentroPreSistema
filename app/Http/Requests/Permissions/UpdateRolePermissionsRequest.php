<?php

namespace App\Http\Requests\Permissions;

use App\Models\Role;
use App\Support\Permissions\PermissionCatalog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRolePermissionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $role = $this->route('role');

        if (! ($user?->can('roles.update') || $user?->isSuperAdmin())) {
            return false;
        }

        return ! ($role instanceof Role && $role->name === Role::NAME_SUPER_ADMIN && ! $user->isSuperAdmin());
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in(PermissionCatalog::names())],
            'status' => ['required', 'boolean'],
        ];
    }

    /**
     * @return list<string>
     */
    public function permissionNames(): array
    {
        /** @var Role $role */
        $role = $this->route('role');

        if ($role->name === Role::NAME_SUPER_ADMIN) {
            return PermissionCatalog::names();
        }

        return array_values($this->validated('permissions', []));
    }
}
