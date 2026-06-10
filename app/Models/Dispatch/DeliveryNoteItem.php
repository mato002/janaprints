<?php

namespace App\Models\Dispatch;

use App\Models\Inventory\InventoryItem;
use App\Models\Production\ProductionOutput;
use App\Models\Sales\SalesOrderItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryNoteItem extends Model
{
    protected $fillable = [
        'delivery_note_id',
        'description',
        'quantity',
        'unit',
        'sales_order_item_id',
        'inventory_item_id',
        'production_output_id',
        'unit_cost',
        'total_cost',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'unit_cost' => 'decimal:4',
            'total_cost' => 'decimal:2',
        ];
    }

    public function deliveryNote(): BelongsTo
    {
        return $this->belongsTo(DeliveryNote::class);
    }

    public function salesOrderItem(): BelongsTo
    {
        return $this->belongsTo(SalesOrderItem::class);
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function productionOutput(): BelongsTo
    {
        return $this->belongsTo(ProductionOutput::class);
    }
}
