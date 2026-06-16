<?php

namespace App\Support\Hr;

use App\Enums\ApprovalChainRunStatus;
use App\Enums\ApprovalRuleType;
use App\Enums\PayrollRunStatus;
use App\Enums\WorkflowRuleTrigger;
use App\Models\Hr\PayrollRun;
use App\Models\User;
use App\Support\Governance\ApprovalEnforcementEngine;
use App\Support\Governance\WorkflowRulesService;
use Illuminate\Validation\ValidationException;

class PayrollApprovalWorkflowService
{
    public function __construct(
        protected ApprovalEnforcementEngine $approvalEngine,
        protected WorkflowRulesService $workflowRules,
        protected PayrollReviewService $review,
        protected PayrollAuditService $audit,
    ) {}

    public function submitForApproval(PayrollRun $run, User $user): PayrollRun
    {
        $this->review->assertCanSubmitForApproval($run);

        $review = $this->review->review($run);

        $run->update([
            'status' => PayrollRunStatus::PendingApproval,
            'submitted_for_approval_by_user_id' => $user->id,
            'submitted_for_approval_at' => now(),
            'review_snapshot' => $review,
            'has_critical_review_issues' => ! $review['can_submit_for_approval'],
        ]);

        $this->approvalEngine->beginApproval(
            $run,
            ApprovalRuleType::PayrollApproval,
            ['amount' => (float) $run->net_total],
            (int) $run->company_id,
            $run->branch_id,
        );

        $this->workflowRules->dispatch(WorkflowRuleTrigger::Completed, $run->fresh(), $user);
        $this->audit->logSubmittedForApproval($run->fresh(), $user);

        return $run->fresh();
    }

    public function approve(PayrollRun $run, User $user): PayrollRun
    {
        if (! $run->status->canApprove()) {
            throw ValidationException::withMessages([
                'status' => __('Only payroll runs pending approval can be approved.'),
            ]);
        }

        $chainRun = $this->approvalEngine->latestRun($run);

        if ($chainRun && $chainRun->status === ApprovalChainRunStatus::Pending) {
            $this->approvalEngine->recordApproval($run, $user);

            if (! $this->approvalEngine->hasApprovedChain($run)) {
                return $run->fresh();
            }
        }

        return $this->finalizeApproval($run, $user);
    }

    public function reject(PayrollRun $run, User $user, ?string $reason = null): PayrollRun
    {
        if ($run->status !== PayrollRunStatus::PendingApproval) {
            throw ValidationException::withMessages([
                'status' => __('Only payroll runs pending approval can be rejected.'),
            ]);
        }

        $chainRun = $this->approvalEngine->latestRun($run);

        if ($chainRun && $chainRun->status === ApprovalChainRunStatus::Pending) {
            $this->approvalEngine->recordRejection($run, $user, $reason);
        }

        $run->update([
            'status' => PayrollRunStatus::UnderReview,
            'approved_by_user_id' => null,
            'approved_at' => null,
        ]);

        $this->workflowRules->dispatch(WorkflowRuleTrigger::Rejected, $run->fresh(), $user);
        $this->audit->logRejected($run->fresh(), $user, $reason);

        return $run->fresh();
    }

    protected function finalizeApproval(PayrollRun $run, User $user): PayrollRun
    {
        $run->update([
            'status' => PayrollRunStatus::Approved,
            'approved_by_user_id' => $user->id,
            'approved_at' => now(),
        ]);

        $run = $run->fresh();

        $this->workflowRules->dispatch(WorkflowRuleTrigger::Approved, $run, $user);
        $this->audit->logApproved($run, $user);

        return $run;
    }
}
