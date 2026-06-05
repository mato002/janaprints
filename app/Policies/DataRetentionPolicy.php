<?php

namespace App\Policies;

use App\Models\User;

class DataRetentionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('operations.retention.view');
    }

    public function view(User $user): bool
    {
        return $user->can('operations.retention.view');
    }

    public function manage(User $user): bool
    {
        return $user->can('operations.retention.manage');
    }
}
