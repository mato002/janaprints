<?php

namespace App\Policies;

use App\Models\User;

class InventoryVariancePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('inventory.variance.view');
    }
}
