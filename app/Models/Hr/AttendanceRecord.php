<?php

namespace App\Models\Hr;

use App\Enums\AttendanceMethod;
use App\Enums\AttendanceStatus;
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
    'shift_id',
    'leave_request_id',
    'attendance_date',
    'clock_in_at',
    'clock_out_at',
    'clock_in_device',
    'clock_in_ip',
    'clock_in_location',
    'clock_out_device',
    'clock_out_ip',
    'clock_out_location',
    'scheduled_hours',
    'actual_hours',
    'late_minutes',
    'overtime_hours',
    'status',
    'method',
    'notes',
    'is_manual',
    'adjusted_by_user_id',
    'approved_by_user_id',
    'approved_at',
])]
class AttendanceRecord extends Model
{
    use BelongsToCompany, LogsActivity;

    protected function casts(): array
    {
        return [
            'attendance_date' => 'date',
            'clock_in_at' => 'datetime',
            'clock_out_at' => 'datetime',
            'scheduled_hours' => 'decimal:2',
            'actual_hours' => 'decimal:2',
            'late_minutes' => 'integer',
            'overtime_hours' => 'decimal:2',
            'status' => AttendanceStatus::class,
            'method' => AttendanceMethod::class,
            'is_manual' => 'boolean',
            'approved_at' => 'datetime',
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

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function leaveRequest(): BelongsTo
    {
        return $this->belongsTo(LeaveRequest::class);
    }

    public function adjustedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'adjusted_by_user_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function corrections(): HasMany
    {
        return $this->hasMany(AttendanceCorrection::class);
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
