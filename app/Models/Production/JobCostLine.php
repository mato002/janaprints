<?php

namespace App\Models\Production;

use App\Enums\JobCostCategory;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\InventoryMovement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobCostLine extends Model
{
    protected $fillable = [
        'job_cost_sheet_id',
        'cost_category',
        'description',
        'inventory_item_id',
        'inventory_movement_id',
        'quantity',
        'unit_cost',
        'line_total',
    ];

    protected function casts(): array
    {
        return [
            'cost_category' => JobCostCategory::class,
            'quantity' => 'decimal:3',
            'unit_cost' => 'decimal:2',
            'line_total' => 'decimal:2',
        ];
    }

    public function costSheet(): BelongsTo
    {
        return $this->belongsTo(JobCostSheet::class, 'job_cost_sheet_id');
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function movement(): BelongsTo
    {
        return $this->belongsTo(InventoryMovement::class, 'inventory_movement_id');
    }
}
