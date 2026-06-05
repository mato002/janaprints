<?php

namespace App\Policies;

use App\Models\Assets\MaintenanceWorkOrder;
use App\Models\User;
use App\Policies\Concerns\ChecksCrmTenant;

class MaintenanceWorkOrderPolicy
{
    use ChecksCrmTenant;

    public function viewAny(User $user): bool
    {
        return $user->can('maintenance.view');
    }

    public function view(User $user, MaintenanceWorkOrder $order): bool
    {
        return $user->can('maintenance.view') && $this->sameTenant($user, $order);
    }

    public function create(User $user): bool
    {
        return $user->can('maintenance.create');
    }

    public function manage(User $user, MaintenanceWorkOrder $order): bool
    {
        return $user->can('maintenance.manage') && $this->sameTenant($user, $order);
    }

    public function assign(User $user, MaintenanceWorkOrder $order): bool
    {
        return $user->can('maintenance.assign') && $this->sameTenant($user, $order);
    }

    public function complete(User $user, MaintenanceWorkOrder $order): bool
    {
        return $user->can('maintenance.complete') && $this->sameTenant($user, $order);
    }

    public function close(User $user, MaintenanceWorkOrder $order): bool
    {
        return $user->can('maintenance.close') && $this->sameTenant($user, $order);
    }
}
