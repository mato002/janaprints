<?php

namespace App\Policies;

use App\Enums\SupplierBillStatus;
use App\Models\Procurement\SupplierBill;
use App\Models\User;
use App\Policies\Concerns\ChecksCrmTenant;

class SupplierBillPolicy
{
    use ChecksCrmTenant;

    public function viewAny(User $user): bool
    {
        return $user->can('payables.bills.view');
    }

    public function view(User $user, SupplierBill $bill): bool
    {
        return $user->can('payables.bills.view') && $this->sameTenant($user, $bill);
    }

    public function create(User $user): bool
    {
        return $user->can('payables.bills.create');
    }

    public function update(User $user, SupplierBill $bill): bool
    {
        return $user->can('payables.bills.edit')
            && $this->sameTenant($user, $bill)
            && $bill->status->isEditable();
    }

    public function delete(User $user, SupplierBill $bill): bool
    {
        return $user->can('payables.bills.delete')
            && $this->sameTenant($user, $bill)
            && $bill->status === SupplierBillStatus::Draft;
    }

    public function approve(User $user, SupplierBill $bill): bool
    {
        return $user->can('payables.bills.approve')
            && $this->sameTenant($user, $bill)
            && $bill->status === SupplierBillStatus::Draft;
    }

    public function post(User $user, SupplierBill $bill): bool
    {
        return $user->can('payables.bills.post')
            && $this->sameTenant($user, $bill)
            && $bill->status === SupplierBillStatus::Approved;
    }

    public function cancel(User $user, SupplierBill $bill): bool
    {
        return $user->can('payables.bills.cancel')
            && $this->sameTenant($user, $bill)
            && in_array($bill->status, [SupplierBillStatus::Draft, SupplierBillStatus::Approved], true);
    }

    public function creditNote(User $user, SupplierBill $bill): bool
    {
        return $user->can('payables.bills.credit_note')
            && $this->sameTenant($user, $bill)
            && in_array($bill->status, [SupplierBillStatus::Posted, SupplierBillStatus::Paid], true)
            && ! $bill->bill_type->isCredit();
    }
}
