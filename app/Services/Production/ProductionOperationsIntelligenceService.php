<?php

namespace App\Services\Production;

use App\Enums\Dispatch\DeliveryNoteStatus;
use App\Enums\ProductionJobCardStatus;
use App\Enums\ProductionQueueStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Dispatch\DeliveryNote;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionQueue;
use App\Models\Sales\SalesOrder;
use App\Support\Platform\PlatformCacheService;
use App\Support\Reports\IntelligenceScope;
use App\Support\Reports\OperationalRegisterQueries;
use App\Support\Reports\OperationalRegisterScope;
use App\Support\Reports\ProductionReportQueries;
use Illuminate\Support\Facades\DB;

/**
 * Aggregates live production intelligence from existing query layers — no duplicate business tables.
 */
class ProductionOperationsIntelligenceService
{
    public function __construct(
        protected ProductionReportQueries $productionReports,
        protected OperationalRegisterQueries $operationalRegisters,
        protected PlatformCacheService $cache,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function liveExecutiveMetrics(?int $companyId = null, ?int $branchId = null): array
    {
        $companyId ??= tenant()->companyId();
        $branchId ??= tenant()->branchId();

        if (! $companyId) {
            return [];
        }

        $cacheKey = $companyId.':'.($branchId ?? 0).':'.today()->toDateString();

        return $this->cache->remember('production_ops_intel', $cacheKey, function () use ($companyId, $branchId) {
            $scope = new IntelligenceScope(
                companyId: $companyId,
                branchId: $branchId,
                fromDate: now()->startOfMonth()->toDateString(),
                toDate: today()->toDateString(),
            );
            $todayScope = new IntelligenceScope(
                companyId: $companyId,
                branchId: $branchId,
                fromDate: today()->toDateString(),
                toDate: today()->toDateString(),
            );
            $registerScope = new OperationalRegisterScope(
                companyId: $companyId,
                branchId: $branchId,
                fromDate: today()->toDateString(),
                toDate: today()->toDateString(),
            );

            $jobBase = ProductionJobCard::query()
                ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId));

            $queueBase = ProductionQueue::query()
                ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId));

            $activeStatuses = [
                ProductionJobCardStatus::Queued,
                ProductionJobCardStatus::InProduction,
                ProductionJobCardStatus::QualityCheck,
                ProductionJobCardStatus::OnHold,
                ProductionJobCardStatus::Outsourced,
                ProductionJobCardStatus::Rework,
            ];

            $terminal = [
                ProductionJobCardStatus::Completed,
                ProductionJobCardStatus::ReadyForDispatch,
                ProductionJobCardStatus::Cancelled,
            ];

            $registerKpis = $this->operationalRegisters->executiveKpis($registerScope);

            $releasedToday = (clone $jobBase)
                ->whereDate('created_at', today())
                ->count();

            $deliveredToday = DeliveryNote::query()
                ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->where('status', DeliveryNoteStatus::Delivered)
                ->whereDate('updated_at', today())
                ->count();

            $dueToday = (clone $jobBase)
                ->whereNotIn('status', $terminal)
                ->where(function ($q) {
                    $q->whereDate('required_date', today())
                        ->orWhereDate('planned_end_date', today());
                })
                ->count();

            $backlog = (clone $jobBase)->whereIn('status', $activeStatuses)->count();

            $totalJobs = max(1, (clone $jobBase)->whereDate('created_at', '>=', now()->startOfMonth())->count());
            $lateJobs = (clone $jobBase)
                ->whereNotIn('status', $terminal)
                ->whereNotNull('required_date')
                ->whereDate('required_date', '<', today())
                ->count();

            $topCustomers = (clone $jobBase)
                ->whereDate('created_at', '>=', now()->startOfMonth())
                ->select('customer_id', DB::raw('count(*) as job_count'))
                ->groupBy('customer_id')
                ->orderByDesc('job_count')
                ->limit(5)
                ->with('customer:id,company_name')
                ->get()
                ->map(fn ($row) => [
                    'customer' => $row->customer?->company_name ?? __('Unknown'),
                    'jobs' => (int) $row->job_count,
                ])
                ->values()
                ->all();

