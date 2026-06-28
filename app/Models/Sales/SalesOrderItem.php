<?php

namespace App\Models\Sales;

use App\Models\Inventory\InventoryItem;
use App\Models\Production\ProductionSpecification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SalesOrderItem extends Model
{
    protected $fillable = [
        'sales_order_id', 'inventory_item_id', 'item_name', 'description', 'quantity',
        'unit_price', 'line_total', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'unit_price' => 'decimal:2',
            'line_total' => 'decimal:2',
        ];
    }

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function productionSpecification(): HasOne
    {
        return $this->hasOne(ProductionSpecification::class);
    }
}
