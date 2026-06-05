<?php

namespace App\Models\Pos;

use App\Enums\PosPaymentMethod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosPayment extends Model
{
    protected $fillable = [
        'pos_sale_id', 'payment_method', 'amount', 'reference', 'posted_journal_id',
    ];

    protected function casts(): array
    {
        return [
            'payment_method' => PosPaymentMethod::class,
            'amount' => 'decimal:2',
        ];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(PosSale::class, 'pos_sale_id');
    }
}
