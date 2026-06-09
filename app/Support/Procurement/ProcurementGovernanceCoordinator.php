<?php

namespace App\Support\Procurement;

use App\Enums\ApprovalRuleType;
use App\Models\User;
use App\Support\ActivityLogger;
use App\Support\Governance\ApprovalEnforcementEngine;
use App\Support\Platform\ApprovalRulesService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class ProcurementGovernanceCoordinator
{
    public function __construct(
        protected ApprovalEnforcementEngine $engine,
        protected ApprovalRulesService $rules,
        protected ProcurementSegregationOfDuties $segregation,
    ) {}

    public function requiresApproval(
        ApprovalRuleType $ruleType,
        float $amount,
        int $companyId,
        ?int $branchId,
    ): bool {
        return $this->rules->requiresApproval($ruleType, $amount, null, $companyId, $branchId);
    }

    /**
     * @return bool True when auto-approved (no chain required).
     */
    public function submit(
        Model $subject,
        ApprovalRuleType $ruleType,
        float $amount,
        int $submitterId,
        string $activityAction,
    ): bool {
        if (! $this->requiresApproval($ruleType, $amount, (int) $subject->getAttribute('company_id'), $subject->getAttribute('branch_id'))) {
            ActivityLogger::log($activityAction, $subject, $submitterId, [
                'requires_approval' => false,
                'rule_type' => $ruleType->value,
            ]);

            return true;
        }

        $this->engine->beginApproval($subject, $ruleType, ['amount' => $amount]);
        ActivityLogger::log($activityAction, $subject, $submitterId, [
            'requires_approval' => true,
            'rule_type' => $ruleType->value,
        ]);

        return false;
    }

    public function approve(
        Model $subject,
        ApprovalRuleType $ruleType,
        User $actor,
        ?string $notes,
        string $approvePermission,
        ?int $submitterUserId,
        string $approvedActivity,
        string $stepActivity,
    ): bool {
        $this->segregation->assertCanApprove($subject, $actor, $ruleType, $submitterUserId);

        $run = $this->engine->latestRun($subject);

        if ($run !== null) {
            $this->engine->recordApproval($subject, $actor, $notes);
        } elseif (! $actor->can($approvePermission)) {
            throw ValidationException::withMessages([
                'approval' => __('You are not authorized to approve this document.'),
            ]);
        }

        if ($this->engine->hasApprovedChain($subject->fresh())) {
            ActivityLogger::log($approvedActivity, $subject, $actor->id, ['notes' => $notes]);

            return true;
        }

        ActivityLogger::log($stepActivity, $subject, $actor->id, ['notes' => $notes]);

        return false;
    }

    public function reject(
        Model $subject,
        ApprovalRuleType $ruleType,
        User $actor,
        string $reason,
        string $approvePermission,
        ?int $submitterUserId,
        string $rejectedActivity,
    ): void {
        $this->segregation->assertCanApprove($subject, $actor, $ruleType, $submitterUserId);

        $run = $this->engine->latestRun($subject);

        if ($run !== null) {
            $this->engine->recordRejection($subject, $actor, $reason);
        } elseif (! $actor->can($approvePermission)) {
            throw ValidationException::withMessages([
                'approval' => __('You are not authorized to reject this document.'),
            ]);
        }

        ActivityLogger::log($rejectedActivity, $subject, $actor->id, ['reason' => $reason]);
    }

    public function assertChainApproved(Model $subject, ?string $message = null): void
    {
        if (! $this->engine->hasApprovedChain($subject)) {
            throw ValidationException::withMessages([
                'approval' => $message ?? __('Approval chain must be completed before proceeding.'),
            ]);
        }
    }

    public function assertChainApprovedForPosting(
        Model $subject,
        ApprovalRuleType $ruleType,
        float $amount,
        ?string $message = null,
    ): void {
        try {
            $this->engine->assertChainApprovedForPosting(
                $subject,
                $ruleType,
                ['amount' => $amount],
            );
        } catch (ValidationException $exception) {
            if ($message !== null && isset($exception->errors()['approval'])) {
                throw ValidationException::withMessages(['approval' => $message]);
            }

            throw $exception;
        }
    }

    public function engine(): ApprovalEnforcementEngine
    {
        return $this->engine;
    }
}
