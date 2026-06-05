<?php

namespace App\Models\Assets;

use App\Enums\AssetHandoverStatus;
use App\Enums\AssetPhysicalCondition;
use App\Models\Branch;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetHandover extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'company_id',
        'branch_id',
        'fixed_asset_id',
        'handover_no',
        'from_employee_id',
        'to_employee_id',
        'from_branch_id',
        'to_branch_id',
        'handover_date',
        'received_date',
        'condition_notes',
        'remarks',
        'approved_by',
        'status',
        'condition',
    ];

    protected function casts(): array
    {
        return [
            'handover_date' => 'date',
            'received_date' => 'date',
            'status' => AssetHandoverStatus::class,
            'condition' => AssetPhysicalCondition::class,
        ];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(FixedAsset::class, 'fixed_asset_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function fromEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'from_employee_id');
    }

    public function toEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'to_employee_id');
    }

    public function fromBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'from_branch_id');
    }

    public function toBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'to_branch_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
