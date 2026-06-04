<?php

namespace App\Policies;

use App\Enums\GoodsReceiptStatus;
use App\Models\Procurement\GoodsReceipt;
use App\Models\User;
use App\Policies\Concerns\ChecksCrmTenant;

class GoodsReceiptPolicy
{
    use ChecksCrmTenant;

    public function viewAny(User $user): bool
    {
        return $user->can('procurement.orders.view');
    }

    public function view(User $user, GoodsReceipt $goodsReceipt): bool
    {
        return $user->can('procurement.orders.view') && $this->sameTenant($user, $goodsReceipt);
    }

    public function create(User $user): bool
    {
        return $user->can('procurement.orders.receive');
    }

    public function post(User $user, GoodsReceipt $goodsReceipt): bool
    {
        return $user->can('procurement.orders.receive')
            && $this->sameTenant($user, $goodsReceipt)
            && $goodsReceipt->status === GoodsReceiptStatus::Draft;
    }
}
