<?php

namespace App\Models\Hr;

use App\Enums\LeaveRequestStatus;
use App\Models\Branch;
use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\LogsActivity;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'company_id',
    'branch_id',
    'department_id',
    'employee_id',
    'leave_type_id',
    'reference',
    'start_date',
    'end_date',
    'is_half_day_start',
    'is_half_day_end',
    'days_requested',
    'reason',
    'status',
    'conflict_warnings',
    'submitted_at',
    'submitted_by_user_id',
    'supervisor_approved_at',
    'supervisor_approved_by_user_id',
    'hr_approved_at',
    'hr_approved_by_user_id',
    'rejected_at',
    'rejected_by_user_id',
    'rejection_reason',
    'cancelled_at',
    'notes',
])]
class LeaveRequest extends Model
{
    use BelongsToCompany, LogsActivity;

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_half_day_start' => 'boolean',
            'is_half_day_end' => 'boolean',
            'days_requested' => 'decimal:1',
            'status' => LeaveRequestStatus::class,
            'conflict_warnings' => 'array',
            'submitted_at' => 'datetime',
            'supervisor_approved_at' => 'datetime',
            'hr_approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by_user_id');
    }

    public function supervisorApprovedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_approved_by_user_id');
    }

    public function hrApprovedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'hr_approved_by_user_id');
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by_user_id');
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function scopeForTenant(Builder $query): Builder
    {
        if (tenant()->isSuperAdmin && ! tenant()->hasCompany()) {
            return $query;
        }

        if ($companyId = tenant()->companyId() ?? auth()->user()?->company_id) {
            return $query->where('company_id', $companyId);
        }

        return $query->whereRaw('1 = 0');
    }
}
