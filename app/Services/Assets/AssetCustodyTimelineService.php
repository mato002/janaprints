<?php

namespace App\Services\Assets;

use App\Models\Assets\AssetCustodyTimelineEntry;
use App\Models\Assets\FixedAsset;
use Illuminate\Database\Eloquent\Model;

class AssetCustodyTimelineService
{
    /**
     * @param  array<string, mixed>|null  $metadata
     */
    public function record(
        FixedAsset $asset,
        string $eventType,
        string $title,
        ?string $description = null,
        ?Model $source = null,
        ?int $userId = null,
        ?array $metadata = null,
    ): AssetCustodyTimelineEntry {
        return AssetCustodyTimelineEntry::query()->create([
            'fixed_asset_id' => $asset->id,
            'event_type' => $eventType,
            'title' => $title,
            'description' => $description,
            'metadata' => array_merge($metadata ?? [], $source ? [
                'source_type' => $source::class,
                'source_id' => $source->getKey(),
            ] : []),
            'user_id' => $userId,
            'occurred_at' => now(),
        ]);
    }
}
