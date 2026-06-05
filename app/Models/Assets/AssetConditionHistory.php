<?php

namespace App\Models\Assets;

use App\Enums\AssetPhysicalCondition;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AssetConditionHistory extends Model
{
    protected $fillable = [
        'fixed_asset_id',
        'condition',
        'source_type',
        'source_id',
        'recorded_by',
        'notes',
        'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'condition' => AssetPhysicalCondition::class,
            'recorded_at' => 'datetime',
        ];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(FixedAsset::class, 'fixed_asset_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function source(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'source_type', 'source_id');
    }
}
