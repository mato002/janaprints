<?php

namespace App\Models\Production;

use App\Models\Inventory\InventoryItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductBomLine extends Model
{
    protected $fillable = [
        'product_bom_id', 'inventory_item_id', 'quantity_per_unit',
        'waste_factor_percent', 'sort_order', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity_per_unit' => 'decimal:4',
            'waste_factor_percent' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }

    public function bom(): BelongsTo
    {
        return $this->belongsTo(ProductBom::class, 'product_bom_id');
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function effectiveQuantityPerUnit(): float
    {
        $base = (float) $this->quantity_per_unit;
        $waste = (float) $this->waste_factor_percent;

        return round($base * (1 + ($waste / 100)), 4);
    }
}
