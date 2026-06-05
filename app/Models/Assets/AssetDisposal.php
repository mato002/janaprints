<?php

namespace App\Models\Assets;

use App\Enums\AssetDisposalStatus;
use App\Models\Accounting\Journal;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetDisposal extends Model
{
    protected $fillable = [
        'fixed_asset_id',
        'disposal_date',
        'disposal_proceeds',
        'nbv_at_disposal',
        'gain_loss_amount',
        'disposal_method',
        'notes',
        'disposed_by',
        'status',
        'approved_by',
        'approved_at',
        'posted_journal_id',
    ];

    protected function casts(): array
    {
        return [
            'disposal_date' => 'date',
            'disposal_proceeds' => 'decimal:2',
            'nbv_at_disposal' => 'decimal:2',
            'gain_loss_amount' => 'decimal:2',
            'status' => AssetDisposalStatus::class,
            'approved_at' => 'datetime',
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

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class, 'posted_journal_id');
    }
}
