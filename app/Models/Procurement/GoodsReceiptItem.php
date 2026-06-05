<?php

namespace App\Models\Procurement;

use App\Enums\ProcurementItemClassification;
use App\Models\Assets\AssetCategory;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\StockReceiptItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoodsReceiptItem extends Model
{
    protected $fillable = [
        'goods_receipt_id',
        'purchase_order_item_id',
        'inventory_item_id',
        'item_classification',
        'asset_category_id',
        'quantity_received',
        'unit_cost',
        'stock_receipt_item_id',
    ];

    protected function casts(): array
    {
        return [
            'quantity_received' => 'decimal:3',
            'unit_cost' => 'decimal:2',
            'item_classification' => ProcurementItemClassification::class,
        ];
    }

    public function assetCategory(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'asset_category_id');
    }

    public function isCapitalizable(): bool
    {
        return $this->item_classification?->isCapitalizable() ?? false;
    }

    public function goodsReceipt(): BelongsTo
    {
        return $this->belongsTo(GoodsReceipt::class);
    }

    public function purchaseOrderItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderItem::class);
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function stockReceiptItem(): BelongsTo
    {
        return $this->belongsTo(StockReceiptItem::class);
    }
}
