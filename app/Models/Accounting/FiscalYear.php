<?php

namespace App\Models\Accounting;

use App\Enums\FiscalYearStatus;
use App\Models\Company;
use App\Models\Concerns\LogsActivity;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FiscalYear extends Model
{
    use LogsActivity;

    protected $fillable = [
        'company_id',
        'name',
        'code',
        'start_date',
        'end_date',
        'start_month',
        'status',
        'is_current',
        'closed_at',
        'closed_by',
        'locked_at',
        'locked_by',
        'year_end_prep_at',
        'year_end_prep_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'start_month' => 'integer',
            'status' => FiscalYearStatus::class,
            'is_current' => 'boolean',
            'closed_at' => 'datetime',
            'locked_at' => 'datetime',
            'year_end_prep_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function periods(): HasMany
    {
        return $this->hasMany(AccountingPeriod::class)->orderBy('period_number');
    }

    public function closedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function lockedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    public function yearEndPrepByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'year_end_prep_by');
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
