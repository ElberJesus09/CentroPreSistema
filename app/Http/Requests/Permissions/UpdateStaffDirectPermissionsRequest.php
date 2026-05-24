<?php

namespace App\Http\Requests\Permissions;

use App\Support\Permissions\PermissionCatalog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStaffDirectPermissionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user?->can('roles.update') || $user?->isSuperAdmin();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'direct_permissions' => ['nullable', 'array'],
            'direct_permissions.*' => ['string', Rule::in(PermissionCatalog::names())],
            'expires_at' => ['nullable', 'required_with:direct_permissions', 'date', 'after:now'],
            'duration_minutes' => ['nullable', 'integer', 'min:1', 'max:43200'],
        ];
    }

    /**
     * @return list<string>
     */
    public function permissionNames(): array
    {
        return array_values($this->validated('direct_permissions', []));
    }

    public function expiresAt(): ?string
    {
        $durationMinutes = $this->validated('duration_minutes');

        if ($durationMinutes !== null && $durationMinutes !== '') {
            return now()->addMinutes((int) $durationMinutes)->toDateTimeString();
        }

        return $this->validated('expires_at');
    }
}
