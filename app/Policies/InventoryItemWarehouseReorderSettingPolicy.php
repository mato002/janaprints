<?php

namespace App\Policies;

use App\Models\Inventory\InventoryItemWarehouseReorderSetting;
use App\Models\User;
use App\Policies\Concerns\ChecksCrmTenant;

class InventoryItemWarehouseReorderSettingPolicy
{
    use ChecksCrmTenant;

    public function viewAny(User $user): bool
    {
        return $user->can('inventory.reorder.configure');
    }

    public function create(User $user): bool
    {
        return $user->can('inventory.reorder.configure');
    }

    public function view(User $user, InventoryItemWarehouseReorderSetting $setting): bool
    {
        return $user->can('inventory.reorder.configure') && $this->sameTenant($user, $setting);
    }
}
