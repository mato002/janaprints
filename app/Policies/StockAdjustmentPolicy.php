<?php

namespace App\Policies;

use App\Enums\InventoryDocumentStatus;
use App\Models\Inventory\StockAdjustment;
use App\Models\User;
use App\Policies\Concerns\ChecksCrmTenant;

class StockAdjustmentPolicy
{
    use ChecksCrmTenant;

    public function viewAny(User $user): bool
    {
        return $user->can('inventory.view');
    }

    public function view(User $user, StockAdjustment $adjustment): bool
    {
        return $user->can('inventory.view') && $this->sameTenant($user, $adjustment);
    }

    public function create(User $user): bool
    {
        return $user->can('inventory.adjust');
    }

    public function post(User $user, StockAdjustment $adjustment): bool
    {
        return $user->can('inventory.adjust')
            && $this->sameTenant($user, $adjustment)
            && $adjustment->status === InventoryDocumentStatus::Draft;
    }
}
