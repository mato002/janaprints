<?php

namespace App\Models\Accounting;

use App\Enums\JournalEntryType;
use App\Enums\JournalStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Concerns\LogsActivity;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Journal extends Model
{
    use LogsActivity;

    protected $fillable = [
        'company_id',
        'branch_id',
        'fiscal_year_id',
        'accounting_period_id',
        'journal_number',
        'journal_date',
        'entry_type',
        'status',
        'reference',
        'description',
        'posting_event',
        'source_module',
        'source_type',
        'source_id',
        'posting_template_id',
        'posting_rule_id',
        'total_debit',
        'total_credit',
        'reversal_of_journal_id',
        'reversed_by_journal_id',
        'posted_at',
        'posted_by',
        'reversed_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'journal_date' => 'date',
            'entry_type' => JournalEntryType::class,
            'status' => JournalStatus::class,
            'total_debit' => 'decimal:2',
            'total_credit' => 'decimal:2',
            'posted_at' => 'datetime',
            'reversed_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class);
    }

    public function accountingPeriod(): BelongsTo
    {
        return $this->belongsTo(AccountingPeriod::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(JournalLine::class)->orderBy('line_number');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function poster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function reversalOf(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reversal_of_journal_id');
    }

    public function reversedBy(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reversed_by_journal_id');
    }

    public function postingTemplate(): BelongsTo
    {
        return $this->belongsTo(PostingTemplate::class);
    }

    public function postingRule(): BelongsTo
    {
        return $this->belongsTo(PostingRule::class);
    }

    public function scopeForTenant($query)
    {
        if (tenant()->isSuperAdmin && ! tenant()->hasCompany()) {
            return $query;
        }

        if ($companyId = tenant()->companyId()) {
            $query->where($this->getTable().'.company_id', $companyId);

            if ($branchId = tenant()->branchId()) {
                $query->where(function ($inner) use ($branchId) {
                    $inner->whereNull($this->getTable().'.branch_id')
                        ->orWhere($this->getTable().'.branch_id', $branchId);
                });
            }

            return $query;
        }

        return $query->whereRaw('1 = 0');
    }

    public function isBalanced(): bool
    {
        return round((float) $this->total_debit, 2) === round((float) $this->total_credit, 2);
    }
}
