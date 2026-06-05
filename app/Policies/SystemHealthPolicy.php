<?php

namespace App\Policies;

use App\Models\User;
use App\Operations\SystemHealthCenter;

class SystemHealthPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('operations.health.view');
    }

    public function manage(User $user): bool
    {
        return $user->can('operations.health.manage');
    }

    public function refresh(User $user, SystemHealthCenter $center): bool
    {
        return $user->can('operations.health.manage');
    }
}
