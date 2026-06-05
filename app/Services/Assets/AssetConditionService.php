<?php

namespace App\Services\Assets;

use App\Enums\AssetPhysicalCondition;
use App\Models\Assets\AssetConditionHistory;
use App\Models\Assets\FixedAsset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AssetConditionService
{
    public function __construct(
        protected AssetCustodyTimelineService $timeline,
    ) {}

    public function record(
        FixedAsset $asset,
        AssetPhysicalCondition $condition,
        ?Model $source = null,
        ?int $userId = null,
        ?string $notes = null,
    ): AssetConditionHistory {
        return DB::transaction(function () use ($asset, $condition, $source, $userId, $notes) {
            $history = AssetConditionHistory::query()->create([
                'fixed_asset_id' => $asset->id,
                'condition' => $condition,
                'source_type' => $source ? $source::class : null,
                'source_id' => $source?->getKey(),
                'recorded_by' => $userId,
                'notes' => $notes,
                'recorded_at' => now(),
            ]);

            $asset->update(['current_condition' => $condition]);

            $this->timeline->record(
                $asset,
                'condition_changed',
                __('Condition updated to :condition', ['condition' => $condition->label()]),
                $notes,
                $source,
                $userId,
                ['condition' => $condition->value],
            );

            return $history;
        });
    }
}
