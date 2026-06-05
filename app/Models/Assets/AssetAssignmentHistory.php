<?php

namespace App\Models\Assets;

use App\Enums\AssetAssignmentStatus;
use App\Enums\AssetAssignmentType;
use App\Enums\AssetPhysicalCondition;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetAssignmentHistory extends Model
{
    protected $fillable = [
        'fixed_asset_id',
        'assignment_type',
        'assigned_to_user_id',
        'assigned_to_branch_id',
        'assigned_to_employee_id',
        'assigned_to_department_id',
        'assigned_by',
        'assigned_at',
        'expected_return_date',
        'assignment_reason',
        'status',
        'returned_at',
        'condition_at_assignment',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'assignment_type' => AssetAssignmentType::class,
            'assigned_at' => 'datetime',
            'expected_return_date' => 'date',
            'status' => AssetAssignmentStatus::class,
            'returned_at' => 'datetime',
            'condition_at_assignment' => AssetPhysicalCondition::class,
        ];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(FixedAsset::class, 'fixed_asset_id');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function assignedBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'assigned_to_branch_id');
    }

    public function assignedEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'assigned_to_employee_id');
    }

    public function assignedDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'assigned_to_department_id');
    }

    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
