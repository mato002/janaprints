<?php

namespace App\Models\Procurement;

use App\Enums\ProcurementItemClassification;
use App\Models\Assets\AssetCategory;
use App\Models\Inventory\InventoryItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseRequestItem extends Model
{
    protected $fillable = [
        'purchase_request_id',
        'inventory_item_id',
        'item_classification',
        'asset_category_id',
        'description',
        'quantity',
        'estimated_unit_cost',
        'line_total',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'estimated_unit_cost' => 'decimal:2',
            'line_total' => 'decimal:2',
            'item_classification' => ProcurementItemClassification::class,
        ];
    }

    public function assetCategory(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'asset_category_id');
    }

    public function purchaseRequest(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }
}
