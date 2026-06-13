<?php

namespace App\Policies;

use App\Enums\CustomerPaymentStatus;
use App\Models\Sales\CustomerPayment;
use App\Models\User;
use App\Policies\Concerns\ChecksCrmTenant;

class CustomerPaymentPolicy
{
    use ChecksCrmTenant;

    public function viewAny(User $user): bool
    {
        return $user->can('payments.view');
    }

    public function view(User $user, CustomerPayment $payment): bool
    {
        return $user->can('payments.view') && $this->sameTenant($user, $payment);
    }

    public function create(User $user): bool
    {
        return $user->can('payments.create');
    }

    public function update(User $user, CustomerPayment $payment): bool
    {
        return $user->can('payments.edit')
            && $this->sameTenant($user, $payment)
            && $payment->status->isEditable();
    }

    public function delete(User $user, CustomerPayment $payment): bool
    {
        return $user->can('payments.delete')
            && $this->sameTenant($user, $payment)
            && $payment->status === CustomerPaymentStatus::Draft;
    }

    public function post(User $user, CustomerPayment $payment): bool
    {
        return $user->can('payments.post')
            && $this->sameTenant($user, $payment)
            && $payment->status === CustomerPaymentStatus::Draft;
    }

    public function cancel(User $user, CustomerPayment $payment): bool
    {
        return $user->can('payments.cancel')
            && $this->sameTenant($user, $payment)
            && $payment->status === CustomerPaymentStatus::Draft;
    }

    public function viewReceipt(User $user, CustomerPayment $payment): bool
    {
        return $user->can('payments.receipt.view')
            && $this->sameTenant($user, $payment)
            && $payment->status === CustomerPaymentStatus::Posted;
    }

    public function downloadReceiptPdf(User $user, CustomerPayment $payment): bool
    {
        return $this->viewReceipt($user, $payment);
    }

    public function emailReceipt(User $user, CustomerPayment $payment): bool
    {
        return $user->can('payments.receipt.email')
            && $this->sameTenant($user, $payment)
            && $payment->status === CustomerPaymentStatus::Posted;
    }

    public function smsReceipt(User $user, CustomerPayment $payment): bool
    {
        return $user->can('payments.receipt.sms')
            && $this->sameTenant($user, $payment)
            && $payment->status === CustomerPaymentStatus::Posted;
    }
}
