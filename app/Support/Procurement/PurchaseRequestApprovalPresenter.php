<?php

namespace App\Support\Procurement;

use App\Enums\ApprovalChainRunStatus;
use App\Enums\ApprovalRuleType;
use App\Enums\PurchaseRequestStatus;
use App\Models\Governance\ApprovalChainStepRecord;
use App\Models\Procurement\PurchaseRequest;
use App\Support\Governance\ApprovalChainEngine;
use App\Support\Governance\ApprovalEnforcementEngine;

class PurchaseRequestApprovalPresenter
{
    public function __construct(
        protected ApprovalEnforcementEngine $engine,
        protected ApprovalChainEngine $chainEngine,
    ) {}

    /**
     * @return array{
     *     requires_approval: bool,
     *     rule_type: string|null,
     *     run_status: string|null,
     *     chain_complete: bool,
     *     current_step: array<string, mixed>|null,
     *     history: list<array<string, mixed>>,
     * }
     */
    public function present(PurchaseRequest $request): array
    {
        $run = $this->engine->latestRun($request);
        $ruleType = $run?->approval_rule_type ?? ApprovalRuleType::PurchaseRequestApproval;

        $currentStep = null;
        if ($run && $run->status === ApprovalChainRunStatus::Pending) {
            $actionable = $this->chainEngine->actionableStepRecords($run)->first();
            if ($actionable) {
                $currentStep = $this->mapStepRecord($actionable);
            }
        }

        $history = $run
            ? $run->stepRecords()
                ->with(['step', 'actor'])
                ->orderBy('step_number')
                ->get()
                ->map(fn (ApprovalChainStepRecord $record) => $this->mapStepRecord($record))
                ->all()
            : [];

        return [
            'requires_approval' => PurchaseRequestService::requiresApproval($request)
                || in_array($request->status, [PurchaseRequestStatus::PendingApproval, PurchaseRequestStatus::Approved], true),
            'rule_type' => $ruleType->value,
            'run_status' => $run?->status->value,
            'chain_complete' => $this->engine->hasApprovedChain($request),
            'current_step' => $currentStep,
            'history' => $history,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function mapStepRecord(ApprovalChainStepRecord $record): array
    {
        return [
            'step_number' => $record->step_number,
            'approver' => $record->step?->approverLabel() ?? __('Unassigned'),
            'status' => $record->status->value,
            'status_label' => str($record->status->value)->headline(),
            'acted_by' => $record->actor?->name,
            'acted_at' => $record->acted_at,
            'notes' => $record->notes,
        ];
    }
}
