<?php

namespace App\Support\Governance;

use App\Enums\ApprovalChainMode;
use App\Enums\ApprovalChainRunStatus;
use App\Enums\ApprovalChainStatus;
use App\Enums\ApprovalChainStepStatus;
use App\Enums\ApprovalRuleType;
use App\Models\Governance\ApprovalChain;
use App\Models\Governance\ApprovalChainRun;
use App\Models\Governance\ApprovalChainStep;
use App\Models\Governance\ApprovalChainStepRecord;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class ApprovalChainsService
{
    public function __construct(
        protected ApprovalChainEngine $engine,
    ) {}

    /**
     * @param  array{amount?: float|null, percent?: float|null}  $context
     */
    public function resolveChain(
        ApprovalRuleType $ruleType,
        ?int $companyId = null,
        ?int $branchId = null,
        array $context = [],
    ): ?ApprovalChain {
        $companyId ??= tenant()->companyId();
        $branchId ??= tenant()->branchId();

        if ($branchId !== null) {
            $branchChain = $this->findMatchingChain($ruleType, $companyId, $branchId, $context);
            if ($branchChain) {
                return $branchChain;
            }
        }

        return $this->findMatchingChain($ruleType, $companyId, null, $context);
    }

    /**
     * @param  array{amount?: float|null, percent?: float|null}  $context
     * @return Collection<int, ApprovalChainStep>
     */
    public function applicableSteps(ApprovalChain $chain, array $context = []): Collection
    {
        if ($chain->approval_mode === ApprovalChainMode::Conditional) {
            return $chain->steps->filter(
                fn (ApprovalChainStep $step) => $this->engine->matchesCondition($step->condition_json, $context),
            )->values();
        }

        return $chain->steps;
    }

    /**
     * @param  array{amount?: float|null, percent?: float|null, reference?: string|null}  $context
     */
    public function startRun(
        ApprovalChain $chain,
        Model $subject,
        array $context = [],
        ?int $companyId = null,
        ?int $branchId = null,
    ): ApprovalChainRun {
        $companyId ??= $subject->getAttribute('company_id') ?? tenant()->companyId();
        $branchId ??= $subject->getAttribute('branch_id') ?? tenant()->branchId();

        $run = ApprovalChainRun::query()->create([
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'approval_chain_id' => $chain->id,
            'approval_rule_type' => $chain->approval_rule_type,
            'subject_type' => $subject->getMorphClass(),
            'subject_id' => $subject->getKey(),
            'status' => ApprovalChainRunStatus::Pending,
            'context_json' => $context,
            'started_at' => now(),
        ]);

        $this->engine->initializeStepRecords($run, $this->applicableSteps($chain, $context));

        return $run->load('stepRecords.step');
    }

    /**
     * @return array{total: int, active: int, pending_runs: int, approved_runs: int, rejected_runs: int}
     */
    public function summaryMetrics(int $companyId, ?int $branchId = null): array
    {
        $chainQuery = ApprovalChain::query()->where('company_id', $companyId);
        $runQuery = ApprovalChainRun::query()->where('company_id', $companyId);

        if ($branchId !== null) {
            $chainQuery->where('branch_id', $branchId);
            $runQuery->where('branch_id', $branchId);
        }

        return [
            'total' => (clone $chainQuery)->count(),
            'active' => (clone $chainQuery)->where('status', ApprovalChainStatus::Active)->count(),
            'pending_runs' => (clone $runQuery)->where('status', ApprovalChainRunStatus::Pending)->count(),
            'approved_runs' => (clone $runQuery)->where('status', ApprovalChainRunStatus::Approved)->count(),
            'rejected_runs' => (clone $runQuery)->where('status', ApprovalChainRunStatus::Rejected)->count(),
        ];
    }

    /**
     * @return Collection<int, ApprovalChainRun>
     */
    public function recentRuns(int $companyId, ?int $branchId = null, int $limit = 15): Collection
    {
        return ApprovalChainRun::query()
            ->where('company_id', $companyId)
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->with(['chain', 'stepRecords'])
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function recordStepAction(
        ApprovalChainStepRecord $record,
        ApprovalChainStepStatus $status,
        User $actor,
        ?string $notes = null,
    ): ApprovalChainRun {
        $record->update([
            'status' => $status,
            'acted_by_user_id' => $actor->id,
            'acted_at' => now(),
            'notes' => $notes,
        ]);

        $run = $record->run()->with(['chain', 'stepRecords'])->firstOrFail();

        return $this->engine->refreshRunStatus($run);
    }

    /**
     * @param  array{amount?: float|null, percent?: float|null}  $context
     */
    protected function findMatchingChain(
        ApprovalRuleType $ruleType,
        int $companyId,
        ?int $branchId,
        array $context,
    ): ?ApprovalChain {
        return ApprovalChain::query()
            ->where('company_id', $companyId)
            ->where('approval_rule_type', $ruleType)
            ->where('status', ApprovalChainStatus::Active)
            ->when(
                $branchId,
                fn ($query) => $query->where('branch_id', $branchId),
                fn ($query) => $query->whereNull('branch_id'),
            )
            ->with('steps.approverUser')
            ->orderBy('id')
            ->get()
            ->first(fn (ApprovalChain $chain) => $this->engine->matchesCondition($chain->condition_json, $context));
    }
}
