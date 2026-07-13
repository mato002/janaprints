<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankStatementLine extends Model
{
    protected $fillable = [
        'bank_statement_id',
        'line_date',
        'description',
        'reference',
        'amount',
        'matched_journal_line_id',
        'is_matched',
    ];

    protected function casts(): array
    {
        return [
            'line_date' => 'date',
            'amount' => 'decimal:2',
            'is_matched' => 'boolean',
        ];
    }

    public function statement(): BelongsTo
    {
        return $this->belongsTo(BankStatement::class, 'bank_statement_id');
    }

    public function matchedJournalLine(): BelongsTo
    {
        return $this->belongsTo(JournalLine::class, 'matched_journal_line_id');
    }
}
