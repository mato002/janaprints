<?php

namespace App\Models\Pos;

use App\Enums\PosReconciliationAction;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosCashReconciliationLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'pos_cash_reconciliation_id', 'company_id', 'user_id', 'action', 'notes', 'metadata', 'created_at',
    ];

    protected function casts(): array
    {
        return [
            'action' => PosReconciliationAction::class,
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function reconciliation(): BelongsTo
    {
        return $this->belongsTo(PosCashReconciliation::class, 'pos_cash_reconciliation_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
