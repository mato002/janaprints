<?php

namespace App\Models\Hr;

use App\Enums\ExitStatus;
use App\Enums\ExitType;
use App\Models\Branch;
use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\LogsActivity;
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
    'employee_id',
    'reference',
    'exit_type',
    'status',
    'last_working_date',
    'exit_date',
    'reason',
    'notes',
    'leave_balance_days',
    'leave_balance_amount',
    'salary_balance',
    'deductions_total',
    'net_final_dues',
    'initiated_by_user_id',
    'initiated_at',
    'settled_by_user_id',
    'settled_at',
    'closed_by_user_id',
    'closed_at',
])]
class EmployeeExit extends Model
{
    use BelongsToCompany, LogsActivity;

    protected function casts(): array
    {
        return [
            'exit_type' => ExitType::class,
            'status' => ExitStatus::class,
            'last_working_date' => 'date',
            'exit_date' => 'date',
            'leave_balance_days' => 'decimal:2',
            'leave_balance_amount' => 'decimal:2',
            'salary_balance' => 'decimal:2',
            'deductions_total' => 'decimal:2',
            'net_final_dues' => 'decimal:2',
            'initiated_at' => 'datetime',
            'settled_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function initiatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by_user_id');
    }

    public function settledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'settled_by_user_id');
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by_user_id');
    }

    public function clearances(): HasMany
    {
        return $this->hasMany(EmployeeExitClearance::class);
    }

    public function clearanceProgress(): array
    {
        $total = $this->clearances()->count();
        $done = $this->clearances()->whereIn('status', ['cleared', 'waived'])->count();

        return ['done' => $done, 'total' => $total];
    }

    public function isClearanceComplete(): bool
    {
        return $this->clearances()->where('status', 'pending')->doesntExist();
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
