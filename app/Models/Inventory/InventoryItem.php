<?php

namespace App\Models\Inventory;

use App\Enums\InventoryStockRole;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\LogsActivity;
use Database\Factories\Inventory\InventoryItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryItem extends Model
{
    /** @use HasFactory<InventoryItemFactory> */
    use BelongsToTenant, HasFactory, LogsActivity;

    protected bool $tenantScopedToBranch = true;

    protected $fillable = [
        'company_id', 'branch_id', 'inventory_category_id', 'subcategory_id',
        'brand_id', 'brand_name', 'unit_of_measure_id', 'sku', 'item_name',
        'description',         'reorder_level', 'reorder_quantity', 'standard_cost',
        'is_active', 'stock_role',
        'uses_serial_numbers', 'serial_prefix', 'serial_padding_length',
        'requires_customer_approval',
    ];

    protected function casts(): array
    {
        return [
            'reorder_level' => 'decimal:3',
            'reorder_quantity' => 'decimal:3',
            'standard_cost' => 'decimal:2',
            'is_active' => 'boolean',
            'stock_role' => InventoryStockRole::class,
            'uses_serial_numbers' => 'boolean',
            'requires_customer_approval' => 'boolean',
            'serial_padding_length' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(InventoryCategory::class, 'inventory_category_id');
    }

    public function unitOfMeasure(): BelongsTo
    {
        return $this->belongsTo(UnitOfMeasure::class);
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(InventorySubcategory::class, 'subcategory_id');
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function attributeValues(): HasMany
    {
        return $this->hasMany(InventoryItemAttribute::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(InventoryItemImage::class)->orderByDesc('is_primary')->orderBy('sort_order');
    }

    public function priceListItems(): HasMany
    {
        return $this->hasMany(PriceListItem::class);
    }

    public function productionRouteSteps(): HasMany
    {
        return $this->hasMany(ProductProductionRouteStep::class)->orderBy('sequence');
    }

    public function activeProductionRouteSteps(): HasMany
    {
        return $this->hasMany(ProductProductionRouteStep::class)
            ->where('is_active', true)
            ->orderBy('sequence');
    }
}
