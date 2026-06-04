<?php

namespace App\Policies;

use App\Models\Inventory\Warehouse;
use App\Models\User;
use App\Policies\Concerns\ChecksCrmTenant;

class WarehousePolicy
{
    use ChecksCrmTenant;

    public function viewAny(User $user): bool
    {
        return $user->can('inventory.view');
    }

    public function view(User $user, Warehouse $warehouse): bool
    {
        return $user->can('inventory.view') && $this->sameTenant($user, $warehouse);
    }

    public function create(User $user): bool
    {
        return $user->can('inventory.create');
    }

    public function update(User $user, Warehouse $warehouse): bool
    {
        return $user->can('inventory.edit') && $this->sameTenant($user, $warehouse);
    }

    public function manage(User $user, Warehouse $warehouse): bool
    {
        return $user->can('inventory.edit') && $this->sameTenant($user, $warehouse);
    }

    public function delete(User $user, Warehouse $warehouse): bool
    {
        return $user->can('inventory.delete') && $this->sameTenant($user, $warehouse);
    }
}
