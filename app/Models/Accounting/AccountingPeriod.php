<?php

namespace App\Models\Accounting;

use App\Enums\AccountingPeriodStatus;
use App\Models\Company;
use App\Models\Concerns\LogsActivity;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountingPeriod extends Model
{
    use LogsActivity;

    protected $fillable = [
        'company_id',
        'fiscal_year_id',
        'period_number',
        'name',
        'code',
        'start_date',
        'end_date',
        'status',
        'is_current',
        'closed_at',
        'closed_by',
        'locked_at',
        'locked_by',
    ];

    protected function casts(): array
    {
        return [
            'period_number' => 'integer',
            'start_date' => 'date',
            'end_date' => 'date',
            'status' => AccountingPeriodStatus::class,
            'is_current' => 'boolean',
            'closed_at' => 'datetime',
            'locked_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class);
    }

    public function closedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function lockedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    public function scopeForTenant($query)
    {
        if (tenant()->isSuperAdmin && ! tenant()->hasCompany()) {
            return $query;
        }

        if ($companyId = tenant()->companyId()) {
            return $query->where($this->getTable().'.company_id', $companyId);
        }

        return $query->whereRaw('1 = 0');
    }
}
