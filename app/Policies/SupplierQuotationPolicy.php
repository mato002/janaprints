<?php

namespace App\Policies;

use App\Models\Procurement\SupplierQuotation;
use App\Models\User;
use App\Policies\Concerns\ChecksCrmTenant;

class SupplierQuotationPolicy
{
    use ChecksCrmTenant;

    public function viewAny(User $user): bool
    {
        return $user->can('procurement.orders.view');
    }

    public function view(User $user, SupplierQuotation $supplierQuotation): bool
    {
        return $user->can('procurement.orders.view') && $this->sameTenant($user, $supplierQuotation);
    }

    public function create(User $user): bool
    {
        return $user->can('procurement.orders.create');
    }

    public function update(User $user, SupplierQuotation $supplierQuotation): bool
    {
        return $user->can('procurement.orders.edit')
            && $this->sameTenant($user, $supplierQuotation)
            && $supplierQuotation->status->isEditable();
    }

    public function delete(User $user, SupplierQuotation $supplierQuotation): bool
    {
        return $user->can('procurement.orders.delete')
            && $this->sameTenant($user, $supplierQuotation)
            && $supplierQuotation->status->isEditable();
    }
}
