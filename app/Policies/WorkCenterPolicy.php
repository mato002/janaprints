<?php

namespace App\Policies;

use App\Models\Production\WorkCenter;
use App\Models\User;
use App\Policies\Concerns\ChecksCrmTenant;

class WorkCenterPolicy
{
    use ChecksCrmTenant;

    public function viewAny(User $user): bool
    {
        return $user->can('production.work-centers.view');
    }

    public function view(User $user, WorkCenter $workCenter): bool
    {
        return $user->can('production.work-centers.view')
            && $this->sameTenant($user, $workCenter);
    }

    public function create(User $user): bool
    {
        return $user->can('production.edit');
    }

    public function update(User $user, WorkCenter $workCenter): bool
    {
        return $user->can('production.edit') && $this->sameTenant($user, $workCenter);
    }
}
