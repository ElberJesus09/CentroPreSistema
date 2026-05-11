<?php

namespace App\Policies;

use App\Models\AcademicCycle;
use App\Models\Role;
use App\Models\Staff;

class AcademicCyclePolicy
{
    public function viewAny(Staff $user): bool
    {
        return $this->adminRoles($user);
    }

    public function view(Staff $user, AcademicCycle $academicCycle): bool
    {
        return $this->adminRoles($user);
    }

    public function create(Staff $user): bool
    {
        return $this->adminRoles($user);
    }

    public function update(Staff $user, AcademicCycle $academicCycle): bool
    {
        return $this->adminRoles($user);
    }

    public function delete(Staff $user, AcademicCycle $academicCycle): bool
    {
        return $this->adminRoles($user);
    }

    /** super_admin y admin; trabajador sin acceso. */
    private function adminRoles(Staff $user): bool
    {
        $name = $user->role?->name;

        return $name === Role::NAME_SUPER_ADMIN || $name === Role::NAME_ADMIN;
    }
}
