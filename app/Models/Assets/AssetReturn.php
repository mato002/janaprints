<?php

namespace App\Models\Assets;

use App\Enums\AssetReturnCondition;
use App\Models\Branch;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetReturn extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'company_id',
        'branch_id',
        'fixed_asset_id',
        'assignment_history_id',
        'return_date',
        'condition',
        'returned_by',
        'received_by',
        'notes',
        'requires_review',
    ];

    protected function casts(): array
    {
        return [
            'return_date' => 'date',
            'condition' => AssetReturnCondition::class,
            'requires_review' => 'boolean',
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

    public function assignmentHistory(): BelongsTo
    {
        return $this->belongsTo(AssetAssignmentHistory::class, 'assignment_history_id');
    }

    public function returnedByEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'returned_by');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
