<?php

namespace App\Models\Production;

use App\Enums\MaterialRequirementStatus;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\ProductionMaterialConsumption;
use App\Models\Inventory\Warehouse;
use App\Models\Sales\SalesOrderItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductionMaterialRequirement extends Model
{
    use BelongsToTenant;

    protected bool $tenantScopedToBranch = true;

    protected $fillable = [
        'company_id', 'branch_id', 'production_job_card_id', 'product_bom_id',
        'finished_item_id', 'sales_order_item_id', 'inventory_item_id', 'warehouse_id',
        'job_quantity', 'quantity_formula', 'required_quantity', 'reserved_quantity',
        'consumed_quantity', 'issued_quantity', 'waste_quantity', 'returned_quantity',
        'unit_cost', 'estimated_cost', 'status', 'generated_by', 'generated_at',
    ];

    protected function casts(): array
    {
        return [
            'job_quantity' => 'decimal:3',
            'required_quantity' => 'decimal:3',
            'reserved_quantity' => 'decimal:3',
            'consumed_quantity' => 'decimal:3',
            'issued_quantity' => 'decimal:3',
            'waste_quantity' => 'decimal:3',
            'returned_quantity' => 'decimal:3',
            'unit_cost' => 'decimal:2',
            'estimated_cost' => 'decimal:2',
            'status' => MaterialRequirementStatus::class,
            'generated_at' => 'datetime',
        ];
    }

    public function jobCard(): BelongsTo
    {
        return $this->belongsTo(ProductionJobCard::class, 'production_job_card_id');
    }

    public function bom(): BelongsTo
    {
        return $this->belongsTo(ProductBom::class, 'product_bom_id');
    }

    public function finishedItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'finished_item_id');
    }

    public function salesOrderItem(): BelongsTo
    {
        return $this->belongsTo(SalesOrderItem::class);
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function consumptions(): HasMany
    {
        return $this->hasMany(ProductionMaterialConsumption::class, 'production_material_requirement_id');
    }

    public function remainingQuantity(): float
    {
        $consumed = (float) ProductionMaterialConsumption::query()
            ->where('production_job_card_id', $this->production_job_card_id)
            ->where('inventory_item_id', $this->inventory_item_id)
            ->where(function ($query) {
                $query->where('production_material_requirement_id', $this->id)
                    ->orWhereNull('production_material_requirement_id');
            })
            ->when($this->warehouse_id, fn ($query) => $query->where('warehouse_id', $this->warehouse_id))
            ->sum('quantity');

        return max(0, round((float) $this->required_quantity - $consumed, 3));
    }

    public function unreservedRemaining(): float
    {
        return max(0, round($this->remainingQuantity() - (float) $this->reserved_quantity, 3));
    }

    public function remainingToIssue(): float
    {
        return max(0, round((float) $this->required_quantity - (float) $this->issued_quantity, 3));
    }

    public function issues(): HasMany
    {
        return $this->hasMany(ProductionMaterialIssue::class, 'production_material_requirement_id');
    }
}
