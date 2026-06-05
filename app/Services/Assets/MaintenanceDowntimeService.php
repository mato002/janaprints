<?php

namespace App\Services\Assets;

use App\Enums\DowntimeImpactLevel;
use App\Models\Assets\AssetDowntimeRecord;
use App\Models\Assets\MaintenanceWorkOrder;

class MaintenanceDowntimeService
{
    public function startForWorkOrder(MaintenanceWorkOrder $order, int $userId): AssetDowntimeRecord
    {
        $impact = $order->priority->isCritical()
            ? DowntimeImpactLevel::Critical
            : ($order->maintenance_type->blocksProduction() ? DowntimeImpactLevel::High : DowntimeImpactLevel::Medium);

        return AssetDowntimeRecord::query()->create([
            'company_id' => $order->company_id,
            'branch_id' => $order->branch_id,
            'fixed_asset_id' => $order->fixed_asset_id,
            'maintenance_work_order_id' => $order->id,
            'start_time' => now(),
            'reason' => $order->description,
            'impact_level' => $impact,
        ]);
    }

    public function endForWorkOrder(MaintenanceWorkOrder $order): void
    {
        AssetDowntimeRecord::query()
            ->where('maintenance_work_order_id', $order->id)
            ->whereNull('end_time')
            ->update(['end_time' => now()]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function record(array $data, int $companyId, ?int $branchId): AssetDowntimeRecord
    {
        $record = AssetDowntimeRecord::query()->create([
            'company_id' => $companyId,
            'branch_id' => $data['branch_id'] ?? $branchId,
            'fixed_asset_id' => $data['fixed_asset_id'],
            'maintenance_work_order_id' => $data['maintenance_work_order_id'] ?? null,
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'] ?? null,
            'reason' => $data['reason'] ?? null,
            'impact_level' => $data['impact_level'] ?? DowntimeImpactLevel::Medium->value,
        ]);

        return $record->fresh();
    }

    public function totalDowntimeMinutes(int $companyId, ?int $branchId = null, ?string $from = null, ?string $to = null): int
    {
        $query = AssetDowntimeRecord::query()->where('company_id', $companyId);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }
        if ($from) {
            $query->where('start_time', '>=', $from);
        }
        if ($to) {
            $query->where('start_time', '<=', $to);
        }

        return (int) $query->sum('duration_minutes');
    }
}
