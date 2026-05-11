<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\Staff;
use App\Models\Student;

class StudentPolicy
{
    public function viewAny(Staff $user): bool
    {
        return $this->staffWithStudentAccess($user);
    }

    public function view(Staff $user, Student $student): bool
    {
        return $this->staffWithStudentAccess($user);
    }

    public function create(Staff $user): bool
    {
        return $this->staffWithStudentAccess($user);
    }

    public function update(Staff $user, Student $student): bool
    {
        return $this->staffWithStudentAccess($user);
    }

    /** trabajador no elimina; admin y super_admin si. */
    public function delete(Staff $user, Student $student): bool
    {
        $name = $user->role?->name;

        return $name === Role::NAME_SUPER_ADMIN || $name === Role::NAME_ADMIN;
    }

    /** Roles con acceso al modulo alumnos. */
    private function staffWithStudentAccess(Staff $user): bool
    {
        $name = $user->role?->name;

        return in_array($name, [
            Role::NAME_SUPER_ADMIN,
            Role::NAME_ADMIN,
            Role::NAME_TRABAJADOR,
        ], true);
    }
}
