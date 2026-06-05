<?php

namespace App\Support\Governance;

use App\Enums\EscalationRuleStatus;
use App\Models\Governance\ApprovalChainStepRecord;
use App\Models\Governance\WorkflowEscalationRule;
use Illuminate\Support\Collection;

class EscalationsService
{
    /**
     * @return array<string, string>
     */
    public function workflowOptions(): array
    {
        return collect(config('escalation_registry.workflows', []))
            ->mapWithKeys(fn (array $workflow, string $key) => [$key => __($workflow['label'])])
            ->all();
    }

    /**
     * @return array<int, string>
     */
    public function waitingPeriodOptions(): array
    {
        $presets = config('escalation_registry.waiting_period_presets', []);

        return collect($presets)
            ->mapWithKeys(fn (string $label, int $hours) => [$hours => __($label)])
            ->all();
    }

    /**
     * @return array{total: int, active: int, reminder_rules: int, auto_escalate_rules: int}
     */
    public function summaryMetrics(int $companyId, ?int $branchId = null): array
    {
        $query = WorkflowEscalationRule::query()->where('company_id', $companyId);

        if ($branchId !== null) {
            $query->where(function ($scoped) use ($branchId) {
                $scoped->whereNull('branch_id')->orWhere('branch_id', $branchId);
            });
        } else {
            $query->whereNull('branch_id');
        }

        return [
            'total' => (clone $query)->count(),
            'active' => (clone $query)->where('status', EscalationRuleStatus::Active)->count(),
            'reminder_rules' => (clone $query)->where('status', EscalationRuleStatus::Active)->where('escalation_method', 'reminder')->count(),
            'auto_escalate_rules' => (clone $query)->where('status', EscalationRuleStatus::Active)->where('escalation_method', 'auto_escalate')->count(),
        ];
    }

    public function resolveRuleForStepRecord(ApprovalChainStepRecord $record): ?WorkflowEscalationRule
    {
        $record->loadMissing('run.chain');

        $run = $record->run;
        if ($run === null) {
            return null;
        }

        $workflowKey = $this->workflowKeyForRun(
            $run->approval_rule_type instanceof \App\Enums\ApprovalRuleType
                ? $run->approval_rule_type->value
                : (string) $run->approval_rule_type,
            $run->chain?->document_type,
        );

        if ($workflowKey === null) {
            return null;
        }

        return $this->resolveRule($workflowKey, $run->company_id, $run->branch_id);
    }

    public function resolveRule(string $workflowKey, int $companyId, ?int $branchId): ?WorkflowEscalationRule
    {
        if ($branchId !== null) {
            $branchRule = $this->findActiveRule($workflowKey, $companyId, $branchId);
            if ($branchRule) {
                return $branchRule;
            }
        }

        return $this->findActiveRule($workflowKey, $companyId, null);
    }

    /**
     * @return Collection<int, WorkflowEscalationRule>
     */
    public function rulesForScope(int $companyId, ?int $branchId): Collection
    {
        return WorkflowEscalationRule::query()
            ->where('company_id', $companyId)
            ->when(
                $branchId,
                fn ($query) => $query->where(function ($scoped) use ($branchId) {
                    $scoped->whereNull('branch_id')->orWhere('branch_id', $branchId);
                }),
                fn ($query) => $query->whereNull('branch_id'),
            )
            ->orderBy('workflow_key')
            ->orderBy('name')
            ->get();
    }

    protected function findActiveRule(string $workflowKey, int $companyId, ?int $branchId): ?WorkflowEscalationRule
    {
        return WorkflowEscalationRule::query()
            ->where('company_id', $companyId)
            ->where('workflow_key', $workflowKey)
            ->where('status', EscalationRuleStatus::Active)
            ->when(
                $branchId,
                fn ($query) => $query->where('branch_id', $branchId),
                fn ($query) => $query->whereNull('branch_id'),
            )
            ->first();
    }

    protected function workflowKeyForRun(string $approvalRuleType, ?string $documentType): ?string
    {
        foreach (config('escalation_registry.workflows', []) as $key => $workflow) {
            if (($workflow['approval_rule_type'] ?? null) !== $approvalRuleType) {
                continue;
            }

            if ($documentType !== null && ($workflow['document_type'] ?? null) !== $documentType) {
                continue;
            }

            return $key;
        }

        foreach (config('escalation_registry.workflows', []) as $key => $workflow) {
            if (($workflow['approval_rule_type'] ?? null) === $approvalRuleType) {
                return $key;
            }
        }

        return null;
    }
}
