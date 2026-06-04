<?php

namespace App\Models\Procurement;

use App\Enums\SupplierBillLineType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierBillLine extends Model
{
    protected $fillable = [
        'supplier_bill_id',
        'purchase_order_item_id',
        'goods_receipt_item_id',
        'line_type',
        'item_name',
        'description',
        'quantity',
        'unit_cost',
        'discount',
        'tax_code_id',
        'tax_rate',
        'line_subtotal',
        'tax_amount',
        'line_total',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'line_type' => SupplierBillLineType::class,
            'quantity' => 'decimal:4',
            'unit_cost' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'line_subtotal' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'line_total' => 'decimal:2',
        ];
    }

    public function bill(): BelongsTo
    {
        return $this->belongsTo(SupplierBill::class, 'supplier_bill_id');
    }

    public function purchaseOrderItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderItem::class);
    }

    public function goodsReceiptItem(): BelongsTo
    {
        return $this->belongsTo(GoodsReceiptItem::class);
    }
}
