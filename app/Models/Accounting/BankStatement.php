<?php

namespace App\Models\Accounting;

use App\Enums\BankStatementStatus;
use App\Models\Concerns\BelongsToTenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BankStatement extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'company_id',
        'bank_account_id',
        'statement_date',
        'opening_balance',
        'closing_balance',
        'status',
        'notes',
        'created_by',
        'reconciled_at',
    ];

    protected function casts(): array
    {
        return [
            'statement_date' => 'date',
            'opening_balance' => 'decimal:2',
            'closing_balance' => 'decimal:2',
            'status' => BankStatementStatus::class,
            'reconciled_at' => 'datetime',
        ];
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(BankStatementLine::class)->orderBy('line_date')->orderBy('id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reconciliation(): HasOne
    {
        return $this->hasOne(BankReconciliation::class);
    }
}
