<?php

namespace App\Services\Assets;

use App\Models\Assets\FixedAsset;
use App\Models\Assets\MaintenanceTimelineEntry;
use App\Models\Assets\MaintenanceWorkOrder;
use App\Support\ActivityLogger;

class MaintenanceTimelineService
{
    public function record(
        FixedAsset $asset,
        string $eventType,
        string $title,
        ?string $description = null,
        ?MaintenanceWorkOrder $workOrder = null,
        ?int $userId = null,
        array $metadata = [],
    ): MaintenanceTimelineEntry {
        $entry = MaintenanceTimelineEntry::query()->create([
            'fixed_asset_id' => $asset->id,
            'maintenance_work_order_id' => $workOrder?->id,
            'event_type' => $eventType,
            'title' => $title,
            'description' => $description,
            'metadata' => $metadata ?: null,
            'user_id' => $userId ?? auth()->id(),
            'occurred_at' => now(),
        ]);

        ActivityLogger::log($eventType, $workOrder ?? $asset, $userId ?? auth()->id(), $metadata);

        return $entry;
    }
}
