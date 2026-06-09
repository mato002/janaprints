<?php

namespace App\Policies;

use App\Models\User;

class LeaveConfigurationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('hr.leave.config.view');
    }

    public function create(User $user): bool
    {
        return $user->can('hr.leave.config.create');
    }

    public function update(User $user): bool
    {
        return $user->can('hr.leave.config.edit');
    }

    public function manage(User $user): bool
    {
        return $user->can('hr.leave.config.manage');
    }
}
