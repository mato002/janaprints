<?php

namespace App\Models\Accounting;

use App\Enums\AccountingCloseType;
use App\Models\Company;
use App\Models\Concerns\LogsActivity;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountingCloseAudit extends Model
{
    use LogsActivity;

    protected $fillable = [
        'company_id',
        'fiscal_year_id',
        'accounting_period_id',
        'close_type',
        'journal_id',
        'reversal_journal_id',
        'net_amount',
        'validation_snapshot',
        'performed_by',
        'performed_at',
        'reversed_at',
        'reversed_by',
    ];

    protected function casts(): array
    {
        return [
            'close_type' => AccountingCloseType::class,
            'net_amount' => 'decimal:2',
            'validation_snapshot' => 'array',
            'performed_at' => 'datetime',
            'reversed_at' => 'datetime',
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

    public function accountingPeriod(): BelongsTo
    {
        return $this->belongsTo(AccountingPeriod::class);
    }

    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class);
    }

    public function reversalJournal(): BelongsTo
    {
        return $this->belongsTo(Journal::class, 'reversal_journal_id');
    }

    public function performedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function isActive(): bool
    {
        return $this->reversed_at === null;
    }
}
