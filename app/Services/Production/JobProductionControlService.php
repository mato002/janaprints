<?php

namespace App\Services\Production;

use App\Enums\ArtworkApprovalDecision;
use App\Enums\ArtworkRequestStatus;
use App\Enums\ProductionJobCardStatus;
use App\Enums\QualityCheckResult;
use App\Models\Artwork\ArtworkApproval;
use App\Models\Employee;
use App\Models\Inventory\ProductionMaterialConsumption;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionMaterialRequirement;
use App\Models\Production\ProductionOperation;
use App\Models\Production\QualityCheck;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class JobProductionControlService
{
    public function operatorAssignmentAvailable(): bool
    {
        return Schema::hasColumn('production_operations', 'assigned_employee_id');
    }

    public function wastageTrackingAvailable(): bool
    {
        if (Schema::hasTable('production_wastage_records')) {
            return true;
        }

        return Schema::hasColumn('production_material_consumptions', 'is_wastage');
    }

    /**
     * @return Collection<int, Employee>
     */
    public function scopedOperators(ProductionJobCard $jobCard): Collection
    {
        if (! $this->operatorAssignmentAvailable()) {
            return collect();
        }

        return Employee::query()
            ->where('company_id', $jobCard->company_id)
            ->where('branch_id', $jobCard->branch_id)
            ->where('is_active', true)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->limit(200)
            ->get(['id', 'employee_number', 'first_name', 'last_name']);
    }

    public function operationExecutionStatus(ProductionOperation $operation, ProductionJobCard $jobCard): string
    {
        if ($operation->ended_at !== null) {
            return 'completed';
        }

        if (
            $operation->started_at !== null
            && in_array($jobCard->status, [ProductionJobCardStatus::OnHold, ProductionJobCardStatus::Cancelled], true)
        ) {
            return 'blocked';
        }

        if ($operation->started_at !== null) {
            return 'in_progress';
        }

        return 'pending';
    }

    /**
     * @return array{assigned: int, total: int, percent: int|null, label: string}
     */
    public function operatorAssignmentCompleteness(ProductionJobCard $jobCard): array
    {
        if (! $this->operatorAssignmentAvailable()) {
            return [
                'assigned' => 0,
                'total' => 0,
                'percent' => null,
                'label' => __('Not available'),
            ];
        }

        $total = (int) $jobCard->operations_count;
        $assigned = (int) ($jobCard->assigned_operations_count ?? 0);

        if ($total === 0) {
            return [
                'assigned' => 0,
                'total' => 0,
                'percent' => null,
                'label' => __('No operations'),
            ];
        }

        $percent = (int) round(($assigned / $total) * 100);

        return [
            'assigned' => $assigned,
            'total' => $total,
            'percent' => $percent,
            'label' => $percent.'%',
        ];
    }

    /**
     * @return array{total: int, completed: int, percent: int, incomplete: bool}
     */
    public function operationsCompletionState(ProductionJobCard $jobCard): array
    {
        $base = ProductionOperation::query()->where('production_job_card_id', $jobCard->id);
        $total = (int) (clone $base)->count();
        $completed = (int) (clone $base)->whereNotNull('ended_at')->count();
        $percent = $total > 0 ? (int) min(100, round(($completed / $total) * 100)) : 0;

        return [
            'total' => $total,
            'completed' => $completed,
            'percent' => $percent,
            'incomplete' => $total > 0 && $completed < $total,
        ];
    }

    public function hasIncompleteOperations(ProductionJobCard $jobCard): bool
    {
        return ProductionOperation::query()
            ->where('production_job_card_id', $jobCard->id)
            ->whereNull('ended_at')
            ->exists();
    }

    public function hasUnresolvedQcFailure(ProductionJobCard $jobCard): bool
    {
        $latest = QualityCheck::query()
            ->where('production_job_card_id', $jobCard->id)
            ->latest('checked_at')
            ->first();

        if ($latest === null) {
            return false;
        }

        return in_array($latest->result, [
            QualityCheckResult::Failed,
            QualityCheckResult::ReworkRequired,
            QualityCheckResult::ConditionalPass,
        ], true) && $latest->customer_approved_at === null;
    }

    /**
     * @return array{status: string, label: string, latest: string|null}
     */
    public function qcStatusSummary(ProductionJobCard $jobCard): array
    {
        $latest = QualityCheck::query()
            ->where('production_job_card_id', $jobCard->id)
            ->latest('checked_at')
            ->first();

        if ($latest === null) {
            return [
                'status' => 'none',
                'label' => __('No QC recorded'),
                'latest' => null,
            ];
        }

        return [
            'status' => $latest->result->value,
            'label' => str_replace('_', ' ', $latest->result->value),
            'latest' => $latest->checked_at?->format('Y-m-d H:i'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function wastageSummary(ProductionJobCard $jobCard): array
    {
        if ($this->wastageTrackingAvailable()) {
            return app(\App\Support\Production\ProductionWastageService::class)->summaryForJob($jobCard);
        }

        return [
            'activated' => false,
            'total_quantity' => null,
            'line_count' => null,
            'placeholder' => __('Wastage Tracking Not Activated'),
            'recommended_migration' => $this->recommendedWastageMigration(),
        ];
    }

    public function recommendedWastageMigration(): string
    {
        return <<<'SQL'
Schema::create('production_wastage_records', function (Blueprint $table) {
    $table->id();
    $table->foreignId('company_id')->constrained()->cascadeOnDelete();
    $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
    $table->foreignId('production_job_card_id')->constrained()->cascadeOnDelete();
    $table->foreignId('inventory_item_id')->constrained()->cascadeOnDelete();
    $table->decimal('quantity', 12, 3);
    $table->foreignId('unit_id')->nullable()->constrained('unit_of_measures')->nullOnDelete();
    $table->string('reason');
    $table->foreignId('recorded_by')->constrained('users')->cascadeOnDelete();
    $table->timestamp('recorded_at')->useCurrent();
    $table->timestamps();
});
SQL;
    }

    public function isArtworkApproved(ProductionJobCard $jobCard): bool
    {
        if (! $jobCard->artwork_request_id) {
            return true;
        }

        if ($jobCard->artworkRequest?->status === ArtworkRequestStatus::Approved) {
            return true;
        }

        $latest = ArtworkApproval::query()
            ->where('artwork_request_id', $jobCard->artwork_request_id)
            ->latest('created_at')
            ->first();

        return $latest?->decision === ArtworkApprovalDecision::Approved;
    }

    /**
     * @return list<array{key: string, label: string, state: string, detail: string}>
     */
    public function readinessChecklist(ProductionJobCard $jobCard): array
    {
        $ops = $this->operationsCompletionState($jobCard);
        $qc = $this->qcStatusSummary($jobCard);
        $canTransition = $jobCard->status->canTransitionTo(ProductionJobCardStatus::ReadyForDispatch);
        $consumptionCount = (int) $jobCard->material_consumptions_count;

        $items = [];

        $items[] = [
            'key' => 'job_status',
            'label' => __('Job status allows dispatch'),
            'state' => $canTransition ? 'passed' : ($jobCard->status === ProductionJobCardStatus::ReadyForDispatch ? 'passed' : 'failed'),
            'detail' => str_replace('_', ' ', $jobCard->status->value),
        ];

        if ($ops['total'] === 0) {
            $items[] = [
                'key' => 'operations',
                'label' => __('Operations complete'),
                'state' => 'warning',
                'detail' => __('No operations logged'),
            ];
        } else {
            $items[] = [
                'key' => 'operations',
                'label' => __('Operations complete'),
                'state' => $ops['incomplete'] ? 'failed' : 'passed',
                'detail' => $ops['completed'].' / '.$ops['total'],
            ];
        }

        if ($qc['status'] === 'none') {
            $items[] = [
                'key' => 'qc',
                'label' => __('QC passed'),
                'state' => 'warning',
                'detail' => __('No QC checks recorded'),
            ];
        } elseif ($this->hasUnresolvedQcFailure($jobCard)) {
            $items[] = [
                'key' => 'qc',
                'label' => __('QC passed'),
                'state' => 'failed',
                'detail' => $qc['label'],
            ];
        } else {
            $items[] = [
                'key' => 'qc',
                'label' => __('QC passed'),
                'state' => 'passed',
                'detail' => $qc['label'],
            ];
        }

        $materialsState = $this->materialsReadinessState($jobCard, $consumptionCount);

        $items[] = [
            'key' => 'materials',
            'label' => __('Materials consumed / requirements'),
            'state' => $materialsState['state'],
            'detail' => $materialsState['detail'],
        ];

        if ($jobCard->artwork_request_id) {
            $items[] = [
                'key' => 'artwork',
                'label' => __('Artwork approved'),
                'state' => $this->isArtworkApproved($jobCard) ? 'passed' : 'failed',
                'detail' => $jobCard->artworkRequest?->status->value ?? '—',
            ];
        } else {
            $items[] = [
                'key' => 'artwork',
                'label' => __('Artwork approved'),
                'state' => 'na',
                'detail' => __('Not linked'),
            ];
        }

        $items[] = [
            'key' => 'sales_order',
            'label' => __('Sales order linked'),
            'state' => $jobCard->sales_order_id ? 'passed' : 'failed',
            'detail' => $jobCard->salesOrder?->order_number ?? __('Not linked'),
        ];

        if (Schema::hasTable('delivery_notes')) {
            $activeNote = \App\Models\Dispatch\DeliveryNote::query()
                ->where('production_job_card_id', $jobCard->id)
                ->whereNot('status', \App\Enums\Dispatch\DeliveryNoteStatus::Cancelled->value)
                ->first();

            $items[] = [
                'key' => 'delivery_notes',
                'label' => __('Delivery note'),
                'state' => $activeNote ? 'passed' : ($this->deliveryNoteCreationEligibility($jobCard)['eligible'] ? 'warning' : 'failed'),
                'detail' => $activeNote?->delivery_note_number ?? __('Not created'),
            ];
        } else {
            $items[] = [
                'key' => 'delivery_notes',
                'label' => __('Delivery notes module'),
                'state' => 'na',
                'detail' => __('Not available'),
            ];
        }

        return $items;
    }

    public function dispatchReadinessScore(ProductionJobCard $jobCard): int
    {
        $checklist = $this->readinessChecklist($jobCard);
        $scored = collect($checklist)->filter(fn (array $item) => $item['state'] !== 'na');
        $applicable = $scored->count();

        if ($applicable === 0) {
            return 0;
        }

        $passed = $scored->where('state', 'passed')->count();
        $warning = $scored->where('state', 'warning')->count();

        return (int) min(100, round((($passed + ($warning * 0.5)) / $applicable) * 100));
    }

    /**
     * @return array{eligible: bool, blockers: list<string>, warnings: list<string>}
     */
    public function dispatchEligibility(ProductionJobCard $jobCard): array
    {
        $blockers = [];
        $warnings = [];

        if (! $jobCard->status->canTransitionTo(ProductionJobCardStatus::ReadyForDispatch)
            && $jobCard->status !== ProductionJobCardStatus::ReadyForDispatch) {
            $blockers[] = __('Job status does not allow dispatch.');
        }

        if ($this->hasUnresolvedQcFailure($jobCard)) {
            $blockers[] = __('QC failed — dispatch blocked');
        }

        if ($this->hasIncompleteOperations($jobCard)) {
            $blockers[] = __('Operations incomplete — dispatch blocked');
        }

        if ($jobCard->artwork_request_id && ! $this->isArtworkApproved($jobCard)) {
            $blockers[] = __('Artwork not approved — dispatch blocked');
        }

        $consumptionCount = (int) $jobCard->material_consumptions_count;
        if ($consumptionCount === 0) {
            $warnings[] = __('No material consumption recorded');
        }

        $eligible = $blockers === []
            && ($jobCard->status->canTransitionTo(ProductionJobCardStatus::ReadyForDispatch)
                || $jobCard->status === ProductionJobCardStatus::ReadyForDispatch);

        return [
            'eligible' => $eligible,
            'blockers' => $blockers,
            'warnings' => $warnings,
        ];
    }

    /**
     * Eligibility to create a delivery note (operational delivery truth).
     *
     * @return array{eligible: bool, blockers: list<string>}
     */
    public function deliveryNoteCreationEligibility(ProductionJobCard $jobCard): array
    {
        $blockers = [];

        if ($jobCard->status !== ProductionJobCardStatus::ReadyForDispatch) {
            $blockers[] = __('Job must be marked ready for dispatch before creating a delivery note.');
        }

        if ($this->hasUnresolvedQcFailure($jobCard)) {
            $blockers[] = __('QC failed — delivery note blocked');
        }

        if ($this->hasIncompleteOperations($jobCard)) {
            $blockers[] = __('Operations incomplete — delivery note blocked');
        }

        if ($jobCard->artwork_request_id && ! $this->isArtworkApproved($jobCard)) {
            $blockers[] = __('Artwork not approved — delivery note blocked');
        }

        return [
            'eligible' => $blockers === [],
            'blockers' => $blockers,
        ];
    }

    /**
     * @return list<array{type: string, message: string}>
     */
    public function controlAlerts(ProductionJobCard $jobCard): array
    {
        $alerts = [];
        $eligibility = $this->dispatchEligibility($jobCard);

        foreach ($eligibility['blockers'] as $message) {
            $alerts[] = ['type' => 'error', 'message' => $message];
        }

        foreach ($eligibility['warnings'] as $message) {
            $alerts[] = ['type' => 'warning', 'message' => $message];
        }

        return $alerts;
    }

    public function materialCostingIncomplete(ProductionJobCard $jobCard): bool
    {
        return ProductionMaterialConsumption::query()
            ->where('production_job_card_id', $jobCard->id)
            ->where(function ($q) {
                $q->whereNull('unit_cost')->orWhere('unit_cost', 0);
            })
            ->exists();
    }

    /**
     * @return array{state: string, detail: string}
     */
    public function materialsReadinessState(ProductionJobCard $jobCard, ?int $consumptionCount = null): array
    {
        $consumptionCount ??= (int) $jobCard->material_consumptions_count;

        $requirements = ProductionMaterialRequirement::query()
            ->where('production_job_card_id', $jobCard->id)
            ->get(['id', 'required_quantity', 'consumed_quantity', 'inventory_item_id', 'warehouse_id', 'production_job_card_id']);

        if ($consumptionCount === 0) {
            return [
                'state' => 'warning',
                'detail' => $requirements->isEmpty()
                    ? __('No material consumption recorded')
                    : __('Material requirements generated but nothing consumed yet'),
            ];
        }

        if ($requirements->isEmpty()) {
            return [
                'state' => 'passed',
                'detail' => (string) $consumptionCount.' '.__('consumption lines'),
            ];
        }

        $openRemaining = $requirements->sum(fn (ProductionMaterialRequirement $row) => $row->remainingQuantity());

        if ($openRemaining > 0) {
            return [
                'state' => 'warning',
                'detail' => __(':count consumption lines — :remaining units still open on requirements', [
                    'count' => $consumptionCount,
                    'remaining' => round($openRemaining, 3),
                ]),
            ];
        }

        return [
            'state' => 'passed',
            'detail' => (string) $consumptionCount.' '.__('consumption lines'),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function productionKpis(ProductionJobCard $jobCard): array
    {
        $ops = $this->operationsCompletionState($jobCard);
        $operator = $this->operatorAssignmentCompleteness($jobCard);
        $qc = $this->qcStatusSummary($jobCard);
        $wastage = $this->wastageSummary($jobCard);
        $sessionWaste = app(\App\Support\Production\ProductionSessionService::class)->jobMetrics($jobCard);
        $serialLoss = app(\App\Support\Production\SerialNumberGovernanceService::class)->productionLossMetrics($jobCard);
        $score = $this->dispatchReadinessScore($jobCard);
        $costingIncomplete = $this->materialCostingIncomplete($jobCard);

        $materialCost = ProductionMaterialConsumption::query()
            ->where('production_job_card_id', $jobCard->id)
            ->selectRaw('COALESCE(SUM(quantity * COALESCE(unit_cost, 0)), 0) as total')
            ->value('total');

        return [
            [
                'key' => 'operation_completion',
                'label' => __('Operation completion'),
                'icon' => 'chart-bar',
                'metrics' => [
                    ['label' => __('Complete'), 'value' => $ops['percent'].'%'],
                    ['label' => __('Done'), 'value' => $ops['completed'].' / '.$ops['total']],
                ],
            ],
            [
                'key' => 'operators',
                'label' => __('Operator assignment'),
                'icon' => 'user',
                'metrics' => [
                    ['label' => __('Assigned'), 'value' => $operator['label']],
                    ['label' => __('Coverage'), 'value' => $operator['assigned'].' / '.$operator['total']],
                ],
                'placeholder' => $operator['percent'] === null && $operator['total'] === 0
                    ? null
                    : ($this->operatorAssignmentAvailable() ? null : __('Operator column not available')),
            ],
            [
                'key' => 'quality',
                'label' => __('QC status'),
                'icon' => 'badge-check',
                'warning' => $this->hasUnresolvedQcFailure($jobCard) ? __('QC failed — dispatch blocked') : null,
                'metrics' => [
                    ['label' => __('Latest'), 'value' => $qc['label']],
                    ['label' => __('Failed count'), 'value' => (int) $jobCard->failed_qc_count],
                ],
            ],
            [
                'key' => 'materials',
                'label' => __('Material costing'),
                'icon' => 'cube',
                'warning' => $costingIncomplete ? __('Costing incomplete for one or more lines') : null,
                'metrics' => [
                    ['label' => __('Lines'), 'value' => (int) $jobCard->material_consumptions_count],
                    ['label' => __('Est. cost'), 'value' => number_format((float) $materialCost, 2)],
                ],
            ],
            [
                'key' => 'wastage',
                'label' => __('Wastage'),
                'icon' => 'trash',
                'placeholder' => $wastage['placeholder'],
                'metrics' => $wastage['activated']
                    ? [
                        ['label' => __('Lines'), 'value' => $wastage['line_count']],
                        ['label' => __('Qty'), 'value' => $wastage['total_quantity']],
                    ]
                    : [],
            ],
            [
                'key' => 'session_waste',
                'label' => __('Session waste'),
                'icon' => 'trash',
                'metrics' => [
                    ['label' => __('Sessions'), 'value' => $sessionWaste['session_count']],
                    ['label' => __('Waste qty'), 'value' => number_format($sessionWaste['total_waste'], 0)],
                ],
            ],
            [
                'key' => 'serial_loss',
                'label' => __('Serial spoilage'),
                'icon' => 'document',
                'metrics' => [
                    ['label' => __('Spoiled'), 'value' => $serialLoss['spoiled']],
                    ['label' => __('Loss qty'), 'value' => $serialLoss['production_loss_quantity']],
                ],
            ],
            [
                'key' => 'dispatch_score',
                'label' => __('Dispatch readiness'),
                'icon' => 'truck',
                'metrics' => [
                    ['label' => __('Score'), 'value' => $score.'%'],
                    ['label' => __('Status'), 'value' => $this->dispatchEligibility($jobCard)['eligible'] ? __('Eligible') : __('Blocked')],
                ],
            ],
        ];
    }
}
