<?php

namespace App\Policies;

use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionQueue;
use App\Models\User;
use App\Policies\Concerns\ChecksCrmTenant;

class ProductionQueuePolicy
{
    use ChecksCrmTenant;

    public function viewWorkspace(User $user): bool
    {
        return $user->can('production.queue.view');
    }

    public function viewAny(User $user, ProductionJobCard $jobCard): bool
    {
        return $user->can('production.view') && $this->sameTenant($user, $jobCard);
    }

    public function create(User $user, ProductionJobCard $jobCard): bool
    {
        return $user->can('production.schedule') && $this->sameTenant($user, $jobCard);
    }

    public function update(User $user, ProductionQueue $queue): bool
    {
        return $user->can('production.schedule')
            && $this->sameTenant($user, $queue->jobCard);
    }

    public function delete(User $user, ProductionQueue $queue): bool
    {
        return $user->can('production.schedule')
            && $this->sameTenant($user, $queue->jobCard);
    }
}
