<?php

namespace App\Models\Inventory;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryItemWarehouseReorderSetting extends Model
{
    use BelongsToTenant;

    protected bool $tenantScopedToBranch = true;

    protected $fillable = [
        'company_id', 'branch_id', 'warehouse_id', 'inventory_item_id',
        'min_level', 'max_level', 'reorder_quantity', 'safety_stock', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'min_level' => 'decimal:3',
            'max_level' => 'decimal:3',
            'reorder_quantity' => 'decimal:3',
            'safety_stock' => 'decimal:3',
            'is_active' => 'boolean',
        ];
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }
}
