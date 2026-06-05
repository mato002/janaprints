<?php

namespace App\Services\Assets;

use App\Enums\ProductionMachineStatus;
use App\Models\Assets\MachineProfile;
use App\Support\Platform\PlatformCacheService;
use Illuminate\Support\Collection;

class MachineDashboardService
{
    public function __construct(
        protected PlatformCacheService $cache,
        protected MachineCapacityService $capacity,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(int $companyId, ?int $branchId = null): array
    {
        $cacheKey = $branchId ? "{$companyId}:{$branchId}" : "{$companyId}:all";

        return $this->cache->remember('machines_dashboard', $cacheKey, function () use ($companyId, $branchId) {
            $query = MachineProfile::query()
                ->where('company_id', $companyId)
                ->with(['asset:id,asset_name,asset_number,branch_id', 'workCenter:id,name,fixed_asset_id']);

            if ($branchId) {
                $query->where('branch_id', $branchId);
            }

            $profiles = $query->get();
            $metrics = $this->capacity->metricsForProfiles($profiles);

            $statusCounts = [];
            foreach (ProductionMachineStatus::cases() as $status) {
                $statusCounts[$status->value] = $profiles->where('production_status', $status)->count();
            }

            $utilizations = collect($metrics)->pluck('current_utilization');
            $avgUtilization = $utilizations->isNotEmpty() ? round($utilizations->avg(), 1) : 0;
            $avgCapacity = $utilizations->isNotEmpty() ? round(100 - min(100, $avgUtilization), 1) : 100;

            return [
                'total_machines' => $profiles->count(),
                'available_machines' => $statusCounts[ProductionMachineStatus::Available->value] ?? 0,
                'running_machines' => $statusCounts[ProductionMachineStatus::Running->value] ?? 0,
                'offline_machines' => $statusCounts[ProductionMachineStatus::Offline->value] ?? 0,
                'maintenance_holds' => $statusCounts[ProductionMachineStatus::Maintenance->value] ?? 0,
                'utilization_percent' => $avgUtilization,
                'capacity_percent' => $avgCapacity,
                'by_branch' => $this->byBranch($profiles),
                'by_type' => $this->byType($profiles),
                'recently_assigned' => $this->recentlyAssigned($companyId, $branchId),
                'profiles' => $profiles,
                'metrics' => $metrics,
            ];
        }, config('platform.cache.machines_dashboard', 60));
    }

    /**
     * @param  Collection<int, MachineProfile>  $profiles
     * @return Collection<int, object>
     */
    protected function byBranch(Collection $profiles): Collection
    {
        return $profiles
            ->groupBy('branch_id')
            ->map(fn ($group, $branchId) => (object) [
                'branch_id' => $branchId,
                'count' => $group->count(),
            ])
            ->values();
    }

    /**
     * @param  Collection<int, MachineProfile>  $profiles
     * @return Collection<int, object>
     */
    protected function byType(Collection $profiles): Collection
    {
        return $profiles
            ->groupBy('machine_type')
            ->map(fn ($group, $type) => (object) [
                'machine_type' => $type,
                'count' => $group->count(),
            ])
            ->sortByDesc('count')
            ->values();
    }

    protected function recentlyAssigned(int $companyId, ?int $branchId): Collection
    {
        return MachineProfile::query()
            ->where('company_id', $companyId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->whereHas('jobAssignments', fn ($q) => $q->where('assigned_at', '>=', now()->subDays(30)))
            ->with(['asset:id,asset_name,asset_number', 'jobAssignments' => fn ($q) => $q->latest('assigned_at')->limit(1)])
            ->limit(8)
            ->get();
    }
}
