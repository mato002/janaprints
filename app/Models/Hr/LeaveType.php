<?php

namespace App\Models\Hr;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'company_id',
    'code',
    'name',
    'is_paid',
    'requires_supervisor_approval',
    'requires_hr_approval',
    'default_days_per_year',
    'accrual_days_per_month',
    'allow_half_day',
    'is_active',
    'sort_order',
])]
class LeaveType extends Model
{
    use BelongsToCompany, LogsActivity;

    protected function casts(): array
    {
        return [
            'is_paid' => 'boolean',
            'requires_supervisor_approval' => 'boolean',
            'requires_hr_approval' => 'boolean',
            'default_days_per_year' => 'decimal:1',
            'accrual_days_per_month' => 'decimal:2',
            'allow_half_day' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function leaveBalances(): HasMany
    {
        return $this->hasMany(LeaveBalance::class);
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
