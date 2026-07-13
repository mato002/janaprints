<?php

namespace App\Models\Accounting;

use App\Models\Concerns\BelongsToTenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankReconciliation extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'company_id',
        'bank_statement_id',
        'statement_closing_balance',
        'gl_closing_balance',
        'difference',
        'reconciled_at',
        'reconciled_by',
    ];

    protected function casts(): array
    {
        return [
            'statement_closing_balance' => 'decimal:2',
            'gl_closing_balance' => 'decimal:2',
            'difference' => 'decimal:2',
            'reconciled_at' => 'datetime',
        ];
    }

    public function statement(): BelongsTo
    {
        return $this->belongsTo(BankStatement::class, 'bank_statement_id');
    }

    public function reconciledByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reconciled_by');
    }
}
