<?php

namespace App\Models\Inventory;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryCostLayer extends Model
{
    use BelongsToTenant;

    protected bool $tenantScopedToBranch = true;

    protected $fillable = [
        'company_id',
        'branch_id',
        'inventory_item_id',
        'warehouse_id',
        'inventory_movement_id',
        'quantity_received',
        'quantity_remaining',
        'unit_cost',
        'layer_date',
    ];

    protected function casts(): array
    {
        return [
            'quantity_received' => 'decimal:3',
            'quantity_remaining' => 'decimal:3',
            'unit_cost' => 'decimal:2',
            'layer_date' => 'date',
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

    public function movement(): BelongsTo
    {
        return $this->belongsTo(InventoryMovement::class, 'inventory_movement_id');
    }
}
