<?php

namespace App\Policies;

use App\Enums\PurchaseRequestStatus;
use App\Models\Procurement\PurchaseRequest;
use App\Models\User;
use App\Policies\Concerns\ChecksCrmTenant;

class PurchaseRequestPolicy
{
    use ChecksCrmTenant;

    public function viewAny(User $user): bool
    {
        return $user->can('procurement.requests.view');
    }

    public function view(User $user, PurchaseRequest $purchaseRequest): bool
    {
        return $user->can('procurement.requests.view') && $this->sameTenant($user, $purchaseRequest);
    }

    public function create(User $user): bool
    {
        return $user->can('procurement.requests.create');
    }

    public function update(User $user, PurchaseRequest $purchaseRequest): bool
    {
        return $user->can('procurement.requests.edit')
            && $this->sameTenant($user, $purchaseRequest)
            && $purchaseRequest->status->isEditable();
    }

    public function delete(User $user, PurchaseRequest $purchaseRequest): bool
    {
        return $user->can('procurement.requests.delete')
            && $this->sameTenant($user, $purchaseRequest)
            && $purchaseRequest->status === PurchaseRequestStatus::Draft;
    }

    public function submit(User $user, PurchaseRequest $purchaseRequest): bool
    {
        return $user->can('procurement.requests.edit')
            && $this->sameTenant($user, $purchaseRequest)
            && $purchaseRequest->status->canSubmit();
    }

    public function approve(User $user, PurchaseRequest $purchaseRequest): bool
    {
        return $user->can('procurement.requests.approve')
            && $this->sameTenant($user, $purchaseRequest)
            && $purchaseRequest->status->canApprove();
    }

    public function convert(User $user, PurchaseRequest $purchaseRequest): bool
    {
        return $user->can('procurement.orders.create')
            && $this->sameTenant($user, $purchaseRequest)
            && $purchaseRequest->status->canConvert();
    }
}
