<?php

namespace App\Services\PrintingIntelligence;

use App\Enums\ApprovalChainRunStatus;
use App\Enums\ApprovalRuleType;
use App\Enums\CalibrationRuleStatus;
use App\Models\PrintingIntelligence\PrintCalibrationRule;
use App\Models\PrintingIntelligence\PrintCalibrationRuleHistory;
use App\Models\User;
use App\Support\Governance\ApprovalEnforcementEngine;
use App\Support\Platform\ApprovalRulesService;
use Illuminate\Validation\ValidationException;

class CalibrationApprovalService
{
    public function __construct(
        protected ApprovalEnforcementEngine $engine,
        protected ApprovalRulesService $rules,
        protected CostFormulaVersionService $formulaVersions,
        protected CalibrationImpactSimulationService $simulation,
    ) {}

    public function submitForReview(PrintCalibrationRule $rule, User $user): PrintCalibrationRule
    {
        if (! in_array($rule->status, [CalibrationRuleStatus::Draft, CalibrationRuleStatus::Rejected], true)) {
            throw ValidationException::withMessages([
                'status' => __('Only draft or rejected calibration rules can be submitted for review.'),
            ]);
        }

        $simulation = $this->simulation->simulate($rule);

        $rule->update([
            'status' => CalibrationRuleStatus::PendingReview,
            'reviewed_by' => $user->id,
            'metadata' => array_merge($rule->metadata ?? [], [
                'impact_simulation' => $simulation,
                'submitted_at' => now()->toIso8601String(),
            ]),
        ]);

        $this->engine->beginApproval(
            $rule->fresh(),
            ApprovalRuleType::CalibrationRuleApproval,
            ['percent' => abs((float) ($rule->variance_trigger_percent ?? 0))],
        );

        return $rule->fresh();
    }

    public function approve(PrintCalibrationRule $rule, User $user, ?string $notes = null): PrintCalibrationRule
    {
        if ($rule->status !== CalibrationRuleStatus::PendingReview) {
            throw ValidationException::withMessages([
                'status' => __('Only pending review calibration rules can be approved.'),
            ]);
        }

        if (! $user->can('printing.calibration.approve')) {
            throw ValidationException::withMessages([
                'approval' => __('You are not authorized to approve calibration rules.'),
            ]);
        }

        if ((int) $rule->reviewed_by === (int) $user->id) {
            throw ValidationException::withMessages([
                'approval' => __('You cannot approve a calibration rule you submitted for review.'),
            ]);
        }

        $run = $this->engine->latestRun($rule);
        if ($run !== null && $run->status === ApprovalChainRunStatus::Pending) {
            $this->engine->recordApproval($rule, $user, $notes);
        }

        $this->retirePreviousApproved($rule);

        $version = $rule->rule_version ?: $this->formulaVersions->nextVersion($rule->rule_type, $rule->company_id);
        $effectiveFrom = now();

        PrintCalibrationRuleHistory::query()->create([
            'print_calibration_rule_id' => $rule->id,
            'company_id' => $rule->company_id,
            'before_value' => $rule->current_value,
            'after_value' => $rule->proposed_value,
            'rule_version' => $version,
            'approved_by' => $user->id,
            'approved_at' => now(),
            'effective_from' => $effectiveFrom,
            'reason' => $rule->reason,
            'metadata' => [
                'approval_notes' => $notes,
                'impact_simulation' => $rule->metadata['impact_simulation'] ?? null,
            ],
            'recorded_at' => now(),
        ]);

        $rule->update([
            'status' => CalibrationRuleStatus::Approved,
            'approved_by' => $user->id,
            'approved_at' => now(),
            'effective_from' => $effectiveFrom,
            'rule_version' => $version,
            'current_value' => $rule->proposed_value,
        ]);

        return $rule->fresh();
    }

    public function reject(PrintCalibrationRule $rule, User $user, ?string $notes = null): PrintCalibrationRule
    {
        if ($rule->status !== CalibrationRuleStatus::PendingReview) {
            throw ValidationException::withMessages([
                'status' => __('Only pending review calibration rules can be rejected.'),
            ]);
        }

        if (! $user->can('printing.calibration.review') && ! $user->can('printing.calibration.approve')) {
            throw ValidationException::withMessages([
                'approval' => __('You are not authorized to reject calibration rules.'),
            ]);
        }

        $run = $this->engine->latestRun($rule);
        if ($run !== null && $run->status === ApprovalChainRunStatus::Pending) {
            $this->engine->recordRejection($rule, $user, $notes);
        }

        $rule->update([
            'status' => CalibrationRuleStatus::Rejected,
            'reviewed_by' => $user->id,
            'metadata' => array_merge($rule->metadata ?? [], [
                'rejection_notes' => $notes,
                'rejected_at' => now()->toIso8601String(),
            ]),
        ]);

        return $rule->fresh();
    }

    protected function retirePreviousApproved(PrintCalibrationRule $rule): void
    {
        PrintCalibrationRule::query()
            ->where('company_id', $rule->company_id)
            ->where('rule_key', $rule->rule_key)
            ->where('status', CalibrationRuleStatus::Approved)
            ->whereKeyNot($rule->id)
            ->update(['status' => CalibrationRuleStatus::Retired]);
    }
}
