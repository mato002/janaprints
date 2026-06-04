<?php

namespace App\Models\Procurement;

use App\Models\Inventory\InventoryItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierQuotationItem extends Model
{
    protected $fillable = [
        'supplier_quotation_id',
        'purchase_request_item_id',
        'inventory_item_id',
        'description',
        'quantity',
        'unit_cost',
        'line_total',
        'lead_time_days',
        'warranty',
        'delivery_terms',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'unit_cost' => 'decimal:2',
            'line_total' => 'decimal:2',
        ];
    }

    public function supplierQuotation(): BelongsTo
    {
        return $this->belongsTo(SupplierQuotation::class);
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }
}
