<?php

namespace App\Models\Sales;

use App\Enums\QuotationItemType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationItem extends Model
{
    protected $fillable = [
        'quotation_id', 'item_type', 'item_name', 'description', 'quantity',
        'unit_price', 'discount', 'tax_rate', 'line_total', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'item_type' => QuotationItemType::class,
            'quantity' => 'decimal:3',
            'unit_price' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'line_total' => 'decimal:2',
        ];
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }
}
