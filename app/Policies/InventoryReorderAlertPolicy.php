<?php

namespace App\Policies;

use App\Enums\ReorderAlertStatus;
use App\Models\Inventory\InventoryReorderAlert;
use App\Models\User;
use App\Policies\Concerns\ChecksCrmTenant;

class InventoryReorderAlertPolicy
{
    use ChecksCrmTenant;

    public function viewAny(User $user): bool
    {
        return $user->can('inventory.reorder.view') || $user->can('inventory.view');
    }

    public function view(User $user, InventoryReorderAlert $alert): bool
    {
        return ($user->can('inventory.reorder.view') || $user->can('inventory.view'))
            && $this->sameTenant($user, $alert);
    }

    public function acknowledge(User $user, InventoryReorderAlert $alert): bool
    {
        return $user->can('inventory.edit')
            && $this->sameTenant($user, $alert)
            && $alert->status === ReorderAlertStatus::Open;
    }

    public function resolve(User $user, InventoryReorderAlert $alert): bool
    {
        return $user->can('inventory.edit')
            && $this->sameTenant($user, $alert)
            && $alert->status->isActionable();
    }

    public function createPurchaseRequest(User $user, InventoryReorderAlert $alert): bool
    {
        return $user->can('procurement.requests.create')
            && $this->sameTenant($user, $alert)
            && $alert->status->isActionable();
    }
}
