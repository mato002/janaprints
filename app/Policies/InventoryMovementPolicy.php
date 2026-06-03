<?php

namespace App\Policies;

use App\Models\Inventory\InventoryMovement;
use App\Models\User;
use App\Policies\Concerns\ChecksCrmTenant;

class InventoryMovementPolicy
{
    use ChecksCrmTenant;

    public function viewAny(User $user): bool
    {
        return $user->can('inventory.view');
    }

    public function view(User $user, InventoryMovement $movement): bool
    {
        return $user->can('inventory.view') && $this->sameTenant($user, $movement);
    }
}
