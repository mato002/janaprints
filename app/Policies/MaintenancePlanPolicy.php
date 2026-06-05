<?php

namespace App\Policies;

use App\Models\Assets\MaintenancePlan;
use App\Models\User;
use App\Policies\Concerns\ChecksCrmTenant;

class MaintenancePlanPolicy
{
    use ChecksCrmTenant;

    public function viewAny(User $user): bool
    {
        return $user->can('maintenance.view');
    }

    public function view(User $user, MaintenancePlan $plan): bool
    {
        return $user->can('maintenance.view') && $this->sameTenant($user, $plan);
    }

    public function create(User $user): bool
    {
        return $user->can('maintenance.create');
    }

    public function manage(User $user, MaintenancePlan $plan): bool
    {
        return $user->can('maintenance.manage') && $this->sameTenant($user, $plan);
    }
}
