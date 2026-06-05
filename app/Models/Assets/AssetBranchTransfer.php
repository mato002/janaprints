<?php

namespace App\Models\Assets;

use App\Enums\AssetBranchTransferStatus;
use App\Enums\AssetPhysicalCondition;
use App\Models\Branch;
use App\Models\Concerns\BelongsToCompany;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetBranchTransfer extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'fixed_asset_id',
        'transfer_no',
        'from_branch_id',
        'to_branch_id',
        'transfer_reason',
        'status',
        'requested_by',
        'approved_by',
        'accepted_by',
        'requested_at',
        'approved_at',
        'accepted_at',
        'condition',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => AssetBranchTransferStatus::class,
            'condition' => AssetPhysicalCondition::class,
            'requested_at' => 'datetime',
            'approved_at' => 'datetime',
            'accepted_at' => 'datetime',
        ];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(FixedAsset::class, 'fixed_asset_id');
    }

    public function fromBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'from_branch_id');
    }

    public function toBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'to_branch_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function acceptor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accepted_by');
    }
}
