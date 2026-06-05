<?php

namespace App\Policies;

use App\Models\User;

class BackgroundJobsPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('operations.jobs.view');
    }

    public function view(User $user): bool
    {
        return $user->can('operations.jobs.view');
    }

    public function retry(User $user): bool
    {
        return $user->can('operations.jobs.retry');
    }

    public function cancel(User $user): bool
    {
        return $user->can('operations.jobs.cancel');
    }
}
