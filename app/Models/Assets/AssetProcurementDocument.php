<?php

namespace App\Models\Assets;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AssetProcurementDocument extends Model
{
    protected $fillable = [
        'fixed_asset_id',
        'document_type',
        'document_id',
        'document_label',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(FixedAsset::class, 'fixed_asset_id');
    }

    public function document(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'document_type', 'document_id');
    }
}
