<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockCountItem extends Model
{
    protected $fillable = [
        'stock_count_id', 'inventory_item_id', 'system_quantity', 'counted_quantity',
        'variance_quantity', 'system_unit_cost', 'variance_value',
        'inventory_variance_reason_code_id', 'reason', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'system_quantity' => 'decimal:3',
            'counted_quantity' => 'decimal:3',
            'variance_quantity' => 'decimal:3',
            'system_unit_cost' => 'decimal:2',
            'variance_value' => 'decimal:2',
        ];
    }

    public function stockCount(): BelongsTo
    {
        return $this->belongsTo(StockCount::class);
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function varianceReasonCode(): BelongsTo
    {
        return $this->belongsTo(InventoryVarianceReasonCode::class, 'inventory_variance_reason_code_id');
    }
}
