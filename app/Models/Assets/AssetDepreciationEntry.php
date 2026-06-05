<?php

namespace App\Models\Assets;

use App\Enums\DepreciationPostingStatus;
use App\Models\Accounting\Journal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetDepreciationEntry extends Model
{
    protected $fillable = [
        'fixed_asset_id',
        'depreciation_run_id',
        'period_date',
        'depreciation_amount',
        'accumulated_after',
        'net_book_value_after',
        'posting_status',
        'posted_journal_id',
        'posted_at',
        'is_locked',
    ];

    protected function casts(): array
    {
        return [
            'period_date' => 'date',
            'depreciation_amount' => 'decimal:2',
            'accumulated_after' => 'decimal:2',
            'net_book_value_after' => 'decimal:2',
            'posting_status' => DepreciationPostingStatus::class,
            'posted_at' => 'datetime',
            'is_locked' => 'boolean',
        ];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(FixedAsset::class, 'fixed_asset_id');
    }

    public function run(): BelongsTo
    {
        return $this->belongsTo(DepreciationRun::class, 'depreciation_run_id');
    }

    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class, 'posted_journal_id');
    }

    public function isPosted(): bool
    {
        return $this->posting_status === DepreciationPostingStatus::Posted;
    }
}
