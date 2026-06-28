<?php

namespace App\Models\Production;

use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\Warehouse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionSessionMaterial extends Model
{
    protected $fillable = [
        'production_session_id', 'production_material_requirement_id',
        'inventory_item_id', 'warehouse_id',
        'consumed_quantity', 'waste_quantity', 'returned_quantity',
    ];

    protected function casts(): array
    {
        return [
            'consumed_quantity' => 'decimal:3',
            'waste_quantity' => 'decimal:3',
            'returned_quantity' => 'decimal:3',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(ProductionSession::class, 'production_session_id');
    }

    public function requirement(): BelongsTo
    {
        return $this->belongsTo(ProductionMaterialRequirement::class, 'production_material_requirement_id');
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
