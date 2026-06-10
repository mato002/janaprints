<?php

namespace App\Models\Inventory;

use App\Enums\ReplenishmentRecommendation;
use App\Enums\ReorderAlertStatus;
use App\Models\Concerns\BelongsToTenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryReorderAlert extends Model
{
    use BelongsToTenant;

    protected bool $tenantScopedToBranch = true;

    protected $fillable = [
        'company_id',
        'branch_id',
        'inventory_item_id',
        'warehouse_id',
        'alert_type',
        'current_quantity',
        'reorder_level',
        'max_level',
        'reorder_quantity',
        'safety_stock',
        'replenishment_action',
        'source_warehouse_id',
        'recommended_quantity',
        'metadata',
        'is_resolved',
        'status',
        'alerted_at',
        'acknowledged_by',
        'acknowledged_at',
        'resolved_by',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'current_quantity' => 'decimal:3',
            'reorder_level' => 'decimal:3',
            'max_level' => 'decimal:3',
            'reorder_quantity' => 'decimal:3',
            'safety_stock' => 'decimal:3',
            'recommended_quantity' => 'decimal:3',
            'replenishment_action' => ReplenishmentRecommendation::class,
            'metadata' => 'array',
            'is_resolved' => 'boolean',
            'status' => ReorderAlertStatus::class,
            'alerted_at' => 'datetime',
            'acknowledged_at' => 'datetime',
            'resolved_at' => 'datetime',
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

    public function sourceWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'source_warehouse_id');
    }

    public function acknowledger(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acknowledged_by');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function shortageQuantity(): float
    {
        return max(0, (float) $this->reorder_level - (float) $this->current_quantity);
    }

    public function isVelocityAlert(): bool
    {
        return $this->alert_type === config('inventory_intelligence.velocity_alert_type', 'velocity_stockout_risk');
    }
}
