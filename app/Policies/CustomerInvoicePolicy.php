<?php

namespace App\Policies;

use App\Enums\CustomerInvoiceStatus;
use App\Models\Sales\CustomerInvoice;
use App\Models\User;
use App\Policies\Concerns\ChecksCrmTenant;

class CustomerInvoicePolicy
{
    use ChecksCrmTenant;

    public function viewAny(User $user): bool
    {
        return $user->can('invoices.view');
    }

    public function view(User $user, CustomerInvoice $invoice): bool
    {
        return $user->can('invoices.view') && $this->sameTenant($user, $invoice);
    }

    public function create(User $user): bool
    {
        return $user->can('invoices.create');
    }

    public function update(User $user, CustomerInvoice $invoice): bool
    {
        return $user->can('invoices.edit')
            && $this->sameTenant($user, $invoice)
            && $invoice->status->isEditable();
    }

    public function delete(User $user, CustomerInvoice $invoice): bool
    {
        return $user->can('invoices.delete')
            && $this->sameTenant($user, $invoice)
            && $invoice->status === CustomerInvoiceStatus::Draft;
    }

    public function approve(User $user, CustomerInvoice $invoice): bool
    {
        return $user->can('invoices.approve')
            && $this->sameTenant($user, $invoice)
            && $invoice->status === CustomerInvoiceStatus::Draft;
    }

    public function post(User $user, CustomerInvoice $invoice): bool
    {
        return $user->can('invoices.post')
            && $this->sameTenant($user, $invoice)
            && $invoice->status === CustomerInvoiceStatus::Approved;
    }

    public function cancel(User $user, CustomerInvoice $invoice): bool
    {
        return $user->can('invoices.cancel')
            && $this->sameTenant($user, $invoice)
            && in_array($invoice->status, [CustomerInvoiceStatus::Draft, CustomerInvoiceStatus::Approved], true);
    }

    public function creditNote(User $user, CustomerInvoice $invoice): bool
    {
        return $user->can('invoices.credit_note')
            && $this->sameTenant($user, $invoice)
            && $invoice->status === CustomerInvoiceStatus::Posted
            && ! $invoice->invoice_type->isCredit();
    }

    public function emailInvoice(User $user, CustomerInvoice $invoice): bool
    {
        return $user->can('invoices.view')
            && $this->sameTenant($user, $invoice)
            && $invoice->status === CustomerInvoiceStatus::Posted;
    }
}
