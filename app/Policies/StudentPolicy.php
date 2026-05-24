<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\Staff;
use App\Models\Student;

class StudentPolicy
{
    /** trabajador, admin y super_admin listan expedientes. */
    public function viewAny(Staff $user): bool
    {
        return $user->can('students.view') || $this->staffWithStudentAccess($user);
    }

    /** Lectura de detalle alineada al modulo alumnos. */
    public function view(Staff $user, Student $student): bool
    {
        return $user->can('students.view') || $this->staffWithStudentAccess($user);
    }

    /** Alta permitida a personal operativo y administradores. */
    public function create(Staff $user): bool
    {
        return $user->can('students.create') || $this->staffWithStudentAccess($user);
    }

    /** Edicion permitida al mismo conjunto de roles. */
    public function update(Staff $user, Student $student): bool
    {
        return $user->can('students.update') || $this->staffWithStudentAccess($user);
    }

    /** Reenvio de correo de confirmacion (mismos roles que edicion). */
    public function resendRegistrationMail(Staff $user, Student $student): bool
    {
        return $user->can('students.documents') || $this->update($user, $student);
    }

    /** Descarga PDFs de inscripcion sin enviar correo (mismos roles que edicion). */
    public function downloadRegistrationDocuments(Staff $user, Student $student): bool
    {
        return $this->update($user, $student);
    }

    /** trabajador: sin baja; admin y super_admin eliminan y liberan cupo. */
    public function delete(Staff $user, Student $student): bool
    {
        $name = $user->role?->name;

        return $user->can('students.delete') || $name === Role::NAME_SUPER_ADMIN || $name === Role::NAME_ADMIN;
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
