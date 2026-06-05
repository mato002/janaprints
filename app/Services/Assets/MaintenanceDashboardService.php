<?php

namespace App\Services\Assets;

use App\Enums\MaintenancePriority;
use App\Enums\MaintenanceWorkOrderStatus;
use App\Models\Assets\AssetDowntimeRecord;
use App\Models\Assets\MaintenancePlan;
use App\Models\Assets\MaintenanceWorkOrder;
use App\Support\Platform\PlatformCacheService;
use Illuminate\Support\Collection;

class MaintenanceDashboardService
{
    public function __construct(
        protected PlatformCacheService $cache,
        protected MaintenancePlanService $plans,
        protected MaintenanceDowntimeService $downtime,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(int $companyId, ?int $branchId = null): array
    {
        $cacheKey = $branchId ? "{$companyId}:{$branchId}" : "{$companyId}:all";

        return $this->cache->remember('maintenance_dashboard', $cacheKey, function () use ($companyId, $branchId) {
            $base = MaintenanceWorkOrder::query()
                ->where('company_id', $companyId)
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId));

            $openStatuses = array_map(
                fn (MaintenanceWorkOrderStatus $s) => $s->value,
                array_filter(MaintenanceWorkOrderStatus::cases(), fn ($s) => $s->isActive() || $s === MaintenanceWorkOrderStatus::Open),
            );

            return [
                'open_work_orders' => (clone $base)->whereIn('status', $openStatuses)->count(),
                'completed_work_orders' => (clone $base)->where('status', MaintenanceWorkOrderStatus::Completed)->count(),
                'overdue_maintenance' => $this->plans->overdue($companyId, $branchId)->count(),
                'machines_under_maintenance' => (clone $base)
                    ->whereIn('status', [
                        MaintenanceWorkOrderStatus::InProgress->value,
                        MaintenanceWorkOrderStatus::Assigned->value,
                    ])
                    ->distinct('fixed_asset_id')
                    ->count('fixed_asset_id'),
                'downtime_hours' => round($this->downtime->totalDowntimeMinutes($companyId, $branchId) / 60, 1),
                'critical_failures' => (clone $base)
                    ->where('priority', MaintenancePriority::Critical->value)
                    ->whereIn('status', $openStatuses)
                    ->count(),
                'upcoming_maintenance' => $this->plans->upcomingSchedules($companyId, $branchId)->count(),
                'by_branch' => $this->byBranch($companyId, $branchId),
                'by_asset_type' => $this->byAssetType($companyId, $branchId),
                'recently_closed' => $this->recentlyClosed($companyId, $branchId),
                'critical_orders' => $this->criticalOrders($companyId, $branchId),
            ];
        }, config('platform.cache.maintenance_dashboard', 60));
    }

    protected function byBranch(int $companyId, ?int $branchId): Collection
    {
        if ($branchId) {
            return collect();
        }

        return MaintenanceWorkOrder::query()
            ->where('company_id', $companyId)
            ->selectRaw('branch_id, COUNT(*) as count')
            ->groupBy('branch_id')
            ->get();
    }

    protected function byAssetType(int $companyId, ?int $branchId): Collection
    {
        return MaintenanceWorkOrder::query()
            ->where('maintenance_work_orders.company_id', $companyId)
            ->when($branchId, fn ($q) => $q->where('maintenance_work_orders.branch_id', $branchId))
            ->join('fixed_assets', 'fixed_assets.id', '=', 'maintenance_work_orders.fixed_asset_id')
            ->join('asset_categories', 'asset_categories.id', '=', 'fixed_assets.asset_category_id')
            ->selectRaw('asset_categories.asset_type as asset_type, COUNT(*) as count')
            ->groupBy('asset_categories.asset_type')
            ->orderByDesc('count')
            ->get();
    }

    protected function recentlyClosed(int $companyId, ?int $branchId): Collection
    {
        return MaintenanceWorkOrder::query()
            ->where('company_id', $companyId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->where('status', MaintenanceWorkOrderStatus::Closed)
            ->with(['asset:id,asset_name,asset_number'])
            ->latest('completed_at')
            ->limit(8)
            ->get();
    }

    protected function criticalOrders(int $companyId, ?int $branchId): Collection
    {
        return MaintenanceWorkOrder::query()
            ->where('company_id', $companyId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->where('priority', MaintenancePriority::Critical->value)
            ->whereNotIn('status', [
                MaintenanceWorkOrderStatus::Completed->value,
                MaintenanceWorkOrderStatus::Closed->value,
                MaintenanceWorkOrderStatus::Cancelled->value,
            ])
            ->with(['asset:id,asset_name,asset_number'])
            ->latest('opened_at')
            ->limit(10)
            ->get();
    }
}
