<?php

namespace App\Policies;

use App\Models\Inventory\CycleCountSchedule;
use App\Models\User;
use App\Policies\Concerns\ChecksCrmTenant;

class CycleCountSchedulePolicy
{
    use ChecksCrmTenant;

    public function viewAny(User $user): bool
    {
        return $user->can('inventory.cycle.view');
    }

    public function view(User $user, CycleCountSchedule $schedule): bool
    {
        return $user->can('inventory.cycle.view') && $this->sameTenant($user, $schedule);
    }

    public function create(User $user): bool
    {
        return $user->can('inventory.cycle.manage');
    }

    public function update(User $user, CycleCountSchedule $schedule): bool
    {
        return $user->can('inventory.cycle.manage') && $this->sameTenant($user, $schedule);
    }

    public function generate(User $user, CycleCountSchedule $schedule): bool
    {
        return $user->can('inventory.cycle.manage') && $this->sameTenant($user, $schedule);
    }

    public function deactivate(User $user, CycleCountSchedule $schedule): bool
    {
        return $user->can('inventory.cycle.manage') && $this->sameTenant($user, $schedule);
    }
}
