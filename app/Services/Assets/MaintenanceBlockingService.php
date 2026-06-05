<?php

namespace App\Services\Assets;

use App\Enums\FixedAssetStatus;
use App\Enums\MaintenanceType;
use App\Enums\MaintenanceWorkOrderStatus;
use App\Enums\ProductionMachineStatus;
use App\Models\Assets\AssetDowntimeRecord;
use App\Models\Assets\FixedAsset;
use App\Models\Assets\MaintenanceWorkOrder;

class MaintenanceBlockingService
{
    public function assetBlocksProduction(FixedAsset $asset): bool
    {
        if ($asset->status === FixedAssetStatus::UnderMaintenance) {
            return true;
        }

        if ($asset->machineProfile?->production_status === ProductionMachineStatus::Maintenance) {
            return true;
        }

        if ($this->hasActiveDowntime($asset->id)) {
            return true;
        }

        return $this->hasBlockingWorkOrder($asset->id);
    }

    public function blockingReason(FixedAsset $asset): ?string
    {
        if ($asset->status === FixedAssetStatus::UnderMaintenance) {
            return __('Asset is under maintenance.');
        }

        if ($asset->machineProfile?->production_status === ProductionMachineStatus::Maintenance) {
            return __('Machine is under maintenance hold.');
        }

        if ($this->hasActiveDowntime($asset->id)) {
            return __('Asset has active downtime.');
        }

        $order = $this->blockingWorkOrderQuery($asset->id)->first();

        return $order
            ? __('Active maintenance work order :no blocks production.', ['no' => $order->work_order_no])
            : null;
    }

    protected function hasActiveDowntime(int $assetId): bool
    {
        return AssetDowntimeRecord::query()
            ->where('fixed_asset_id', $assetId)
            ->whereNull('end_time')
            ->exists();
    }

    protected function hasBlockingWorkOrder(int $assetId): bool
    {
        return $this->blockingWorkOrderQuery($assetId)->exists();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<MaintenanceWorkOrder>
     */
    protected function blockingWorkOrderQuery(int $assetId)
    {
        return MaintenanceWorkOrder::query()
            ->where('fixed_asset_id', $assetId)
            ->whereIn('status', array_map(
                fn (MaintenanceWorkOrderStatus $s) => $s->value,
                array_filter(MaintenanceWorkOrderStatus::cases(), fn ($s) => $s->blocksProduction()),
            ))
            ->where(function ($q) {
                $q->where('priority', 'critical')
                    ->orWhereIn('maintenance_type', [
                        MaintenanceType::Emergency->value,
                        MaintenanceType::Corrective->value,
                    ]);
            });
    }
}
