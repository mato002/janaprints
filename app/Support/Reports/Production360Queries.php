<?php

namespace App\Support\Reports;

use App\Enums\Dispatch\DeliveryNoteStatus;
use App\Enums\ProductionJobCardStatus;
use App\Enums\ProductionQueueStatus;
use App\Enums\ProductionType;
use App\Enums\QualityCheckResult;
use App\Models\Branch;
use App\Models\Dispatch\DeliveryNote;
use App\Models\Inventory\ProductionMaterialConsumption;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionQueue;
use App\Models\Production\QualityCheck;
use App\Models\Production\WorkCenter;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class Production360Queries
{
    /**
     * @var list<array{code: string, label: string}>
     */
    public const DEPARTMENTS = [
        ['code' => 'DESIGN', 'label' => 'Design'],
        ['code' => 'PREPRESS', 'label' => 'Prepress'],
        ['code' => 'DIGITAL', 'label' => 'Digital'],
        ['code' => 'OFFSET', 'label' => 'Offset'],
        ['code' => 'LARGE_FORMAT', 'label' => 'Large Format'],
        ['code' => 'PACKAGING', 'label' => 'Packaging'],
        ['code' => 'FINISHING', 'label' => 'Finishing'],
    ];

    public function __construct(
        protected IntelligenceAggregateQueries $queries,
    ) {}

    /**
     * @return Builder<ProductionJobCard>
     */
    public function jobs(IntelligenceScope $scope): Builder
    {
        return $this->queries->scoped(ProductionJobCard::class, $scope);
    }

    public function averageDelayDays(IntelligenceScope $scope): ?float
    {
        if (! $this->queries->hasTable('production_job_cards')) {
            return null;
        }

        $asOf = Carbon::parse($scope->toDate);
        $delays = $this->delayedJobsQuery($scope)
            ->get(['planned_end_date'])
            ->map(fn (ProductionJobCard $job) => max(0, $job->planned_end_date?->diffInDays($asOf) ?? 0));

        if ($delays->isEmpty()) {
            return 0.0;
        }

        return round($delays->avg(), 1);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function mostDelayedJobs(IntelligenceScope $scope, int $limit = 10): array
    {
        if (! $this->queries->hasTable('production_job_cards')) {
            return [];
        }

        $asOf = Carbon::parse($scope->toDate);

        return $this->delayedJobsQuery($scope)
            ->with(['customer:id,company_name', 'branch:id,name'])
            ->orderBy('planned_end_date')
            ->limit($limit)
            ->get()
            ->map(function (ProductionJobCard $job) use ($asOf) {
                $days = max(0, $job->planned_end_date?->diffInDays($asOf) ?? 0);

                return [
                    'cells' => [
                        $job->job_card_number,
                        $job->customer?->company_name ?? '—',
                        $job->branch?->name ?? '—',
                        (string) $days,
                        $job->planned_end_date?->format('Y-m-d') ?? '—',
                    ],
                    'url' => $this->jobUrl($job),
                ];
            })
            ->sortByDesc(fn (array $row) => (int) $row['cells'][3])
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function mostDelayedDepartments(IntelligenceScope $scope, int $limit = 7): array
    {
        if (! $this->queries->hasTable('production_queues') || ! $this->queries->hasTable('work_centers')) {
            return [];
        }

        $asOf = $scope->toDate;
        $delayedJobIds = $this->delayedJobsQuery($scope)->pluck('id');

        if ($delayedJobIds->isEmpty()) {
            return collect(self::DEPARTMENTS)
                ->map(fn (array $dept) => ['cells' => [__($dept['label']), '0'], 'url' => null])
                ->all();
        }

        $counts = ProductionQueue::query()
            ->join('work_centers', 'work_centers.id', '=', 'production_queues.work_center_id')
            ->where('production_queues.company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('production_queues.branch_id', $scope->branchId))
            ->whereIn('production_job_card_id', $delayedJobIds)
            ->select('work_centers.code', DB::raw('COUNT(DISTINCT production_job_card_id) as delayed_jobs'))
            ->groupBy('work_centers.code')
            ->pluck('delayed_jobs', 'code');

        return collect(self::DEPARTMENTS)
            ->map(fn (array $dept) => [
                'cells' => [__($dept['label']), (string) ((int) ($counts[$dept['code']] ?? 0))],
                'url' => Route::has('admin.production.work-centers.index')
                    ? route('admin.production.work-centers.index', ['search' => $dept['code']])
                    : null,
            ])
            ->sortByDesc(fn (array $row) => (int) $row['cells'][1])
            ->take($limit)
            ->values()
            ->all();
    }

    /**
     * @return array{pass_rate: float, fail_rate: float, rework_rate: float, hold_rate: float, total_checks: int}
     */
    public function qualityRates(IntelligenceScope $scope): array
    {
        if (! $this->queries->hasTable('quality_checks')) {
            return ['pass_rate' => 0.0, 'fail_rate' => 0.0, 'rework_rate' => 0.0, 'hold_rate' => 0.0, 'total_checks' => 0];
        }

        $checks = QualityCheck::query()
            ->where('company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
            ->whereDate('checked_at', '>=', $scope->fromDate)
            ->whereDate('checked_at', '<=', $scope->toDate);

        $passed = (clone $checks)->where('result', QualityCheckResult::Passed)->count();
        $failed = (clone $checks)->where('result', QualityCheckResult::Failed)->count();
        $rework = (clone $checks)->where('result', QualityCheckResult::ReworkRequired)->count();
        $total = $passed + $failed + $rework;

        $holdJobs = $this->queries->hasTable('production_job_cards')
            ? (int) $this->jobs($scope)->where('status', ProductionJobCardStatus::OnHold)->count()
            : 0;
        $activeJobs = max(1, $this->queries->countActiveJobs($scope));

        return [
            'pass_rate' => $total > 0 ? round(($passed / $total) * 100, 1) : 0.0,
            'fail_rate' => $total > 0 ? round(($failed / $total) * 100, 1) : 0.0,
            'rework_rate' => $total > 0 ? round(($rework / $total) * 100, 1) : 0.0,
            'hold_rate' => round(($holdJobs / $activeJobs) * 100, 1),
            'total_checks' => $total,
        ];
    }

    /**
     * @return array{lines: int, total_cost: float, top_materials: list<array{cells: list<string>, url: null}>}
     */
    public function materialConsumption(IntelligenceScope $scope): array
    {
        if (! $this->queries->hasTable('production_material_consumptions')) {
            return ['lines' => 0, 'total_cost' => 0.0, 'top_materials' => []];
        }

        $base = ProductionMaterialConsumption::query()
            ->where('production_material_consumptions.company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('production_material_consumptions.branch_id', $scope->branchId))
            ->whereDate('production_material_consumptions.consumed_at', '>=', $scope->fromDate)
            ->whereDate('production_material_consumptions.consumed_at', '<=', $scope->toDate);

        $totalCost = (float) (clone $base)
            ->selectRaw('COALESCE(SUM(quantity * unit_cost), 0) as total')
            ->value('total');

        $topMaterials = (clone $base)
            ->join('inventory_items', 'inventory_items.id', '=', 'production_material_consumptions.inventory_item_id')
            ->select(
                'inventory_items.item_name',
                DB::raw('SUM(production_material_consumptions.quantity) as total_qty'),
                DB::raw('SUM(production_material_consumptions.quantity * production_material_consumptions.unit_cost) as total_cost'),
            )
            ->groupBy('inventory_items.id', 'inventory_items.item_name')
            ->orderByDesc('total_cost')
            ->limit(8)
            ->get()
            ->map(fn ($row) => [
                'cells' => [
                    $row->item_name,
                    number_format((float) $row->total_qty, 2),
                    $this->queries->money((float) $row->total_cost),
                ],
                'url' => null,
            ])
            ->all();

        return [
            'lines' => (int) (clone $base)->count(),
            'total_cost' => $totalCost,
            'top_materials' => $topMaterials,
        ];
    }

    /**
     * @return list<array{cells: list<string>, url: null}>
     */
    public function productMix(IntelligenceScope $scope): array
    {
        return $this->materialConsumption($scope)['top_materials'] ?? [];
    }

    /**
     * @return list<array{cells: list<string>, url: null}>
     */
    public function jobTypeMix(IntelligenceScope $scope): array
    {
        if (! $this->queries->hasTable('production_job_cards')) {
            return [];
        }

        return $this->jobs($scope)
            ->whereDate('created_at', '>=', $scope->fromDate)
            ->whereDate('created_at', '<=', $scope->toDate)
            ->select('production_type', DB::raw('COUNT(*) as cnt'))
            ->groupBy('production_type')
            ->orderByDesc('cnt')
            ->get()
            ->map(function ($row) {
                $raw = $row->production_type;
                $type = $raw instanceof ProductionType
                    ? $raw->value
                    : (ProductionType::tryFrom((string) $raw)?->value ?? ((string) $raw ?: 'unknown'));

                return [
                    'cells' => [__(ucfirst(str_replace('_', ' ', $type))), (string) $row->cnt],
                    'url' => null,
                ];
            })
            ->all();
    }

    /**
     * @return array{work_center: float, machine: float, department: float, departments: list<array{cells: list<string>, url: ?string}>}
     */
    public function capacityMetrics(IntelligenceScope $scope): array
    {
        if (! $this->queries->hasTable('work_centers')) {
            return ['work_center' => 0.0, 'machine' => 0.0, 'department' => 0.0, 'departments' => []];
        }

        $capacity = max(1, (int) config('production.scheduling.default_work_center_capacity', 5));
        $centers = WorkCenter::query()
            ->where('company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
            ->where('is_active', true)
            ->get(['id', 'name', 'code']);

        if ($centers->isEmpty()) {
            return ['work_center' => 0.0, 'machine' => 0.0, 'department' => 0.0, 'departments' => []];
        }

        $activeStatuses = [
            ProductionQueueStatus::Waiting->value,
            ProductionQueueStatus::Assigned->value,
            ProductionQueueStatus::InProgress->value,
        ];

        $activeByCenter = ProductionQueue::query()
            ->where('company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
            ->whereIn('work_center_id', $centers->pluck('id'))
            ->whereIn('status', $activeStatuses)
            ->select('work_center_id', DB::raw('COUNT(DISTINCT production_job_card_id) as active_jobs'))
            ->groupBy('work_center_id')
            ->pluck('active_jobs', 'work_center_id');

        $utilizations = $centers->map(fn (WorkCenter $center) => [
            'center' => $center,
            'utilization' => (int) round(((int) ($activeByCenter[$center->id] ?? 0) / $capacity) * 100),
        ]);

        $avgUtil = round($utilizations->avg('utilization') ?: 0, 1);

        $departmentRows = collect(self::DEPARTMENTS)->map(function (array $dept) use ($centers, $activeByCenter, $capacity) {
            $deptCenters = $centers->where('code', $dept['code']);
            $utilization = 0;

            foreach ($deptCenters as $center) {
                $jobs = (int) ($activeByCenter[$center->id] ?? 0);
                $utilization = max($utilization, (int) round(($jobs / $capacity) * 100));
            }

            return [
                'cells' => [__($dept['label']), $utilization.'%'],
                'url' => Route::has('admin.production.work-centers.index')
                    ? route('admin.production.work-centers.index', ['search' => $dept['code']])
                    : null,
            ];
        })->all();

        return [
            'work_center' => $avgUtil,
            'machine' => $avgUtil,
            'department' => $avgUtil,
            'departments' => $departmentRows,
        ];
    }

    /**
     * @return array{on_time: int, late: int, throughput: int, on_time_rate: float}
     */
    public function dispatchMetrics(IntelligenceScope $scope): array
    {
        if (! $this->queries->hasTable('delivery_notes')) {
            return ['on_time' => 0, 'late' => 0, 'throughput' => 0, 'on_time_rate' => 0.0];
        }

        $delivered = DeliveryNote::query()
            ->where('company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
            ->where('status', DeliveryNoteStatus::Delivered)
            ->whereDate('delivered_at', '>=', $scope->fromDate)
            ->whereDate('delivered_at', '<=', $scope->toDate)
            ->get(['delivery_date', 'delivered_at']);

        $onTime = $delivered->filter(function (DeliveryNote $note) {
            if (! $note->delivery_date || ! $note->delivered_at) {
                return true;
            }

            return $note->delivered_at->startOfDay()->lte($note->delivery_date->startOfDay());
        })->count();

        $throughput = $delivered->count();
        $late = max(0, $throughput - $onTime);

        return [
            'on_time' => $onTime,
            'late' => $late,
            'throughput' => $throughput,
            'on_time_rate' => $throughput > 0 ? round(($onTime / $throughput) * 100, 1) : 0.0,
        ];
    }

    /**
     * @return list<array{label: string, value: int, max: int}>
     */
    public function completedJobsTrend(IntelligenceScope $scope): array
    {
        return $this->dailyTrend($scope, function (string $date) use ($scope) {
            return (int) $this->jobs($scope)
                ->where('status', ProductionJobCardStatus::Completed)
                ->where(function ($q) use ($date) {
                    $q->whereDate('actual_end_date', $date)
                        ->orWhere(function ($q2) use ($date) {
                            $q2->whereNull('actual_end_date')->whereDate('updated_at', $date);
                        });
                })
                ->count();
        });
    }

    /**
     * @return list<array{label: string, value: int, max: int}>
     */
    public function delayTrend(IntelligenceScope $scope): array
    {
        return $this->dailyTrend($scope, function (string $date) use ($scope) {
            return (int) $this->jobs($scope)
                ->whereNotIn('status', [
                    ProductionJobCardStatus::Completed,
                    ProductionJobCardStatus::ReadyForDispatch,
                    ProductionJobCardStatus::Cancelled,
                ])
                ->whereNotNull('planned_end_date')
                ->whereDate('planned_end_date', '<', $date)
                ->count();
        });
    }

    /**
     * @return list<array{label: string, value: int, max: int}>
     */
    public function deliveryTrend(IntelligenceScope $scope): array
    {
        if (! $this->queries->hasTable('delivery_notes')) {
            return [];
        }

        return $this->dailyTrend($scope, function (string $date) use ($scope) {
            return (int) DeliveryNote::query()
                ->where('company_id', $scope->companyId)
                ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
                ->where('status', DeliveryNoteStatus::Delivered)
                ->whereDate('delivered_at', $date)
                ->count();
        });
    }

    /**
     * @return list<array{branch: string, score: float, completed: int, delayed: int}>
     */
    public function branchPerformance(IntelligenceScope $scope): array
    {
        if (! $this->queries->hasTable('production_job_cards')) {
            return [];
        }

        return Branch::query()
            ->where('company_id', $scope->companyId)
            ->where('is_active', true)
            ->when($scope->branchId, fn ($q) => $q->where('id', $scope->branchId))
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(function (Branch $branch) use ($scope) {
                $scoped = new IntelligenceScope($scope->companyId, $branch->id, $scope->fromDate, $scope->toDate);
                $completed = $this->queries->countCompletedJobsInPeriod($scoped);
                $delayed = $this->queries->countDelayedJobs($scoped);
                $total = $completed + $this->queries->countActiveJobs($scoped);
                $score = $total > 0 ? round(($completed / $total) * 100, 1) : 0.0;

                return [
                    'branch' => $branch->name,
                    'score' => $score,
                    'completed' => $completed,
                    'delayed' => $delayed,
                ];
            })
            ->sortByDesc('score')
            ->values()
            ->all();
    }

    /**
     * @return list<array{cells: list<string>, url: ?string}>
     */
    public function branchComparisonRows(IntelligenceScope $scope): array
    {
        return collect($this->branchPerformance($scope))
            ->map(fn (array $row) => [
                'cells' => [
                    $row['branch'],
                    (string) $row['completed'],
                    (string) $row['delayed'],
                    $row['score'].'%',
                ],
                'url' => null,
            ])
            ->all();
    }

    /**
     * @param  callable(string): int  $counter
     * @return list<array{label: string, value: int, max: int}>
     */
    protected function dailyTrend(IntelligenceScope $scope, callable $counter): array
    {
        $period = CarbonPeriod::create($scope->fromDate, $scope->toDate);
        $points = collect($period)->map(fn (Carbon $day) => [
            'label' => $day->format('M j'),
            'value' => $counter($day->toDateString()),
        ]);

        $max = max(1, (int) $points->max('value'));

        return $points
            ->map(fn (array $point) => [...$point, 'max' => $max])
            ->values()
            ->all();
    }

    /**
     * @return Builder<ProductionJobCard>
     */
    protected function delayedJobsQuery(IntelligenceScope $scope): Builder
    {
        return $this->jobs($scope)
            ->whereNotIn('status', [
                ProductionJobCardStatus::Completed,
                ProductionJobCardStatus::ReadyForDispatch,
                ProductionJobCardStatus::Cancelled,
            ])
            ->whereNotNull('planned_end_date')
            ->whereDate('planned_end_date', '<', $scope->toDate);
    }

    protected function jobUrl(ProductionJobCard $job): ?string
    {
        return Route::has('admin.production.job-cards.show')
            ? route('admin.production.job-cards.show', $job)
            : null;
    }
}
