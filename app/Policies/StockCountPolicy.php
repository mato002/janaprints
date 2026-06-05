<?php

namespace App\Policies;

use App\Models\Inventory\StockCount;
use App\Models\User;
use App\Policies\Concerns\ChecksCrmTenant;

class StockCountPolicy
{
    use ChecksCrmTenant;

    public function viewAny(User $user): bool
    {
        return $user->can('inventory.count.view');
    }

    public function view(User $user, StockCount $stockCount): bool
    {
        return $user->can('inventory.count.view') && $this->sameTenant($user, $stockCount);
    }

    public function create(User $user): bool
    {
        return $user->can('inventory.count.create');
    }

    public function update(User $user, StockCount $stockCount): bool
    {
        return $user->can('inventory.count.edit')
            && $this->sameTenant($user, $stockCount)
            && $stockCount->status->isEditable();
    }

    public function submit(User $user, StockCount $stockCount): bool
    {
        return $user->can('inventory.count.submit')
            && $this->sameTenant($user, $stockCount)
            && $stockCount->status->canSubmit();
    }

    public function approve(User $user, StockCount $stockCount): bool
    {
        return $user->can('inventory.count.approve')
            && $this->sameTenant($user, $stockCount)
            && $stockCount->status->canApprove();
    }

    public function post(User $user, StockCount $stockCount): bool
    {
        return $user->can('inventory.count.post')
            && $this->sameTenant($user, $stockCount)
            && $stockCount->status->canPost();
    }

    public function cancel(User $user, StockCount $stockCount): bool
    {
        return $user->can('inventory.count.edit')
            && $this->sameTenant($user, $stockCount)
            && $stockCount->status->canCancel();
    }
}
