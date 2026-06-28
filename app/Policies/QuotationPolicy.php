<?php

namespace App\Policies;

use App\Enums\ApprovalRuleType;
use App\Enums\DocumentModule;
use App\Enums\QuotationStatus;
use App\Models\Sales\Quotation;
use App\Models\User;
use App\Policies\Concerns\ChecksApprovalDelegation;
use App\Policies\Concerns\ChecksCrmTenant;
use App\Policies\Concerns\ChecksWorkflowAttempt;

class QuotationPolicy
{
    use ChecksApprovalDelegation, ChecksCrmTenant, ChecksWorkflowAttempt;

    public function viewAny(User $user): bool
    {
        return $user->can('quotations.view');
    }

    public function view(User $user, Quotation $quotation): bool
    {
        return $user->can('quotations.view') && $this->sameTenant($user, $quotation);
    }

    public function create(User $user): bool
    {
        return $user->can('quotations.create');
    }

    public function update(User $user, Quotation $quotation): bool
    {
        return $user->can('quotations.edit')
            && $this->sameTenant($user, $quotation)
            && $quotation->status->isEditable();
    }

    public function delete(User $user, Quotation $quotation): bool
    {
        return $user->can('quotations.delete')
            && $this->sameTenant($user, $quotation)
            && $quotation->status === QuotationStatus::Draft
            && ! $quotation->salesOrder()->exists();
    }

    public function approve(User $user, Quotation $quotation): bool
    {
        if (! $this->sameTenant($user, $quotation) || $quotation->status !== QuotationStatus::PendingApproval) {
            return false;
        }

        if ($user->can('quotations.approve')) {
            return true;
        }

        return $this->canApproveViaDelegation(
            $user,
            ApprovalRuleType::QuotationApproval,
            DocumentModule::Commercial,
            $quotation->company_id,
            $quotation->branch_id,
            'quotations.approve',
        );
    }

    public function send(User $user, Quotation $quotation): bool
    {
        return $user->can('quotations.send')
            && $this->sameTenant($user, $quotation)
            && in_array($quotation->status, [QuotationStatus::PendingApproval, QuotationStatus::Approved], true);
    }

    public function convert(User $user, Quotation $quotation): bool
    {
        return $this->canAttemptWorkflow(
            $user,
            $quotation,
            'quotations.convert',
            fn (Quotation $record) => $record->status !== QuotationStatus::Accepted,
        );
    }

    public function linkArtwork(User $user, Quotation $quotation): bool
    {
        if (! $user->can('quotations.edit') || ! $this->sameTenant($user, $quotation) || ! $quotation->customer_id) {
            return false;
        }

        return ! in_array($quotation->status, [
            QuotationStatus::Converted,
            QuotationStatus::Rejected,
            QuotationStatus::Expired,
        ], true);
    }

    public function transition(User $user, Quotation $quotation): bool
    {
        return $user->can('quotations.edit') && $this->sameTenant($user, $quotation);
    }
}
