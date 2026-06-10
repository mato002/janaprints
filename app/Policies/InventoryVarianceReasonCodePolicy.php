<?php

namespace App\Policies;

use App\Models\Inventory\InventoryVarianceReasonCode;
use App\Models\User;

class InventoryVarianceReasonCodePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('inventory.variance-reasons.view');
    }

    public function view(User $user, InventoryVarianceReasonCode $code): bool
    {
        return $user->can('inventory.variance-reasons.view')
            && (int) $user->company_id === (int) $code->company_id;
    }

    public function create(User $user): bool
    {
        return $user->can('inventory.variance-reasons.manage');
    }

    public function update(User $user, InventoryVarianceReasonCode $code): bool
    {
        return $user->can('inventory.variance-reasons.manage')
            && (int) $user->company_id === (int) $code->company_id;
    }

    public function delete(User $user, InventoryVarianceReasonCode $code): bool
    {
        return $user->can('inventory.variance-reasons.manage')
            && (int) $user->company_id === (int) $code->company_id;
    }
}
