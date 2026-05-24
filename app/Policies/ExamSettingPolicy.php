<?php

namespace App\Policies;

use App\Models\ExamSetting;
use App\Models\Staff;

class ExamSettingPolicy
{
    public function viewAny(Staff $user): bool
    {
        return $user->can('exam-settings.update') || $user->isSuperAdmin() || $user->isAdmin();
    }

    public function view(Staff $user, ExamSetting $examSetting): bool
    {
        return $user->can('exam-settings.update') || $user->isSuperAdmin() || $user->isAdmin();
    }

    public function update(Staff $user, ExamSetting $examSetting): bool
    {
        return $user->can('exam-settings.update') || $user->isSuperAdmin() || $user->isAdmin();
    }
}
