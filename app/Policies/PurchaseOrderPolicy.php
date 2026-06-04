<?php

namespace App\Policies;

use App\Enums\PurchaseOrderStatus;
use App\Models\Procurement\PurchaseOrder;
use App\Models\User;
use App\Policies\Concerns\ChecksCrmTenant;

class PurchaseOrderPolicy
{
    use ChecksCrmTenant;

    public function viewAny(User $user): bool
    {
        return $user->can('procurement.orders.view');
    }

    public function view(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->can('procurement.orders.view') && $this->sameTenant($user, $purchaseOrder);
    }

    public function create(User $user): bool
    {
        return $user->can('procurement.orders.create');
    }

    public function update(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->can('procurement.orders.edit')
            && $this->sameTenant($user, $purchaseOrder)
            && $purchaseOrder->status->isEditable();
    }

    public function delete(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->can('procurement.orders.delete')
            && $this->sameTenant($user, $purchaseOrder)
            && $purchaseOrder->status === PurchaseOrderStatus::Draft;
    }

    public function submit(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->can('procurement.orders.edit')
            && $this->sameTenant($user, $purchaseOrder)
            && $purchaseOrder->status === PurchaseOrderStatus::Draft;
    }

    public function approve(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->can('procurement.orders.approve')
            && $this->sameTenant($user, $purchaseOrder)
            && $purchaseOrder->status === PurchaseOrderStatus::PendingApproval;
    }

    public function reject(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->can('procurement.orders.approve')
            && $this->sameTenant($user, $purchaseOrder)
            && $purchaseOrder->status === PurchaseOrderStatus::PendingApproval;
    }

    public function send(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->can('procurement.orders.edit')
            && $this->sameTenant($user, $purchaseOrder)
            && $purchaseOrder->status === PurchaseOrderStatus::Approved;
    }

    public function receive(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->can('procurement.orders.receive')
            && $this->sameTenant($user, $purchaseOrder)
            && $purchaseOrder->status->canReceive();
    }

    public function cancel(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->can('procurement.orders.edit')
            && $this->sameTenant($user, $purchaseOrder)
            && in_array($purchaseOrder->status, [
                PurchaseOrderStatus::Draft,
                PurchaseOrderStatus::PendingApproval,
                PurchaseOrderStatus::Approved,
            ], true);
    }
}
