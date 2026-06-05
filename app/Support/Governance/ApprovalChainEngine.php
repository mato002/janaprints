<?php

namespace App\Support\Governance;

use App\Enums\ApprovalChainMode;
use App\Enums\ApprovalChainRunStatus;
use App\Enums\ApprovalChainStepStatus;
use App\Models\Governance\ApprovalChainRun;
use App\Models\Governance\ApprovalChainStep;
use App\Models\Governance\ApprovalChainStepRecord;
use App\Models\User;
use Illuminate\Support\Collection;

class ApprovalChainEngine
{
    /**
     * @param  Collection<int, ApprovalChainStep>  $steps
     */
    public function initializeStepRecords(ApprovalChainRun $run, Collection $steps): void
    {
        foreach ($steps as $step) {
            ApprovalChainStepRecord::query()->create([
                'approval_chain_run_id' => $run->id,
                'approval_chain_step_id' => $step->id,
                'step_number' => $step->step_number,
                'status' => ApprovalChainStepStatus::Pending,
            ]);
        }
    }

    public function refreshRunStatus(ApprovalChainRun $run): ApprovalChainRun
    {
        $run->loadMissing(['chain', 'stepRecords']);

        if ($run->stepRecords->contains(fn (ApprovalChainStepRecord $record) => $record->status === ApprovalChainStepStatus::Rejected)) {
            $run->update([
                'status' => ApprovalChainRunStatus::Rejected,
                'completed_at' => now(),
            ]);

            return $run->fresh(['stepRecords']);
        }

        $mode = $run->chain->approval_mode;

        if ($mode === ApprovalChainMode::Parallel) {
            return $this->refreshParallelRun($run);
        }

        return $this->refreshSequentialRun($run);
    }

    /**
     * @param  array<string, float>|null  $condition
     * @param  array{amount?: float|null, percent?: float|null}  $context
     */
    public function matchesCondition(?array $condition, array $context): bool
    {
        if ($condition === null || $condition === []) {
            return true;
        }

        $amount = $context['amount'] ?? null;
        $percent = $context['percent'] ?? null;

        if (isset($condition['min_amount']) && ($amount === null || $amount < (float) $condition['min_amount'])) {
            return false;
        }

        if (isset($condition['max_amount']) && ($amount === null || $amount > (float) $condition['max_amount'])) {
            return false;
        }

        if (isset($condition['min_percent']) && ($percent === null || $percent < (float) $condition['min_percent'])) {
            return false;
        }

        if (isset($condition['max_percent']) && ($percent === null || $percent > (float) $condition['max_percent'])) {
            return false;
        }

        return true;
    }

    /**
     * @return Collection<int, ApprovalChainStepRecord>
     */
    public function actionableStepRecords(ApprovalChainRun $run): Collection
    {
        $run->loadMissing(['chain', 'stepRecords.step']);

        if ($run->chain->approval_mode === ApprovalChainMode::Parallel) {
            return $run->stepRecords
                ->filter(fn (ApprovalChainStepRecord $record) => in_array($record->status, [
                    ApprovalChainStepStatus::Pending,
                    ApprovalChainStepStatus::Escalated,
                ], true)
                    && ($record->step?->is_required ?? true))
                ->values();
        }

        $next = $run->stepRecords
            ->sortBy('step_number')
            ->first(fn (ApprovalChainStepRecord $record) => in_array($record->status, [
                ApprovalChainStepStatus::Pending,
                ApprovalChainStepStatus::Escalated,
            ], true));

        return $next ? collect([$next]) : collect();
    }

    public function canUserActOnStep(User $user, ApprovalChainStepRecord $record): bool
    {
        $record->loadMissing('step');

        if ($record->status === ApprovalChainStepStatus::Escalated) {
            return $record->escalated_to_role && $user->hasRole($record->escalated_to_role);
        }

        if ($record->step?->approver_user_id) {
            return (int) $record->step->approver_user_id === (int) $user->id;
        }

        if ($record->step?->approver_role) {
            return $user->hasRole($record->step->approver_role);
        }

        return false;
    }

    protected function refreshSequentialRun(ApprovalChainRun $run): ApprovalChainRun
    {
        $pendingRequired = $run->stepRecords
            ->filter(fn (ApprovalChainStepRecord $record) => $record->status === ApprovalChainStepStatus::Pending
                && ($record->step?->is_required ?? true));

        if ($pendingRequired->isEmpty()) {
            $run->update([
                'status' => ApprovalChainRunStatus::Approved,
                'completed_at' => now(),
            ]);
        }

        return $run->fresh(['stepRecords']);
    }

    protected function refreshParallelRun(ApprovalChainRun $run): ApprovalChainRun
    {
        $requiredRecords = $run->stepRecords->filter(
            fn (ApprovalChainStepRecord $record) => $record->step?->is_required ?? true,
        );

        $allRequiredResolved = $requiredRecords->every(
            fn (ApprovalChainStepRecord $record) => in_array($record->status, [
                ApprovalChainStepStatus::Approved,
                ApprovalChainStepStatus::Skipped,
            ], true),
        );

        if ($allRequiredResolved && $requiredRecords->isNotEmpty()) {
            $run->update([
                'status' => ApprovalChainRunStatus::Approved,
                'completed_at' => now(),
            ]);
        }

        return $run->fresh(['stepRecords']);
    }
}
