<?php

namespace App\Services\Assets;

use App\Enums\ProductionMachineStatus;
use App\Models\Assets\MachineProfile;
use App\Support\Platform\PlatformCacheService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;

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
                ->with(['asset:id,public_id,asset_name,asset_number,branch_id', 'workCenter:id,public_id,name,fixed_asset_id']);

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
                'metrics' => $metrics,
            ];
        }, config('platform.cache.machines_dashboard', 60));
    }

    /**
     * Operational KPI strip for the unified machines page.
     *
     * @return list<array{key: string, label: string, value: string, hint: string, filter: array<string, string>}>
     */
    public function summaryStrip(int $companyId, ?int $branchId = null): array
    {
        $stats = $this->build($companyId, $branchId);

        return [
            [
                'key' => 'total',
                'label' => __('Total'),
                'value' => (string) ($stats['total_machines'] ?? 0),
                'hint' => __('Machines'),
                'filter' => [],
            ],
            [
                'key' => 'available',
                'label' => __('Available'),
                'value' => (string) ($stats['available_machines'] ?? 0),
                'hint' => __('Ready to run'),
                'filter' => ['status' => ProductionMachineStatus::Available->value],
            ],
            [
                'key' => 'running',
                'label' => __('Running'),
                'value' => (string) ($stats['running_machines'] ?? 0),
                'hint' => __('On jobs'),
                'filter' => ['status' => ProductionMachineStatus::Running->value],
            ],
            [
                'key' => 'maintenance',
                'label' => __('Maintenance'),
                'value' => (string) ($stats['maintenance_holds'] ?? 0),
                'hint' => __('Out of service'),
                'filter' => ['status' => ProductionMachineStatus::Maintenance->value],
            ],
            [
                'key' => 'utilization',
                'label' => __('Utilization'),
                'value' => (string) ($stats['utilization_percent'] ?? 0),
                'hint' => __('Fleet average'),
                'filter' => [],
            ],
        ];
    }

    /**
     * @param  Collection<int, MachineProfile>  $profiles
     * @return list<array{branch_id: int|string|null, count: int}>
     */
    protected function byBranch(Collection $profiles): array
    {
        return $profiles
            ->groupBy('branch_id')
            ->map(fn ($group, $branchId) => [
                'branch_id' => $branchId,
                'count' => $group->count(),
            ])
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, MachineProfile>  $profiles
     * @return list<array{machine_type: string, count: int}>
     */
    protected function byType(Collection $profiles): array
    {
        return $profiles
            ->groupBy(fn (MachineProfile $profile) => (string) $profile->machine_type)
            ->map(fn ($group, $type) => [
                'machine_type' => (string) $type,
                'count' => $group->count(),
            ])
            ->sortByDesc('count')
            ->values()
            ->all();
    }

    /**
     * @return list<array{fixed_asset_id: int, machine_code: string, asset_name: ?string}>
     */
    protected function recentlyAssigned(int $companyId, ?int $branchId): array
    {
        return MachineProfile::query()
            ->where('company_id', $companyId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->whereHas('jobAssignments', fn ($q) => $q->where('assigned_at', '>=', now()->subDays(30)))
            ->with(['asset:id,public_id,asset_name,asset_number', 'jobAssignments' => fn ($q) => $q->latest('assigned_at')->limit(1)])
            ->limit(8)
            ->get()
            ->map(fn (MachineProfile $profile) => [
                'machine_code' => $profile->machine_code,
                'asset_name' => $profile->asset?->asset_name,
                'url' => ($profile->asset && Route::has('admin.assets.machines.show'))
                    ? route('admin.assets.machines.show', $profile->asset)
                    : null,
            ])
            ->all();
    }
}
