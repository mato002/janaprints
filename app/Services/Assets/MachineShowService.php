<?php

namespace App\Services\Assets;

use App\Enums\ProductionJobCardStatus;
use App\Models\Assets\FixedAsset;
use App\Models\Production\WorkCenter;

class MachineShowService
{
    public function __construct(
        protected MachineCapacityService $capacity,
        protected MachineAvailabilityService $availability,
        protected MachineQueueReadinessService $queueReadiness,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(FixedAsset $asset): array
    {
        $profile = $asset->machineProfile;
        abort_unless($profile, 404);

        $profile->loadMissing([
            'workCenter:id,name,code,fixed_asset_id',
            'asset.category:id,name',
            'asset.branch:id,name',
        ]);

        $capacity = $this->capacity->profileMetrics($profile);
        $availability = $this->availability->evaluate($profile);
        $queueReadiness = $this->queueReadiness->readiness($profile);

        $assignedJobs = $asset->assignedJobCards()
            ->with(['customer:id,company_name'])
            ->whereNotIn('status', [
                ProductionJobCardStatus::Completed->value,
                ProductionJobCardStatus::Cancelled->value,
            ])
            ->latest()
            ->limit(15)
            ->get(['id', 'job_card_number', 'status', 'customer_id', 'assigned_machine_asset_id']);

        $timeline = $asset->machineTimelineEntries()
            ->with('user:id,name')
            ->limit(30)
            ->get();

        $workCenters = WorkCenter::query()
            ->forTenant()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'fixed_asset_id']);

        return [
            'asset' => $asset,
            'profile' => $profile,
            'capacity' => $capacity,
            'availability' => $availability,
            'queue_readiness' => $queueReadiness,
            'assigned_jobs' => $assignedJobs,
            'timeline' => $timeline,
            'work_centers' => $workCenters,
        ];
    }
}
