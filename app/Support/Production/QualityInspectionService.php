<?php

namespace App\Support\Production;

use App\Enums\ProductionJobCardStatus;
use App\Enums\QualityCheckResult;
use App\Enums\QualityFailReason;
use App\Enums\QualityReworkReason;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\QualityCheck;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class QualityInspectionService
{
    public function __construct(
        protected ProductQcChecklistService $checklists,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function recordInspection(ProductionJobCard $jobCard, array $payload, int $inspectorId): QualityCheck
    {
        $result = QualityCheckResult::from((string) $payload['result']);

        if ($result === QualityCheckResult::ReworkRequired) {
            $result = QualityCheckResult::Failed;
        }

        return DB::transaction(function () use ($jobCard, $payload, $inspectorId, $result) {
            $snapshot = $this->checklists->snapshotForJobCard($jobCard);
            $checklistResults = $this->checklists->mergeChecklistAnswers(
                $snapshot,
                $payload['checklist'] ?? [],
            );

            $requiresApproval = (bool) ($payload['requires_customer_approval'] ?? false)
                || $result === QualityCheckResult::ConditionalPass
                || $this->productRequiresCustomerApproval($jobCard);

            $inspectionDate = isset($payload['inspection_date'])
                ? \Illuminate\Support\Carbon::parse($payload['inspection_date'])
                : now();

            $check = QualityCheck::query()->create([
                'company_id' => $jobCard->company_id,
                'branch_id' => $jobCard->branch_id,
                'production_job_card_id' => $jobCard->id,
                'checked_by' => $inspectorId,
                'result' => $result,
                'comments' => $payload['comments'] ?? null,
                'checked_at' => $inspectionDate,
                'inspection_date' => $inspectionDate->toDateString(),
                'checklist_results' => $checklistResults,
                'fail_reason' => isset($payload['fail_reason']) ? QualityFailReason::from($payload['fail_reason']) : null,
                'rework_reason' => isset($payload['rework_reason']) ? QualityReworkReason::from($payload['rework_reason']) : null,
                'estimated_rework_qty' => $payload['estimated_rework_qty'] ?? null,
                'actual_rework_qty' => $payload['actual_rework_qty'] ?? null,
                'requires_customer_approval' => $requiresApproval,
            ]);

            $snapshot->update(['checklist_items' => $checklistResults]);

            $this->applyStatusTransition($jobCard, $result, $requiresApproval);

            return $check->fresh(['checker', 'customerApprover']);
        });
    }

    public function approveCustomerHold(ProductionJobCard $jobCard, QualityCheck $check, int $approverId): QualityCheck
    {
        if (! $check->requires_customer_approval || $check->result !== QualityCheckResult::ConditionalPass) {
            throw ValidationException::withMessages([
                'approval' => __('This inspection does not require customer approval.'),
            ]);
        }

        if ($check->customer_approved_at !== null) {
            throw ValidationException::withMessages([
                'approval' => __('Customer approval already recorded.'),
            ]);
        }

        return DB::transaction(function () use ($jobCard, $check, $approverId) {
            $check->update([
                'customer_approved_by' => $approverId,
                'customer_approved_at' => now(),
            ]);

            if ($jobCard->status === ProductionJobCardStatus::AwaitingCustomerApproval) {
                $jobCard->transitionTo(ProductionJobCardStatus::ReadyForDispatch);
            }

            return $check->fresh(['checker', 'customerApprover']);
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function reworkSummary(ProductionJobCard $jobCard): array
    {
        $checks = QualityCheck::query()
            ->where('production_job_card_id', $jobCard->id)
            ->whereIn('result', [QualityCheckResult::Failed, QualityCheckResult::ReworkRequired])
            ->orderByDesc('checked_at')
            ->get();

        return [
            'count' => $checks->count(),
            'estimated_total' => round((float) $checks->sum('estimated_rework_qty'), 3),
            'actual_total' => round((float) $checks->sum('actual_rework_qty'), 3),
            'latest_reason' => $checks->first()?->rework_reason?->label(),
            'lines' => $checks->map(fn (QualityCheck $c) => [
                'inspection_date' => $c->inspection_date?->format('Y-m-d') ?? $c->checked_at?->format('Y-m-d'),
                'rework_reason' => $c->rework_reason?->label() ?? $c->fail_reason?->label() ?? '—',
                'estimated_rework_qty' => (float) $c->estimated_rework_qty,
                'actual_rework_qty' => (float) $c->actual_rework_qty,
                'notes' => $c->comments,
            ]),
        ];
    }

    protected function applyStatusTransition(
        ProductionJobCard $jobCard,
        QualityCheckResult $result,
        bool $requiresApproval,
    ): void {
        match ($result) {
            QualityCheckResult::Passed => $jobCard->transitionTo(ProductionJobCardStatus::ReadyForDispatch),
            QualityCheckResult::Failed => $jobCard->transitionTo(ProductionJobCardStatus::Rework),
            QualityCheckResult::ConditionalPass => $jobCard->transitionTo(
                $requiresApproval
                    ? ProductionJobCardStatus::AwaitingCustomerApproval
                    : ProductionJobCardStatus::ReadyForDispatch,
            ),
            QualityCheckResult::ReworkRequired => $jobCard->transitionTo(ProductionJobCardStatus::Rework),
        };
    }

    protected function productRequiresCustomerApproval(ProductionJobCard $jobCard): bool
    {
        $itemId = $jobCard->inventory_item_id
            ?? $jobCard->salesOrder?->items?->firstWhere('inventory_item_id', '!=', null)?->inventory_item_id;

        if (! $itemId) {
            return false;
        }

        return (bool) \App\Models\Inventory\InventoryItem::query()
            ->where('id', $itemId)
            ->value('requires_customer_approval');
    }
}
