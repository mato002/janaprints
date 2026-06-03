<?php

namespace App\Policies;

use App\Enums\InventoryDocumentStatus;
use App\Models\Inventory\StockReceipt;
use App\Models\User;
use App\Policies\Concerns\ChecksCrmTenant;

class StockReceiptPolicy
{
    use ChecksCrmTenant;

    public function viewAny(User $user): bool
    {
        return $user->can('inventory.view');
    }

    public function view(User $user, StockReceipt $receipt): bool
    {
        return $user->can('inventory.view') && $this->sameTenant($user, $receipt);
    }

    public function create(User $user): bool
    {
        return $user->can('inventory.receive');
    }

    public function update(User $user, StockReceipt $receipt): bool
    {
        return $user->can('inventory.receive')
            && $this->sameTenant($user, $receipt)
            && $receipt->status === InventoryDocumentStatus::Draft;
    }

    public function post(User $user, StockReceipt $receipt): bool
    {
        return $user->can('inventory.receive')
            && $this->sameTenant($user, $receipt)
            && $receipt->status === InventoryDocumentStatus::Draft;
    }
}
