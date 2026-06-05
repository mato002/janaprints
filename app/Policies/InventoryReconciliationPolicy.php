<?php

namespace App\Policies;

use App\Models\Inventory\InventoryReconciliation;
use App\Models\User;
use App\Policies\Concerns\ChecksCrmTenant;

class InventoryReconciliationPolicy
{
    use ChecksCrmTenant;

    public function viewAny(User $user): bool
    {
        return $user->can('inventory.reconcile.view');
    }

    public function view(User $user, InventoryReconciliation $reconciliation): bool
    {
        return $user->can('inventory.reconcile.view') && $this->sameTenant($user, $reconciliation);
    }

    public function approve(User $user, InventoryReconciliation $reconciliation): bool
    {
        return $user->can('inventory.reconcile.approve')
            && $this->sameTenant($user, $reconciliation)
            && $reconciliation->status->canApprove();
    }

    public function post(User $user, InventoryReconciliation $reconciliation): bool
    {
        return $user->can('inventory.reconcile.post')
            && $this->sameTenant($user, $reconciliation)
            && in_array($reconciliation->status, [
                \App\Enums\InventoryReconciliationStatus::Pending,
                \App\Enums\InventoryReconciliationStatus::Approved,
            ], true);
    }
}
