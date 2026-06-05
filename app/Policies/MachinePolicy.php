<?php

namespace App\Policies;

use App\Models\Assets\MachineProfile;
use App\Models\User;
use App\Policies\Concerns\ChecksCrmTenant;

class MachinePolicy
{
    use ChecksCrmTenant;

    public function viewAny(User $user): bool
    {
        return $user->can('machines.view');
    }

    public function create(User $user): bool
    {
        return $user->can('machines.manage');
    }

    public function view(User $user, MachineProfile $machine): bool
    {
        return $user->can('machines.view') && $this->sameTenant($user, $machine);
    }

    public function manage(User $user, MachineProfile $machine): bool
    {
        return $user->can('machines.manage') && $this->sameTenant($user, $machine);
    }

    public function updateCapacity(User $user, MachineProfile $machine): bool
    {
        return $user->can('machines.capacity.manage') && $this->sameTenant($user, $machine);
    }

    public function assign(User $user, MachineProfile $machine): bool
    {
        return $user->can('machines.assign') && $this->sameTenant($user, $machine);
    }
}
