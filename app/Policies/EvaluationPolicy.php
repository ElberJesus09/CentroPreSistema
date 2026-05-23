<?php

namespace App\Policies;

use App\Models\Evaluation;
use App\Models\Role;
use App\Models\Staff;

class EvaluationPolicy
{
    public function viewAny(Staff $user): bool
    {
        return $user->canAccessAcademicManagementModule();
    }

    public function view(Staff $user, Evaluation $evaluation): bool
    {
        return $this->viewAny($user);
    }

    public function create(Staff $user): bool
    {
        return $this->isManager($user);
    }

    public function update(Staff $user, Evaluation $evaluation): bool
    {
        return $this->isManager($user);
    }

    public function delete(Staff $user, Evaluation $evaluation): bool
    {
        return $this->isManager($user);
    }

    private function isManager(Staff $user): bool
    {
        return in_array($user->role?->name, [Role::NAME_SUPER_ADMIN, Role::NAME_ADMIN], true);
    }
}
