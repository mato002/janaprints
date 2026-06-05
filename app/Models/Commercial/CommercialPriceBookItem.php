<?php

namespace App\Models\Commercial;

use App\Enums\CommercialPriceBookStatus;
use App\Models\Inventory\InventoryItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommercialPriceBookItem extends Model
{
    protected $fillable = [
        'price_book_id', 'inventory_item_id', 'service_code', 'description',
        'unit_price', 'minimum_quantity', 'discount_percent',
        'effective_from', 'effective_to', 'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => CommercialPriceBookStatus::class,
            'unit_price' => 'decimal:2',
            'minimum_quantity' => 'decimal:4',
            'discount_percent' => 'decimal:2',
            'effective_from' => 'datetime',
            'effective_to' => 'datetime',
        ];
    }

    public function priceBook(): BelongsTo
    {
        return $this->belongsTo(CommercialPriceBook::class, 'price_book_id');
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }
}
