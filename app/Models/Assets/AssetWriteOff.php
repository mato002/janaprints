<?php

namespace App\Models\Assets;

use App\Enums\AssetWriteOffReason;
use App\Enums\AssetWriteOffStatus;
use App\Models\Accounting\Journal;
use App\Models\Concerns\BelongsToTenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetWriteOff extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'company_id',
        'fixed_asset_id',
        'writeoff_no',
        'reason',
        'write_off_date',
        'nbv_at_writeoff',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
        'posted_journal_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'reason' => AssetWriteOffReason::class,
            'write_off_date' => 'date',
            'nbv_at_writeoff' => 'decimal:2',
            'status' => AssetWriteOffStatus::class,
            'approved_at' => 'datetime',
        ];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(FixedAsset::class, 'fixed_asset_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
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
