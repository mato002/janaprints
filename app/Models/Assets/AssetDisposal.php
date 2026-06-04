<?php

namespace App\Models\Assets;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetDisposal extends Model
{
    protected $fillable = [
        'fixed_asset_id',
        'disposal_date',
        'disposal_proceeds',
        'gain_loss_amount',
        'disposal_method',
        'notes',
        'disposed_by',
        'posted_journal_id',
    ];

    protected function casts(): array
    {
        return [
            'disposal_date' => 'date',
            'disposal_proceeds' => 'decimal:2',
            'gain_loss_amount' => 'decimal:2',
        ];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(FixedAsset::class, 'fixed_asset_id');
    }

    public function disposer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disposed_by');
    }
}
