<?php

namespace App\Models\Inventory;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryReorderAlert extends Model
{
    use BelongsToTenant;

    protected bool $tenantScopedToBranch = true;

    protected $fillable = [
        'company_id', 'branch_id', 'inventory_item_id', 'current_quantity',
        'reorder_level', 'is_resolved', 'alerted_at',
    ];

    protected function casts(): array
    {
        return [
            'current_quantity' => 'decimal:3',
            'reorder_level' => 'decimal:3',
            'is_resolved' => 'boolean',
            'alerted_at' => 'datetime',
        ];
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }
}
