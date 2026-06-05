<?php

namespace App\Policies;

use App\Enums\ApprovalRuleType;
use App\Enums\DocumentModule;
use App\Enums\QuotationStatus;
use App\Models\Sales\Quotation;
use App\Models\User;
use App\Policies\Concerns\ChecksApprovalDelegation;
use App\Policies\Concerns\ChecksCrmTenant;

class QuotationPolicy
{
    use ChecksApprovalDelegation, ChecksCrmTenant;

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
            && $quotation->status === QuotationStatus::PendingApproval;
    }

    public function convert(User $user, Quotation $quotation): bool
    {
        return $user->can('quotations.convert')
            && $this->sameTenant($user, $quotation)
            && $quotation->status === QuotationStatus::Accepted;
    }

    public function transition(User $user, Quotation $quotation): bool
    {
        return $user->can('quotations.edit') && $this->sameTenant($user, $quotation);
    }
}
