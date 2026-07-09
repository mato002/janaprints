<?php

namespace App\Services\Assets;

use App\Enums\AssetType;
use App\Enums\MaintenancePriority;
use App\Enums\MaintenanceWorkOrderStatus;
use App\Models\Assets\AssetDowntimeRecord;
use App\Models\Assets\MaintenancePlan;
use App\Models\Assets\MaintenanceWorkOrder;
use App\Support\Assets\AssetSchema;
use App\Support\Platform\PlatformCacheService;
use Illuminate\Support\Facades\Route;

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
            if (! AssetSchema::hasTable('maintenance_work_orders')) {
                return $this->emptyDashboard();
            }

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

    /**
     * @return array<string, mixed>
     */
    protected function emptyDashboard(): array
    {
        return [
            'open_work_orders' => 0,
            'completed_work_orders' => 0,
            'overdue_maintenance' => 0,
            'machines_under_maintenance' => 0,
            'downtime_hours' => 0,
            'critical_failures' => 0,
            'upcoming_maintenance' => 0,
            'by_branch' => [],
            'by_asset_type' => [],
            'recently_closed' => [],
            'critical_orders' => [],
        ];
    }

    /**
     * @return list<array{branch_id: int, count: int}>
     */
    protected function byBranch(int $companyId, ?int $branchId): array
    {
        if ($branchId) {
            return [];
        }

        return MaintenanceWorkOrder::query()
            ->where('company_id', $companyId)
            ->selectRaw('branch_id, COUNT(*) as count')
            ->groupBy('branch_id')
            ->get()
            ->map(fn ($row) => [
                'branch_id' => (int) $row->branch_id,
                'count' => (int) $row->count,
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{asset_type: string, label: string, count: int}>
     */
    protected function byAssetType(int $companyId, ?int $branchId): array
    {
        return MaintenanceWorkOrder::query()
            ->where('maintenance_work_orders.company_id', $companyId)
            ->when($branchId, fn ($q) => $q->where('maintenance_work_orders.branch_id', $branchId))
            ->join('fixed_assets', 'fixed_assets.id', '=', 'maintenance_work_orders.fixed_asset_id')
            ->join('asset_categories', 'asset_categories.id', '=', 'fixed_assets.asset_category_id')
            ->selectRaw('asset_categories.asset_type as asset_type, COUNT(*) as count')
            ->groupBy('asset_categories.asset_type')
            ->orderByDesc('count')
            ->get()
            ->map(function ($row) {
                $assetType = $row->asset_type;
                $value = $assetType instanceof AssetType
                    ? $assetType->value
                    : (string) $assetType;

                return [
                    'asset_type' => $value,
                    'label' => AssetType::tryFrom($value)?->label()
                        ?? ucfirst(str_replace('_', ' ', $value)),
                    'count' => (int) $row->count,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return list<array{id: int, work_order_no: string, asset_name: string|null}>
     */
    protected function recentlyClosed(int $companyId, ?int $branchId): array
    {
        return MaintenanceWorkOrder::query()
            ->where('company_id', $companyId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->where('status', MaintenanceWorkOrderStatus::Closed)
            ->with(['asset:id,asset_name,asset_number'])
            ->latest('completed_at')
            ->limit(8)
            ->get()
            ->map(fn (MaintenanceWorkOrder $order) => $this->orderSummary($order))
            ->all();
    }

    /**
     * @return list<array{id: int, work_order_no: string, asset_name: string|null}>
     */
    protected function criticalOrders(int $companyId, ?int $branchId): array
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
            ->get()
            ->map(fn (MaintenanceWorkOrder $order) => $this->orderSummary($order))
            ->all();
    }

    /**
     * @return array{id: int, work_order_no: string, asset_name: string|null}
     */
    protected function orderSummary(MaintenanceWorkOrder $order): array
    {
        return [
            'public_id' => $order->public_id,
            'work_order_no' => $order->work_order_no,
            'asset_name' => $order->asset?->asset_name,
            'url' => Route::has('admin.assets.maintenance.work-orders.show')
                ? route('admin.assets.maintenance.work-orders.show', $order)
                : null,
        ];
    }
}