            $mostDelayed = (clone $jobBase)
                ->whereNotIn('status', $terminal)
                ->whereNotNull('required_date')
                ->whereDate('required_date', '<', today())
                ->orderBy('required_date')
                ->limit(5)
                ->with('customer:id,company_name')
                ->get(['id', 'job_card_number', 'customer_id', 'required_date', 'status'])
                ->map(fn (ProductionJobCard $job) => [
                    'job_number' => $job->job_card_number,
                    'customer' => $job->customer?->company_name,
                    'required_date' => $job->required_date?->toDateString(),
                    'days_late' => $job->required_date?->diffInDays(today()),
                ])
                ->values()
                ->all();

            $outsourceCount = (clone $jobBase)->where('status', ProductionJobCardStatus::Outsourced)->count();
            $internalCount = (clone $jobBase)->whereIn('status', $activeStatuses)->where('status', '!=', ProductionJobCardStatus::Outsourced)->count();

            return [
                'sales_today' => $registerKpis['sales_today'] ?? 0,
                'revenue_today' => $registerKpis['revenue_today'] ?? 0,
                'revenue_month' => $registerKpis['sales_today'] ?? 0,
                'jobs_received_today' => $releasedToday,
                'jobs_released_today' => $releasedToday,
                'jobs_running' => $registerKpis['jobs_running'] ?? 0,
                'jobs_waiting' => $registerKpis['jobs_waiting'] ?? 0,
                'jobs_completed_today' => $registerKpis['jobs_completed_today'] ?? 0,
                'jobs_delivered_today' => $deliveredToday,
                'jobs_overdue' => $registerKpis['jobs_overdue'] ?? 0,
                'jobs_due_today' => $dueToday,
                'production_backlog' => $backlog,
                'avg_turnaround_days' => $this->productionReports->averageTurnaroundDays($scope),
                'late_percentage' => round(($lateJobs / $totalJobs) * 100, 1),
                'outsource_percentage' => $backlog > 0 ? round(($outsourceCount / $backlog) * 100, 1) : 0,
                'internal_vs_outsourced' => [
                    'internal' => $internalCount,
                    'outsourced' => $outsourceCount,
                ],
                'machine_utilisation' => $registerKpis['machine_utilisation'] ?? null,
                'department_utilisation' => $registerKpis['department_utilisation'] ?? null,
                'operator_productivity' => $registerKpis['operator_productivity'] ?? null,
                'department_throughput' => $this->productionReports->departmentThroughput($todayScope),
                'daily_throughput' => $this->productionReports->throughputByDay($todayScope),
                'dispatch_metrics' => $this->productionReports->dispatchMetrics($todayScope),
                'top_customers' => $topCustomers,
                'most_delayed_jobs' => $mostDelayed,
                'outsourced_jobs' => $outsourceCount,
            ];
        }, config('platform.cache.dashboard', 60));
    }

    /**
     * @return array<string, mixed>
     */
    public function analytics(?int $companyId = null, ?int $branchId = null): array
    {
        $companyId ??= tenant()->companyId();
        $branchId ??= tenant()->branchId();

        $scope = new IntelligenceScope(
            companyId: $companyId,
            branchId: $branchId,
            fromDate: now()->startOfMonth()->toDateString(),
            toDate: today()->toDateString(),
        );

        return [
            'avg_production_time_days' => $this->productionReports->averageTurnaroundDays($scope),
            'avg_dispatch_time' => $this->productionReports->dispatchMetrics($scope),
            'department_utilisation' => $this->productionReports->departmentThroughput($scope),
            'machine_utilisation' => $this->productionReports->machineUtilization($scope),
            'daily_throughput' => $this->productionReports->throughputByDay($scope),
            'monthly_throughput' => $this->productionReports->throughputByDay($scope),
            'quality_rates' => $this->productionReports->qualityRates($scope),
            'fulfilment_metrics' => $this->productionReports->fulfilmentMetrics($scope),
        ];
    }
}
