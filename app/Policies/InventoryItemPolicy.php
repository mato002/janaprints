<?php

namespace App\Policies;

use App\Models\Inventory\InventoryItem;
use App\Models\User;
use App\Policies\Concerns\ChecksCrmTenant;

class InventoryItemPolicy
{
    use ChecksCrmTenant;

    public function viewAny(User $user): bool
    {
        return $user->can('catalogue.view') || $user->can('inventory.view');
    }

    public function view(User $user, InventoryItem $item): bool
    {
        return ($user->can('catalogue.view') || $user->can('inventory.view')) && $this->sameTenant($user, $item);
    }

    public function create(User $user): bool
    {
        return $user->can('catalogue.create') || $user->can('inventory.create');
    }

    public function update(User $user, InventoryItem $item): bool
    {
        return ($user->can('catalogue.edit') || $user->can('inventory.edit')) && $this->sameTenant($user, $item);
    }

    public function classify(User $user, InventoryItem $item): bool
    {
        if (! $this->sameTenant($user, $item)) {
            return false;
        }

        return $user->can('inventory.classification.manage') || $this->update($user, $item);
    }

    public function delete(User $user, InventoryItem $item): bool
    {
        return ($user->can('catalogue.delete') || $user->can('inventory.delete')) && $this->sameTenant($user, $item);
    }
}
