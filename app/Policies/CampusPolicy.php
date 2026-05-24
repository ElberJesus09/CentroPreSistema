<?php

namespace App\Policies;

use App\Models\Campus;
use App\Models\Role;
use App\Models\Staff;

class CampusPolicy
{
    public function viewAny(Staff $user): bool
    {
        return $user->can('academic-cycles.view') || $this->adminRoles($user);
    }

    public function view(Staff $user, Campus $campus): bool
    {
        return $user->can('academic-cycles.view') || $this->adminRoles($user);
    }

    public function create(Staff $user): bool
    {
        return $user->can('academic-cycles.manage') || $this->adminRoles($user);
    }

    public function update(Staff $user, Campus $campus): bool
    {
        return $user->can('academic-cycles.manage') || $this->adminRoles($user);
    }

    public function delete(Staff $user, Campus $campus): bool
    {
        return $user->can('academic-cycles.manage') || $this->adminRoles($user);
    }

    private function adminRoles(Staff $user): bool
    {
        $name = $user->role?->name;

        return $name === Role::NAME_SUPER_ADMIN || $name === Role::NAME_ADMIN;
    }
}
