<?php

namespace App\Support\Reports;

use App\Enums\FulfilmentStatus;
use App\Enums\Dispatch\DeliveryNoteStatus;
use App\Enums\InventoryMovementType;
use App\Enums\ProductionJobCardStatus;
use App\Enums\ProductionQueueStatus;
use App\Enums\ProductionType;
use App\Enums\QualityCheckResult;
use App\Models\Dispatch\DeliveryNote;
use App\Models\Inventory\InventoryMovement;
use App\Models\Inventory\ProductionMaterialConsumption;
use App\Models\Production\JobCostSheet;
use App\Models\Production\ProductionFulfilment;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionQueue;
use App\Models\Production\QualityCheck;
use App\Models\Production\WorkCenter;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class ProductionReportQueries
{
    public function __construct(
        protected IntelligenceAggregateQueries $aggregates,
        protected Production360Queries $production360,
    ) {}

    public function countJobsCompleted(IntelligenceScope $scope): int
    {
        return $this->aggregates->countCompletedJobsInPeriod($scope);
    }

    public function countJobsDelayed(IntelligenceScope $scope): int
    {
        return $this->aggregates->countDelayedJobs($scope);
    }

    public function countJobsCancelled(IntelligenceScope $scope): int
    {
        if (! $this->aggregates->hasTable('production_job_cards')) {
            return 0;
        }

        return (int) $this->production360->jobs($scope)
            ->where('status', ProductionJobCardStatus::Cancelled)
            ->whereDate('updated_at', '>=', $scope->fromDate)
            ->whereDate('updated_at', '<=', $scope->toDate)
            ->count();
    }

    public function averageTurnaroundDays(IntelligenceScope $scope): ?float
    {
        if (! $this->aggregates->hasTable('production_job_cards')) {
            return null;
        }

        $jobs = $this->production360->jobs($scope)
            ->where('status', ProductionJobCardStatus::Completed)
            ->whereNotNull('actual_start_date')
            ->whereNotNull('actual_end_date')
            ->whereDate('actual_end_date', '>=', $scope->fromDate)
            ->whereDate('actual_end_date', '<=', $scope->toDate)
            ->get(['actual_start_date', 'actual_end_date']);

        if ($jobs->isEmpty()) {
            return null;
        }

        $avg = $jobs->avg(fn (ProductionJobCard $job) => $job->actual_start_date->diffInDays($job->actual_end_date));

        return round((float) $avg, 1);
    }

    /**
     * @return list<array<int, string|int>>
     */
    public function throughputByDay(IntelligenceScope $scope): array
    {
        $period = CarbonPeriod::create($scope->fromDate, $scope->toDate);

        return collect($period)->map(function (Carbon $day) use ($scope) {
            $date = $day->toDateString();
            $dayScope = new IntelligenceScope($scope->companyId, $scope->branchId, $date, $date);

            return [
                $day->format('Y-m-d'),
                $this->countJobsCompleted($dayScope),
                $this->countJobsDelayed($dayScope),
                $this->countJobsCancelled($dayScope),
            ];
        })->all();
    }

    /**
     * @return list<array<int, string|int>>
     */
    public function departmentThroughput(IntelligenceScope $scope): array
    {
        if (! $this->aggregates->hasTable('production_queues') || ! $this->aggregates->hasTable('work_centers')) {
            return [];
        }

        $completedJobIds = $this->production360->jobs($scope)
            ->where('status', ProductionJobCardStatus::Completed)
            ->where(function ($q) use ($scope) {
                $q->whereDate('actual_end_date', '>=', $scope->fromDate)
                    ->whereDate('actual_end_date', '<=', $scope->toDate)
                    ->orWhere(function ($q2) use ($scope) {
                        $q2->whereNull('actual_end_date')
                            ->whereDate('updated_at', '>=', $scope->fromDate)
                            ->whereDate('updated_at', '<=', $scope->toDate);
                    });
            })
            ->pluck('id');

        if ($completedJobIds->isEmpty()) {
            return collect(Production360Queries::DEPARTMENTS)
                ->map(fn (array $dept) => [__($dept['label']), 0])
                ->all();
        }

        $counts = ProductionQueue::query()
            ->join('work_centers', 'work_centers.id', '=', 'production_queues.work_center_id')
            ->where('production_queues.company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('production_queues.branch_id', $scope->branchId))
            ->whereIn('production_job_card_id', $completedJobIds)
            ->select('work_centers.code', DB::raw('COUNT(DISTINCT production_job_card_id) as completed_jobs'))
            ->groupBy('work_centers.code')
            ->pluck('completed_jobs', 'code');

        return collect(Production360Queries::DEPARTMENTS)
            ->map(fn (array $dept) => [
                __($dept['label']),
                (int) ($counts[$dept['code']] ?? 0),
            ])
            ->sortByDesc(fn (array $row) => $row[1])
            ->values()
            ->all();
    }

    /**
     * @return list<array<int, string|int|float>>
     */
    public function machineUtilization(IntelligenceScope $scope): array
    {
        if (! $this->aggregates->hasTable('work_centers') || ! $this->aggregates->hasTable('production_queues')) {
            return [];
        }

        $days = max(1, Carbon::parse($scope->fromDate)->diffInDays(Carbon::parse($scope->toDate)) + 1);
        $capacity = max(1, (int) config('production.scheduling.default_work_center_capacity', 5));

        $centers = WorkCenter::query()
            ->where('company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        if ($centers->isEmpty()) {
            return [];
        }

        $completedByCenter = ProductionQueue::query()
            ->join('production_job_cards', 'production_job_cards.id', '=', 'production_queues.production_job_card_id')
            ->where('production_queues.company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('production_queues.branch_id', $scope->branchId))
            ->where('production_queues.status', ProductionQueueStatus::Completed)
            ->where('production_job_cards.status', ProductionJobCardStatus::Completed)
            ->where(function ($q) use ($scope) {
                $q->whereDate('production_job_cards.actual_end_date', '>=', $scope->fromDate)
                    ->whereDate('production_job_cards.actual_end_date', '<=', $scope->toDate);
            })
            ->select('production_queues.work_center_id', DB::raw('COUNT(DISTINCT production_queues.production_job_card_id) as completed'))
            ->groupBy('production_queues.work_center_id')
            ->pluck('completed', 'work_center_id');

        return $centers->map(function (WorkCenter $center) use ($completedByCenter, $days, $capacity) {
            $completed = (int) ($completedByCenter[$center->id] ?? 0);
            $utilization = round(min(100, ($completed / ($days * $capacity)) * 100), 1);

            return [$center->name, $center->code, $completed, $utilization.'%'];
        })->all();
    }

    /**
     * @return array{pass_rate: float, fail_rate: float, rework_rate: float, hold_rate: float, total_checks: int}
     */
    public function qualityRates(IntelligenceScope $scope): array
    {
        return $this->production360->qualityRates($scope);
    }

    /**
     * @return list<array<int, string|int|float>>
     */
    public function qualityByDay(IntelligenceScope $scope): array
    {
        if (! $this->aggregates->hasTable('quality_checks')) {
            return [];
        }

        $period = CarbonPeriod::create($scope->fromDate, $scope->toDate);

        return collect($period)->map(function (Carbon $day) use ($scope) {
            $date = $day->toDateString();

            $checks = QualityCheck::query()
                ->where('company_id', $scope->companyId)
                ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
                ->whereDate('checked_at', $date);

            $passed = (clone $checks)->where('result', QualityCheckResult::Passed)->count();
            $failed = (clone $checks)->where('result', QualityCheckResult::Failed)->count();
            $rework = (clone $checks)->where('result', QualityCheckResult::ReworkRequired)->count();
            $total = $passed + $failed + $rework;

            return [
                $day->format('Y-m-d'),
                $total,
                $total > 0 ? round(($passed / $total) * 100, 1).'%' : '0%',
                $total > 0 ? round(($failed / $total) * 100, 1).'%' : '0%',
                $total > 0 ? round(($rework / $total) * 100, 1).'%' : '0%',
            ];
        })->all();
    }

    /**
     * @return list<array<int, string|int|float>>
     */
    public function qualityFailReasonRows(IntelligenceScope $scope): array
    {
        if (! $this->aggregates->hasTable('quality_checks')) {
            return [];
        }

        return QualityCheck::query()
            ->where('company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
            ->whereBetween('checked_at', [$scope->fromDate.' 00:00:00', $scope->toDate.' 23:59:59'])
            ->whereNotNull('fail_reason')
            ->selectRaw('fail_reason, COUNT(*) as cnt, COALESCE(SUM(estimated_rework_qty), 0) as est_qty')
            ->groupBy('fail_reason')
            ->orderByDesc('cnt')
            ->get()
            ->map(fn ($row) => [
                \App\Enums\QualityFailReason::tryFrom((string) $row->fail_reason)?->label() ?? (string) $row->fail_reason,
                (int) $row->cnt,
                round((float) $row->est_qty, 3),
            ])
            ->all();
    }

    /**
     * @return list<array<int, string|int|float>>
     */
    public function reworkSummaryRows(IntelligenceScope $scope): array
    {
        if (! $this->aggregates->hasTable('quality_checks')) {
            return [];
        }

        return QualityCheck::query()
            ->where('company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
            ->whereBetween('checked_at', [$scope->fromDate.' 00:00:00', $scope->toDate.' 23:59:59'])
            ->whereIn('result', [QualityCheckResult::Failed, QualityCheckResult::ReworkRequired])
            ->with('jobCard:id,job_card_number')
            ->orderByDesc('checked_at')
            ->limit(200)
            ->get()
            ->map(fn (QualityCheck $check) => [
                $check->jobCard?->job_card_number ?? '—',
                $check->rework_reason?->label() ?? $check->fail_reason?->label() ?? '—',
                round((float) $check->estimated_rework_qty, 3),
                round((float) $check->actual_rework_qty, 3),
                $check->checked_at?->format('Y-m-d') ?? '—',
            ])
            ->all();
    }

    /**
     * @return list<array<int, string>>
     */
    public function materialConsumptionRows(IntelligenceScope $scope): array
    {
        $data = $this->production360->materialConsumption($scope);

        return collect($data['top_materials'] ?? [])
            ->map(fn (array $row) => $row['cells'] ?? [])
            ->all();
    }

    /**
     * @return array{lines: int, total_cost: string}
     */
    public function materialCostSummary(IntelligenceScope $scope): array
    {
        $data = $this->production360->materialConsumption($scope);

        return [
            'lines' => (int) ($data['lines'] ?? 0),
            'total_cost' => $this->aggregates->money((float) ($data['total_cost'] ?? 0)),
        ];
    }

    /**
     * @return list<array<int, string>>
     */
    public function wasteAnalysisRows(IntelligenceScope $scope): array
    {
        if (! $this->aggregates->hasTable('inventory_movements')) {
            return [];
        }

        $rows = InventoryMovement::query()
            ->join('inventory_items', 'inventory_items.id', '=', 'inventory_movements.inventory_item_id')
            ->where('inventory_movements.company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('inventory_movements.branch_id', $scope->branchId))
            ->whereIn('inventory_movements.movement_type', [
                InventoryMovementType::ProductionWaste,
                InventoryMovementType::Adjustment,
            ])
            ->where('inventory_movements.quantity', '<', 0)
            ->whereDate('inventory_movements.movement_date', '>=', $scope->fromDate)
            ->whereDate('inventory_movements.movement_date', '<=', $scope->toDate)
            ->select(
                'inventory_items.item_name',
                DB::raw('ABS(SUM(inventory_movements.quantity)) as waste_qty'),
                DB::raw('ABS(SUM(inventory_movements.quantity * inventory_movements.unit_cost)) as waste_cost'),
            )
            ->groupBy('inventory_items.id', 'inventory_items.item_name')
            ->orderByDesc('waste_cost')
            ->limit(50)
            ->get();

        return $rows->map(fn ($row) => [
            $row->item_name,
            number_format((float) $row->waste_qty, 2),
            $this->aggregates->money((float) $row->waste_cost),
        ])->all();
    }

    /**
     * @return list<array<int, string>>
     */
    public function productionMaterialUsageRows(IntelligenceScope $scope): array
    {
        if (! $this->aggregates->hasTable('production_material_issues')) {
            return $this->materialConsumptionRows($scope);
        }

        $rows = DB::table('production_material_issues')
            ->join('inventory_items', 'inventory_items.id', '=', 'production_material_issues.inventory_item_id')
            ->where('production_material_issues.company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('production_material_issues.branch_id', $scope->branchId))
            ->whereDate('production_material_issues.issued_at', '>=', $scope->fromDate)
            ->whereDate('production_material_issues.issued_at', '<=', $scope->toDate)
            ->select(
                'inventory_items.item_name',
                DB::raw('SUM(production_material_issues.quantity) as issued_qty'),
                DB::raw('SUM(production_material_issues.quantity * production_material_issues.unit_cost) as issued_cost'),
            )
            ->groupBy('inventory_items.id', 'inventory_items.item_name')
            ->orderByDesc('issued_cost')
            ->limit(50)
            ->get();

        return $rows->map(fn ($row) => [
            $row->item_name,
            number_format((float) $row->issued_qty, 2),
            $this->aggregates->money((float) $row->issued_cost),
        ])->all();
    }

    /**
     * @return list<array<int, string>>
     */
    public function materialVarianceRows(IntelligenceScope $scope): array
    {
        if (! $this->aggregates->hasTable('production_material_requirements')) {
            return [];
        }

        $rows = DB::table('production_material_requirements')
            ->join('inventory_items', 'inventory_items.id', '=', 'production_material_requirements.inventory_item_id')
            ->join('production_job_cards', 'production_job_cards.id', '=', 'production_material_requirements.production_job_card_id')
            ->where('production_material_requirements.company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('production_material_requirements.branch_id', $scope->branchId))
            ->whereDate('production_job_cards.created_at', '>=', $scope->fromDate)
            ->whereDate('production_job_cards.created_at', '<=', $scope->toDate)
            ->select(
                'inventory_items.item_name',
                DB::raw('SUM(production_material_requirements.required_quantity) as required_qty'),
                DB::raw('SUM(production_material_requirements.consumed_quantity) as consumed_qty'),
                DB::raw('SUM(production_material_requirements.required_quantity - production_material_requirements.consumed_quantity) as variance_qty'),
            )
            ->groupBy('inventory_items.id', 'inventory_items.item_name')
            ->havingRaw('SUM(production_material_requirements.required_quantity - production_material_requirements.consumed_quantity) != 0')
            ->orderByDesc('variance_qty')
            ->limit(50)
            ->get();

        return $rows->map(fn ($row) => [
            $row->item_name,
            number_format((float) $row->required_qty, 2),
            number_format((float) $row->consumed_qty, 2),
            number_format((float) $row->variance_qty, 2),
        ])->all();
    }

    /**
     * @return array{on_time: int, late: int, throughput: int, on_time_rate: float}
     */
    public function dispatchMetrics(IntelligenceScope $scope): array
    {
        return $this->production360->dispatchMetrics($scope);
    }

    /**
     * @return array{ready_for_collection: int, collected: int, delivered: int, outstanding_collections: int, outstanding_deliveries: int}
     */
    public function fulfilmentMetrics(IntelligenceScope $scope): array
    {
        if (! $this->aggregates->hasTable('production_fulfilments')) {
            return [
                'ready_for_collection' => 0,
                'collected' => 0,
                'delivered' => 0,
                'outstanding_collections' => 0,
                'outstanding_deliveries' => 0,
            ];
        }

        $base = ProductionFulfilment::query()
            ->where('company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId));

        return [
            'ready_for_collection' => (int) (clone $base)
                ->where('status', FulfilmentStatus::ReadyForCollection)
                ->whereDate('prepared_at', '>=', $scope->fromDate)
                ->whereDate('prepared_at', '<=', $scope->toDate)
                ->count(),
            'collected' => (int) (clone $base)
                ->where('status', FulfilmentStatus::Collected)
                ->whereDate('collected_at', '>=', $scope->fromDate)
                ->whereDate('collected_at', '<=', $scope->toDate)
                ->count(),
            'delivered' => (int) (clone $base)
                ->where('status', FulfilmentStatus::Delivered)
                ->whereDate('delivered_at', '>=', $scope->fromDate)
                ->whereDate('delivered_at', '<=', $scope->toDate)
                ->count(),
            'outstanding_collections' => (int) (clone $base)
                ->where('status', FulfilmentStatus::ReadyForCollection)
                ->count(),
            'outstanding_deliveries' => (int) (clone $base)
                ->where('status', FulfilmentStatus::Dispatched)
                ->count(),
        ];
    }

    /**
     * @return list<array<int, string>>
     */
    public function readyForCollectionRows(IntelligenceScope $scope): array
    {
        return $this->fulfilmentRows($scope, FulfilmentStatus::ReadyForCollection, 'prepared_at', [
            fn ($f) => $f->jobCard?->job_card_number ?? '—',
            fn ($f) => $f->salesOrder?->order_number ?? '—',
            fn ($f) => $f->prepared_at?->format('Y-m-d H:i') ?? '—',
            fn ($f) => $f->preparedByUser?->name ?? '—',
        ]);
    }

    /**
     * @return list<array<int, string>>
     */
    public function collectedOrdersRows(IntelligenceScope $scope): array
    {
        return $this->fulfilmentRows($scope, FulfilmentStatus::Collected, 'collected_at', [
            fn ($f) => $f->jobCard?->job_card_number ?? '—',
            fn ($f) => $f->salesOrder?->order_number ?? '—',
            fn ($f) => $f->collected_by_name ?? '—',
            fn ($f) => $f->collected_at?->format('Y-m-d H:i') ?? '—',
        ]);
    }

    /**
     * @return list<array<int, string>>
     */
    public function fulfilmentDeliveredRows(IntelligenceScope $scope): array
    {
        return $this->fulfilmentRows($scope, FulfilmentStatus::Delivered, 'delivered_at', [
            fn ($f) => $f->jobCard?->job_card_number ?? '—',
            fn ($f) => $f->salesOrder?->order_number ?? '—',
            fn ($f) => $f->received_by ?? $f->recipient_name ?? '—',
            fn ($f) => $f->delivered_at?->format('Y-m-d H:i') ?? '—',
        ]);
    }

    /**
     * @return list<array<int, string|int>>
     */
    public function outstandingCollectionsRows(IntelligenceScope $scope): array
    {
        if (! $this->aggregates->hasTable('production_fulfilments')) {
            return [];
        }

        return ProductionFulfilment::query()
            ->with(['jobCard:id,job_card_number', 'salesOrder:id,order_number'])
            ->where('company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
            ->where('status', FulfilmentStatus::ReadyForCollection)
            ->orderBy('prepared_at')
            ->limit(100)
            ->get()
            ->map(fn (ProductionFulfilment $f) => [
                $f->jobCard?->job_card_number ?? '—',
                $f->salesOrder?->order_number ?? '—',
                $f->prepared_at?->format('Y-m-d H:i') ?? '—',
                $f->prepared_at ? max(0, $f->prepared_at->diffInDays(now())) : 0,
            ])
            ->all();
    }

    /**
     * @return list<array<int, string>>
     */
    public function outstandingDeliveriesRows(IntelligenceScope $scope): array
    {
        if (! $this->aggregates->hasTable('production_fulfilments')) {
            return [];
        }

        return ProductionFulfilment::query()
            ->with(['jobCard:id,job_card_number', 'salesOrder:id,order_number'])
            ->where('company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
            ->where('status', FulfilmentStatus::Dispatched)
            ->orderBy('dispatched_at')
            ->limit(100)
            ->get()
            ->map(fn (ProductionFulfilment $f) => [
                $f->jobCard?->job_card_number ?? '—',
                $f->salesOrder?->order_number ?? '—',
                $f->recipient_name ?? '—',
                $f->dispatched_at?->format('Y-m-d H:i') ?? '—',
            ])
            ->all();
    }

    /**
     * @param  list<\Closure(ProductionFulfilment): string>  $mappers
     * @return list<array<int, string>>
     */
    protected function fulfilmentRows(
        IntelligenceScope $scope,
        FulfilmentStatus $status,
        string $dateColumn,
        array $mappers,
    ): array {
        if (! $this->aggregates->hasTable('production_fulfilments')) {
            return [];
        }

        return ProductionFulfilment::query()
            ->with(['jobCard:id,job_card_number', 'salesOrder:id,order_number', 'preparedByUser:id,name'])
            ->where('company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
            ->where('status', $status)
            ->whereDate($dateColumn, '>=', $scope->fromDate)
            ->whereDate($dateColumn, '<=', $scope->toDate)
            ->orderByDesc($dateColumn)
            ->limit(100)
            ->get()
            ->map(fn (ProductionFulfilment $f) => collect($mappers)->map(fn ($m) => $m($f))->all())
            ->all();
    }

    /**
     * @return list<array<int, string>>
     */
    public function deliveredJobsRows(IntelligenceScope $scope): array
    {
        if (! $this->aggregates->hasTable('delivery_notes')) {
            return [];
        }

        return DeliveryNote::query()
            ->with(['productionJobCard:id,job_card_number'])
            ->where('company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
            ->where('status', DeliveryNoteStatus::Delivered)
            ->whereDate('delivered_at', '>=', $scope->fromDate)
            ->whereDate('delivered_at', '<=', $scope->toDate)
            ->orderByDesc('delivered_at')
            ->limit(100)
            ->get()
            ->map(fn (DeliveryNote $note) => [
                $note->productionJobCard?->job_card_number ?? '—',
                $note->delivery_note_number ?? '—',
                $note->delivered_at?->format('Y-m-d H:i') ?? '—',
                $note->delivery_date?->format('Y-m-d') ?? '—',
            ])
            ->all();
    }

    /**
     * @return list<array<int, string|int>>
     */
    public function lateDeliveriesRows(IntelligenceScope $scope): array
    {
        if (! $this->aggregates->hasTable('delivery_notes')) {
            return [];
        }

        return DeliveryNote::query()
            ->with(['productionJobCard:id,job_card_number'])
            ->where('company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
            ->where('status', DeliveryNoteStatus::Delivered)
            ->whereDate('delivered_at', '>=', $scope->fromDate)
            ->whereDate('delivered_at', '<=', $scope->toDate)
            ->orderByDesc('delivered_at')
            ->get()
            ->filter(function (DeliveryNote $note) {
                if (! $note->delivery_date || ! $note->delivered_at) {
                    return false;
                }

                return $note->delivered_at->startOfDay()->gt($note->delivery_date->startOfDay());
            })
            ->take(100)
            ->map(function (DeliveryNote $note) {
                $daysLate = $note->delivery_date && $note->delivered_at
                    ? max(0, $note->delivery_date->diffInDays($note->delivered_at->startOfDay()))
                    : 0;

                return [
                    $note->productionJobCard?->job_card_number ?? '—',
                    $note->delivery_note_number ?? '—',
                    $note->delivery_date?->format('Y-m-d') ?? '—',
                    $note->delivered_at?->format('Y-m-d') ?? '—',
                    $daysLate,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return list<array<int, string|float>>
     */
    public function jobProfitabilityRows(IntelligenceScope $scope): array
    {
        if (! $this->aggregates->hasTable('job_cost_sheets')) {
            return [];
        }

        return $this->profitabilityQuery($scope)
            ->with(['jobCard:id,job_card_number'])
            ->orderByDesc('job_cost_sheets.gross_profit')
            ->limit(100)
            ->get()
            ->map(fn (JobCostSheet $sheet) => [
                $sheet->jobCard?->job_card_number ?? '—',
                $this->aggregates->money((float) $sheet->revenue),
                $this->aggregates->money((float) $sheet->total_cost),
                $this->aggregates->money((float) $sheet->gross_profit),
                round((float) $sheet->gross_margin_percent, 1).'%',
            ])
            ->all();
    }

    /**
     * @return list<array<int, string|float>>
     */
    public function departmentProfitabilityRows(IntelligenceScope $scope): array
    {
        if (! $this->aggregates->hasTable('job_cost_sheets')) {
            return [];
        }

        $rows = $this->profitabilityQuery($scope)
            ->join('production_job_cards', 'production_job_cards.id', '=', 'job_cost_sheets.production_job_card_id')
            ->selectRaw('production_job_cards.production_type as production_type')
            ->selectRaw('COALESCE(SUM(job_cost_sheets.revenue), 0) as revenue')
            ->selectRaw('COALESCE(SUM(job_cost_sheets.total_cost), 0) as total_cost')
            ->selectRaw('COALESCE(SUM(job_cost_sheets.gross_profit), 0) as gross_profit')
            ->selectRaw('COUNT(*) as job_count')
            ->groupBy('production_job_cards.production_type')
            ->orderByDesc('gross_profit')
            ->get();

        return $rows->map(function ($row) {
            $type = $row->production_type instanceof ProductionType
                ? $row->production_type->value
                : (ProductionType::tryFrom((string) $row->production_type)?->value ?? (string) $row->production_type);
            $revenue = (float) $row->revenue;
            $profit = (float) $row->gross_profit;
            $margin = $revenue > 0 ? round(($profit / $revenue) * 100, 1) : 0.0;

            return [
                str($type)->headline()->toString(),
                (int) $row->job_count,
                $this->aggregates->money($revenue),
                $this->aggregates->money((float) $row->total_cost),
                $this->aggregates->money($profit),
                $margin.'%',
            ];
        })->all();
    }

    /**
     * @return list<array<int, string|float>>
     */
    public function customerProfitabilityRows(IntelligenceScope $scope): array
    {
        if (! $this->aggregates->hasTable('job_cost_sheets')) {
            return [];
        }

        $rows = $this->profitabilityQuery($scope)
            ->join('production_job_cards', 'production_job_cards.id', '=', 'job_cost_sheets.production_job_card_id')
            ->leftJoin('customers', 'customers.id', '=', 'production_job_cards.customer_id')
            ->selectRaw('customers.company_name as customer_name')
            ->selectRaw('COALESCE(SUM(job_cost_sheets.revenue), 0) as revenue')
            ->selectRaw('COALESCE(SUM(job_cost_sheets.total_cost), 0) as total_cost')
            ->selectRaw('COALESCE(SUM(job_cost_sheets.gross_profit), 0) as gross_profit')
            ->selectRaw('COUNT(*) as job_count')
            ->groupBy('production_job_cards.customer_id', 'customers.company_name')
            ->orderByDesc('gross_profit')
            ->limit(100)
            ->get();

        return $rows->map(function ($row) {
            $revenue = (float) $row->revenue;
            $profit = (float) $row->gross_profit;
            $margin = $revenue > 0 ? round(($profit / $revenue) * 100, 1) : 0.0;

            return [
                $row->customer_name ?? __('Unknown'),
                (int) $row->job_count,
                $this->aggregates->money($revenue),
                $this->aggregates->money((float) $row->total_cost),
                $this->aggregates->money($profit),
                $margin.'%',
            ];
        })->all();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<JobCostSheet>
     */
    protected function profitabilityQuery(IntelligenceScope $scope): \Illuminate\Database\Eloquent\Builder
    {
        return JobCostSheet::query()
            ->where('job_cost_sheets.company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('job_cost_sheets.branch_id', $scope->branchId))
            ->where('job_cost_sheets.calculated_at', '>=', $scope->fromDate.' 00:00:00')
            ->where('job_cost_sheets.calculated_at', '<=', $scope->toDate.' 23:59:59');
    }
}
