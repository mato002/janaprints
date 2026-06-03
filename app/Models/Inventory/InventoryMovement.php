<?php

namespace App\Models\Inventory;

use App\Enums\InventoryMovementType;
use App\Models\Concerns\BelongsToTenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class InventoryMovement extends Model
{
    use BelongsToTenant;

    protected bool $tenantScopedToBranch = true;

    public $timestamps = false;

    protected $fillable = [
        'company_id', 'branch_id', 'inventory_item_id', 'warehouse_id',
        'movement_type', 'quantity', 'unit_cost', 'reference_type', 'reference_id',
        'movement_date', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'movement_type' => InventoryMovementType::class,
            'quantity' => 'decimal:3',
            'unit_cost' => 'decimal:2',
            'movement_date' => 'date',
            'created_at' => 'datetime',
        ];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reference(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'reference_type', 'reference_id');
    }
}
