<?php

namespace App\Services\Assets;

use App\Enums\FixedAssetStatus;
use App\Enums\MachineAvailabilityState;
use App\Enums\ProductionMachineStatus;
use App\Models\Assets\MachineProfile;

class MachineAvailabilityService
{
    public function __construct(
        protected MachineCapacityService $capacity,
        protected MaintenanceBlockingService $maintenanceBlocking,
    ) {}

    /**
     * @return array{state: MachineAvailabilityState, label: string, reason: ?string}
     */
    public function evaluate(MachineProfile $profile): array
    {
        $asset = $profile->asset;

        if ($profile->production_status === ProductionMachineStatus::Retired) {
            return $this->unavailable(__('Machine is retired.'));
        }

        if ($profile->production_status === ProductionMachineStatus::Offline) {
            return $this->unavailable(__('Machine is offline.'));
        }

        if ($asset && $this->maintenanceBlocking->assetBlocksProduction($asset)) {
            return $this->unavailable($this->maintenanceBlocking->blockingReason($asset) ?? __('Asset is blocked for maintenance.'));
        }

        if ($profile->production_status === ProductionMachineStatus::Maintenance) {
            return $this->unavailable(__('Machine is under maintenance hold.'));
        }

        if ($asset && in_array($asset->status, [FixedAssetStatus::Disposed, FixedAssetStatus::Retired, FixedAssetStatus::WrittenOff], true)) {
            return $this->unavailable(__('Asset is not operational.'));
        }

        $metrics = $this->capacity->profileMetrics($profile);

        if ($metrics['capacity_remaining'] <= 0 && $metrics['shift_capacity'] > 0) {
            return [
                'state' => MachineAvailabilityState::LimitedCapacity,
                'label' => MachineAvailabilityState::LimitedCapacity->label(),
                'reason' => __('Capacity limit reached.'),
            ];
        }

        if ($metrics['capacity_percent'] >= 90 && $metrics['shift_capacity'] > 0) {
            return [
                'state' => MachineAvailabilityState::LimitedCapacity,
                'label' => MachineAvailabilityState::LimitedCapacity->label(),
                'reason' => __('Approaching capacity limit.'),
            ];
        }

        if (! $profile->production_status->acceptsJobs()) {
            return $this->unavailable(__('Machine status does not accept jobs.'));
        }

        return [
            'state' => MachineAvailabilityState::Available,
            'label' => MachineAvailabilityState::Available->label(),
            'reason' => null,
        ];
    }

    /**
     * @return array{state: MachineAvailabilityState, label: string, reason: ?string}
     */
    protected function unavailable(string $reason): array
    {
        return [
            'state' => MachineAvailabilityState::Unavailable,
            'label' => MachineAvailabilityState::Unavailable->label(),
            'reason' => $reason,
        ];
    }
}
