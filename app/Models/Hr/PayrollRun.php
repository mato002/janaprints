<?php

namespace App\Models\Hr;

use App\Enums\PayrollRunStatus;
use App\Models\Accounting\Journal;
use App\Models\Branch;
use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\LogsActivity;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'company_id', 'branch_id', 'reference', 'period_start', 'period_end', 'pay_date',
    'status', 'employee_count', 'gross_total', 'deductions_total', 'net_total',
    'paye_total', 'shif_total', 'nssf_total', 'housing_levy_total',
    'processed_by_user_id', 'processed_at', 'approved_by_user_id', 'approved_at',
    'posted_journal_id', 'posted_by_user_id', 'posted_at', 'notes',
])]
class PayrollRun extends Model
{
    use BelongsToCompany, LogsActivity;

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'pay_date' => 'date',
            'status' => PayrollRunStatus::class,
            'gross_total' => 'decimal:2',
            'deductions_total' => 'decimal:2',
            'net_total' => 'decimal:2',
            'paye_total' => 'decimal:2',
            'shif_total' => 'decimal:2',
            'nssf_total' => 'decimal:2',
            'housing_levy_total' => 'decimal:2',
            'processed_at' => 'datetime',
            'approved_at' => 'datetime',
            'posted_at' => 'datetime',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function payslips(): HasMany
    {
        return $this->hasMany(PayrollPayslip::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by_user_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function postedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by_user_id');
    }

    public function postedJournal(): BelongsTo
    {
        return $this->belongsTo(Journal::class, 'posted_journal_id');
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
