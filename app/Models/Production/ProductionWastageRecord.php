<?php

namespace App\Models\Production;

use App\Enums\ProductionMaterialFlowType;
use App\Enums\ProductionWasteType;
use App\Models\Assets\MachineProfile;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Employee;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\InventoryMovement;
use App\Models\Inventory\Warehouse;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionWastageRecord extends Model
{
    use BelongsToTenant;

    protected bool $tenantScopedToBranch = true;

    protected $fillable = [
        'company_id', 'branch_id', 'production_job_card_id', 'inventory_item_id', 'warehouse_id',
        'flow_type', 'waste_type', 'custom_reason', 'quantity', 'unit_cost', 'line_cost',
        'inventory_movement_id', 'employee_id', 'machine_profile_id', 'recorded_by', 'recorded_at', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'flow_type' => ProductionMaterialFlowType::class,
            'waste_type' => ProductionWasteType::class,
            'quantity' => 'decimal:3',
            'unit_cost' => 'decimal:2',
            'line_cost' => 'decimal:2',
            'recorded_at' => 'datetime',
        ];
    }

    public function jobCard(): BelongsTo
    {
        return $this->belongsTo(ProductionJobCard::class, 'production_job_card_id');
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

    public function operator(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function machineProfile(): BelongsTo
    {
        return $this->belongsTo(MachineProfile::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
