<?php

namespace App\Policies;

use App\Enums\SupplierPaymentStatus;
use App\Models\Procurement\SupplierPayment;
use App\Models\User;
use App\Policies\Concerns\ChecksCrmTenant;

class SupplierPaymentPolicy
{
    use ChecksCrmTenant;

    public function viewAny(User $user): bool
    {
        return $user->can('payables.payments.view');
    }

    public function view(User $user, SupplierPayment $payment): bool
    {
        return $user->can('payables.payments.view') && $this->sameTenant($user, $payment);
    }

    public function create(User $user): bool
    {
        return $user->can('payables.payments.create');
    }

    public function update(User $user, SupplierPayment $payment): bool
    {
        return $user->can('payables.payments.edit')
            && $this->sameTenant($user, $payment)
            && $payment->status->isEditable();
    }

    public function delete(User $user, SupplierPayment $payment): bool
    {
        return $user->can('payables.payments.delete')
            && $this->sameTenant($user, $payment)
            && $payment->status === SupplierPaymentStatus::Draft;
    }

    public function post(User $user, SupplierPayment $payment): bool
    {
        return $user->can('payables.payments.post')
            && $this->sameTenant($user, $payment)
            && $payment->status === SupplierPaymentStatus::Draft;
    }

    public function cancel(User $user, SupplierPayment $payment): bool
    {
        return $user->can('payables.payments.cancel')
            && $this->sameTenant($user, $payment)
            && $payment->status === SupplierPaymentStatus::Draft;
    }

    public function viewPayablesLedger(User $user): bool
    {
        return $user->can('payables.ledger.view');
    }

    public function viewPayablesStatement(User $user): bool
    {
        return $user->can('payables.statement.view');
    }

    public function viewPayablesAging(User $user): bool
    {
        return $user->can('payables.aging.view');
    }
}
