<?php

namespace App\Models;

use App\Enums\EmailIdentity\EmployeeActivationStatus;
use App\Enums\EmploymentStatus;
use App\Enums\Gender;
use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'company_id',
    'branch_id',
    'department_id',
    'job_title_id',
    'shift_id',
    'employee_number',
    'first_name',
    'middle_name',
    'last_name',
    'gender',
    'phone',
    'email',
    'national_id',
    'kra_pin',
    'nhif_number',
    'nssf_number',
    'bank_name',
    'bank_account_number',
    'bank_branch_code',
    'designation',
    'hire_date',
    'employment_status',
    'photo',
    'is_active',
    'activation_status',
    'activation_role',
])]
class Employee extends Model
{
    use BelongsToCompany, HasFactory, LogsActivity;

    protected function casts(): array
    {
        return [
            'gender' => Gender::class,
            'employment_status' => EmploymentStatus::class,
            'hire_date' => 'date',
            'is_active' => 'boolean',
            'activation_status' => EmployeeActivationStatus::class,
        ];
    }

    protected function fullName(): Attribute
    {
        return Attribute::get(fn (): string => trim(implode(' ', array_filter([
            $this->first_name,
            $this->middle_name,
            $this->last_name,
        ]))));
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function jobTitle(): BelongsTo
    {
        return $this->belongsTo(JobTitle::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Hr\Shift::class);
    }

    public function attendanceRecords(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Hr\AttendanceRecord::class);
    }

    public function leaveRequests(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Hr\LeaveRequest::class);
    }

    public function leaveBalances(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Hr\LeaveBalance::class);
    }

    public function compensation(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Models\Hr\EmployeeCompensation::class)->where('is_active', true)->latest('effective_from');
    }

    public function compensations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Hr\EmployeeCompensation::class);
    }

    public function documents(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Hr\EmployeeDocument::class);
    }

    public function performanceReviews(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Hr\PerformanceReview::class);
    }

    public function salesTargets(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Hr\EmployeeSalesTarget::class);
    }

    public function trainingAssignments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Hr\EmployeeTrainingAssignment::class);
    }

    public function skills(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Hr\EmployeeSkill::class);
    }

    public function exits(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Hr\EmployeeExit::class);
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    public function activations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\EmailIdentity\EmployeeActivation::class);
    }
}
