<?php

namespace App\Services\Assets;

use App\Enums\ProductionJobCardStatus;
use App\Models\Assets\MachineProfile;
use App\Models\Production\ProductionJobCard;
use Illuminate\Support\Collection;

class MachineCapacityService
{
    /**
     * @return array{
     *     hourly_capacity: float,
     *     daily_capacity: float,
     *     shift_capacity: float,
     *     monthly_capacity: float,
     *     current_utilization: float,
     *     assigned_jobs: int,
     *     capacity_remaining: float,
     *     capacity_percent: float
     * }
     */
    public function profileMetrics(MachineProfile $profile): array
    {
        $assignedJobs = $this->activeJobCount($profile->fixed_asset_id);
        $shiftCapacity = max(0, (float) $profile->shift_capacity);
        $hourlyCapacity = max(0, (float) $profile->hourly_capacity);
        $dailyCapacity = max(0, (float) $profile->daily_capacity);
        $monthlyCapacity = max(0, (float) $profile->monthly_capacity);

        $effectiveCapacity = $shiftCapacity > 0 ? $shiftCapacity : ($dailyCapacity > 0 ? $dailyCapacity : $hourlyCapacity);
        $utilization = $effectiveCapacity > 0
            ? min(100, round(($assignedJobs / $effectiveCapacity) * 100, 2))
            : (float) $profile->current_utilization;

        $remaining = max(0, $effectiveCapacity - $assignedJobs);

        return [
            'hourly_capacity' => $hourlyCapacity,
            'daily_capacity' => $dailyCapacity,
            'shift_capacity' => $shiftCapacity,
            'monthly_capacity' => $monthlyCapacity,
            'current_utilization' => $utilization,
            'assigned_jobs' => $assignedJobs,
            'capacity_remaining' => $remaining,
            'capacity_percent' => $utilization,
        ];
    }

    public function syncUtilization(MachineProfile $profile): MachineProfile
    {
        $metrics = $this->profileMetrics($profile);
        $profile->update(['current_utilization' => $metrics['current_utilization']]);

        return $profile->fresh();
    }

    /**
     * @param  Collection<int, MachineProfile>  $profiles
     * @return array<int, array<string, mixed>>
     */
    public function metricsForProfiles(Collection $profiles): array
    {
        $assetIds = $profiles->pluck('fixed_asset_id');
        $jobCounts = ProductionJobCard::query()
            ->whereIn('assigned_machine_asset_id', $assetIds)
            ->whereNotIn('status', [
                ProductionJobCardStatus::Completed->value,
                ProductionJobCardStatus::Cancelled->value,
                ProductionJobCardStatus::ReadyForDispatch->value,
            ])
            ->selectRaw('assigned_machine_asset_id, COUNT(*) as job_count')
            ->groupBy('assigned_machine_asset_id')
            ->pluck('job_count', 'assigned_machine_asset_id');

        $result = [];

        foreach ($profiles as $profile) {
            $assignedJobs = (int) ($jobCounts[$profile->fixed_asset_id] ?? 0);
            $effectiveCapacity = (float) ($profile->shift_capacity ?: $profile->daily_capacity ?: $profile->hourly_capacity);
            $utilization = $effectiveCapacity > 0
                ? min(100, round(($assignedJobs / $effectiveCapacity) * 100, 2))
                : (float) $profile->current_utilization;

            $result[$profile->id] = [
                'hourly_capacity' => (float) $profile->hourly_capacity,
                'daily_capacity' => (float) $profile->daily_capacity,
                'shift_capacity' => (float) $profile->shift_capacity,
                'monthly_capacity' => (float) $profile->monthly_capacity,
                'current_utilization' => $utilization,
                'assigned_jobs' => $assignedJobs,
                'jobs_waiting' => $assignedJobs,
                'capacity_remaining' => max(0, $effectiveCapacity - $assignedJobs),
                'capacity_percent' => $utilization,
            ];
        }

        return $result;
    }

    protected function activeJobCount(int $fixedAssetId): int
    {
        return ProductionJobCard::query()
            ->where('assigned_machine_asset_id', $fixedAssetId)
            ->whereNotIn('status', [
                ProductionJobCardStatus::Completed->value,
                ProductionJobCardStatus::Cancelled->value,
                ProductionJobCardStatus::ReadyForDispatch->value,
            ])
            ->count();
    }
}
