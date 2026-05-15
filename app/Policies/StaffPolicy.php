<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\Staff;

class StaffPolicy
{
    public function viewAny(Staff $user): bool
    {
        return $this->staffManagementRoles($user);
    }

    public function view(Staff $user, Staff $model): bool
    {
        return $this->staffManagementRoles($user);
    }

    public function create(Staff $user): bool
    {
        return $this->staffManagementRoles($user);
    }

    public function update(Staff $user, Staff $model): bool
    {
        if (! $this->staffManagementRoles($user)) {
            return false;
        }

        if ($model->role?->name === Role::NAME_SUPER_ADMIN && ! $user->isSuperAdmin()) {
            return false;
        }

        return true;
    }

    public function delete(Staff $user, Staff $model): bool
    {
        if (! $this->staffManagementRoles($user)) {
            return false;
        }

        if ($user->id === $model->id) {
            return false;
        }

        return $model->role?->name !== Role::NAME_SUPER_ADMIN || $user->isSuperAdmin();
    }

    /** super_admin y admin gestionan staff; trabajador no. */
    private function staffManagementRoles(Staff $user): bool
    {
        $name = $user->role?->name;

        return $name === Role::NAME_SUPER_ADMIN || $name === Role::NAME_ADMIN;
    }
}
