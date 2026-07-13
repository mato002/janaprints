<?php

namespace App\Support\Procurement;

use App\Enums\ApprovalChainRunStatus;
use App\Enums\ApprovalRuleType;
use App\Enums\PurchaseOrderStatus;
use App\Enums\PurchaseRequestStatus;
use App\Models\Governance\ApprovalChainRun;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\PurchaseRequest;
use App\Models\User;
use App\Support\Governance\ApprovalChainEngine;
use App\Support\Governance\ApprovalEnforcementEngine;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class ProcurementApprovalActionService
{
    public function __construct(
        protected ApprovalEnforcementEngine $engine,
        protected ApprovalChainEngine $chainEngine,
    ) {}

    public function approve(ApprovalChainRun $run, User $actor, ?string $notes = null): void
    {
        $this->assertPending($run);

        $subject = $run->subject;

        if ($subject === null) {
            throw ValidationException::withMessages([
                'approval' => __('Approval subject could not be resolved.'),
            ]);
        }

        $this->engine->recordApproval($subject, $actor, $notes);

        if ($this->engine->hasApprovedChain($subject->fresh())) {
            $this->finalizeApproval($subject, $actor);
        }
    }

    public function reject(ApprovalChainRun $run, User $actor, string $reason): void
    {
        $this->assertPending($run);

        $subject = $run->subject;

        if ($subject === null) {
            throw ValidationException::withMessages([
                'approval' => __('Approval subject could not be resolved.'),
            ]);
        }

        $this->engine->recordRejection($subject, $actor, $reason);
        $this->finalizeRejection($subject, $actor, $reason);
    }

    public function userCanAct(ApprovalChainRun $run, User $user): bool
    {
        if ($run->status !== ApprovalChainRunStatus::Pending || $run->subject === null) {
            return false;
        }

        $permission = $this->resolveApprovePermission($run);

        foreach ($this->chainEngine->actionableStepRecords($run) as $record) {
            if ($this->chainEngine->canUserActOnStep($user, $record)) {
                return true;
            }
        }

        return $permission !== null && $user->can($permission);
    }

    public function resolveApprovePermission(ApprovalChainRun $run): ?string
    {
        return match ($run->approval_rule_type) {
            ApprovalRuleType::PurchaseRequestApproval => 'procurement.requests.approve',
            ApprovalRuleType::ProcurementApproval => 'procurement.orders.approve',
            ApprovalRuleType::RfqApproval => 'procurement.rfq.edit',
            ApprovalRuleType::GoodsReceiptApproval => 'procurement.orders.receive',
            ApprovalRuleType::VendorInvoiceApproval => 'payables.bills.approve',
            ApprovalRuleType::PaymentApproval => 'payables.payments.post',
            ApprovalRuleType::AssetCapitalizationApproval => 'assets.capitalize.approve',
            default => null,
        };
    }

    protected function assertPending(ApprovalChainRun $run): void
    {
        if ($run->status !== ApprovalChainRunStatus::Pending) {
            throw ValidationException::withMessages([
                'approval' => __('This approval chain is no longer pending.'),
            ]);
        }
    }

    protected function finalizeApproval(Model $subject, User $actor): void
    {
        match (true) {
            $subject instanceof PurchaseRequest => $subject->update(['status' => PurchaseRequestStatus::Approved]),
            $subject instanceof PurchaseOrder => $subject->update([
                'status' => PurchaseOrderStatus::Approved,
                'approved_by' => $actor->id,
                'approved_at' => now(),
            ]),
            default => null,
        };
    }

    protected function finalizeRejection(Model $subject, User $actor, string $reason): void
    {
        match (true) {
            $subject instanceof PurchaseRequest => $subject->update(['status' => PurchaseRequestStatus::Closed]),
            $subject instanceof PurchaseOrder => $subject->update(['status' => PurchaseOrderStatus::Rejected]),
            default => null,
        };

        \App\Support\ActivityLogger::log('procurement_approval_rejected', $subject, $actor->id, ['reason' => $reason]);
    }
}
