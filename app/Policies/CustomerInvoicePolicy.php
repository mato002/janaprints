<?php

namespace App\Policies;

use App\Enums\CustomerInvoiceStatus;
use App\Models\Sales\CustomerInvoice;
use App\Models\User;
use App\Policies\Concerns\ChecksCrmTenant;
use App\Policies\Concerns\ChecksWorkflowAttempt;

class CustomerInvoicePolicy
{
    use ChecksCrmTenant, ChecksWorkflowAttempt;

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
        return $this->canAttemptWorkflow(
            $user,
            $invoice,
            'invoices.approve',
            fn (CustomerInvoice $record) => in_array($record->status, [
                CustomerInvoiceStatus::Approved,
                CustomerInvoiceStatus::Posted,
                CustomerInvoiceStatus::Cancelled,
            ], true),
        );
    }

    public function post(User $user, CustomerInvoice $invoice): bool
    {
        return $this->canAttemptWorkflow(
            $user,
            $invoice,
            'invoices.post',
            fn (CustomerInvoice $record) => in_array($record->status, [
                CustomerInvoiceStatus::Posted,
                CustomerInvoiceStatus::Cancelled,
            ], true),
        );
    }

    public function cancel(User $user, CustomerInvoice $invoice): bool
    {
        return $this->canAttemptWorkflow(
            $user,
            $invoice,
            'invoices.cancel',
            fn (CustomerInvoice $record) => in_array($record->status, [
                CustomerInvoiceStatus::Posted,
                CustomerInvoiceStatus::Cancelled,
            ], true),
        );
    }

    public function creditNote(User $user, CustomerInvoice $invoice): bool
    {
        return $this->canAttemptWorkflow(
            $user,
            $invoice,
            'invoices.credit_note',
            fn (CustomerInvoice $record) => $record->status !== CustomerInvoiceStatus::Posted
                || $record->invoice_type->isCredit(),
        );
    }

    public function emailInvoice(User $user, CustomerInvoice $invoice): bool
    {
        return $this->canAttemptWorkflow(
            $user,
            $invoice,
            'invoices.view',
            fn (CustomerInvoice $record) => $record->status !== CustomerInvoiceStatus::Posted,
        );
    }
}
