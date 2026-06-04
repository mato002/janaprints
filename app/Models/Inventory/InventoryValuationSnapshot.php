<?php

namespace App\Models\Inventory;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryValuationSnapshot extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'company_id',
        'branch_id',
        'valuation_date',
        'snapshot_scope',
        'inventory_item_id',
        'warehouse_id',
        'inventory_category_id',
        'quantity',
        'fifo_value',
        'average_cost_value',
    ];

    protected function casts(): array
    {
        return [
            'valuation_date' => 'date',
            'quantity' => 'decimal:3',
            'fifo_value' => 'decimal:2',
            'average_cost_value' => 'decimal:2',
        ];
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
}
