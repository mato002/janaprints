<?php

namespace App\Services\Assets;

use App\Models\Assets\FixedAsset;
use App\Models\Assets\MachineTimelineEntry;
use App\Support\ActivityLogger;

class MachineTimelineService
{
    public function record(
        FixedAsset $asset,
        string $eventType,
        string $title,
        ?string $description = null,
        ?int $userId = null,
        array $metadata = [],
    ): MachineTimelineEntry {
        $entry = MachineTimelineEntry::query()->create([
            'fixed_asset_id' => $asset->id,
            'event_type' => $eventType,
            'title' => $title,
            'description' => $description,
            'metadata' => $metadata ?: null,
            'user_id' => $userId ?? auth()->id(),
            'occurred_at' => now(),
        ]);

        ActivityLogger::log($eventType, $asset, $userId ?? auth()->id(), $metadata);

        return $entry;
    }
}
