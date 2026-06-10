<?php

namespace App\Models\Inventory;

use App\Enums\InventoryRiskLevel;
use App\Enums\InventoryVelocityClass;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryVelocitySnapshot extends Model
{
    use BelongsToTenant;

    protected bool $tenantScopedToBranch = true;

    protected $fillable = [
        'company_id',
        'branch_id',
        'inventory_item_id',
        'warehouse_id',
        'stock_role',
        'period_start',
        'period_end',
        'movement_window_days',
        'opening_balance',
        'closing_balance',
        'total_in_quantity',
        'total_out_quantity',
        'net_quantity',
        'average_daily_consumption',
        'average_weekly_consumption',
        'days_to_depletion',
        'velocity_class',
        'risk_level',
        'last_inbound_at',
        'last_outbound_at',
        'last_movement_at',
        'metadata',
        'generated_at',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'opening_balance' => 'decimal:3',
            'closing_balance' => 'decimal:3',
            'total_in_quantity' => 'decimal:3',
            'total_out_quantity' => 'decimal:3',
            'net_quantity' => 'decimal:3',
            'average_daily_consumption' => 'decimal:4',
            'average_weekly_consumption' => 'decimal:4',
            'days_to_depletion' => 'decimal:2',
            'velocity_class' => InventoryVelocityClass::class,
            'risk_level' => InventoryRiskLevel::class,
            'last_inbound_at' => 'datetime',
            'last_outbound_at' => 'datetime',
            'last_movement_at' => 'datetime',
            'metadata' => 'array',
            'generated_at' => 'datetime',
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
