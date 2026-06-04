<?php

namespace App\Models\Assets;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetDepreciationEntry extends Model
{
    protected $fillable = [
        'fixed_asset_id',
        'period_date',
        'depreciation_amount',
        'accumulated_after',
        'net_book_value_after',
    ];

    protected function casts(): array
    {
        return [
            'period_date' => 'date',
            'depreciation_amount' => 'decimal:2',
            'accumulated_after' => 'decimal:2',
            'net_book_value_after' => 'decimal:2',
        ];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(FixedAsset::class, 'fixed_asset_id');
    }
}
