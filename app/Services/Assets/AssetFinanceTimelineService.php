<?php

namespace App\Services\Assets;

use App\Models\Assets\AssetFinanceTimelineEntry;
use App\Models\Assets\FixedAsset;
use Illuminate\Database\Eloquent\Model;

class AssetFinanceTimelineService
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
    ): AssetFinanceTimelineEntry {
        return AssetFinanceTimelineEntry::query()->create([
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
