<?php

namespace App\Models\Pos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosReturnItem extends Model
{
    protected $fillable = [
        'pos_return_id', 'pos_sale_item_id', 'description', 'quantity_returned',
        'unit_price', 'line_refund_amount', 'reason',
    ];

    protected function casts(): array
    {
        return [
            'quantity_returned' => 'decimal:3',
            'unit_price' => 'decimal:2',
            'line_refund_amount' => 'decimal:2',
        ];
    }

    public function posReturn(): BelongsTo
    {
        return $this->belongsTo(PosReturn::class);
    }

    public function saleItem(): BelongsTo
    {
        return $this->belongsTo(PosSaleItem::class, 'pos_sale_item_id');
    }
}
