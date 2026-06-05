<?php

namespace App\Policies;

use App\Models\Assets\MaintenanceTechnician;
use App\Models\User;
use App\Policies\Concerns\ChecksCrmTenant;

class MaintenanceTechnicianPolicy
{
    use ChecksCrmTenant;

    public function viewAny(User $user): bool
    {
        return $user->can('maintenance.view');
    }

    public function create(User $user): bool
    {
        return $user->can('maintenance.manage');
    }

    public function view(User $user, MaintenanceTechnician $technician): bool
    {
        return $user->can('maintenance.view') && $this->sameTenant($user, $technician);
    }
}
