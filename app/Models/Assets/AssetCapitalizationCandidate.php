<?php

namespace App\Models\Assets;

use App\Enums\CapitalizationCandidateStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Procurement\GoodsReceipt;
use App\Models\Procurement\GoodsReceiptItem;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\PurchaseOrderItem;
use App\Models\Procurement\Vendor;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssetCapitalizationCandidate extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'company_id',
        'branch_id',
        'candidate_number',
        'goods_receipt_id',
        'goods_receipt_item_id',
        'purchase_order_id',
        'purchase_order_item_id',
        'vendor_id',
        'asset_category_id',
        'quantity',
        'quantity_capitalized',
        'unit_cost',
        'line_amount',
        'status',
        'received_date',
        'notes',
        'capitalized_at',
        'capitalized_by',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'quantity_capitalized' => 'decimal:3',
            'unit_cost' => 'decimal:2',
            'line_amount' => 'decimal:2',
            'status' => CapitalizationCandidateStatus::class,
            'received_date' => 'date',
            'capitalized_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function goodsReceipt(): BelongsTo
    {
        return $this->belongsTo(GoodsReceipt::class);
    }

    public function goodsReceiptItem(): BelongsTo
    {
        return $this->belongsTo(GoodsReceiptItem::class);
    }

    public function purchaseOrderItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderItem::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'asset_category_id');
    }

    public function capitalizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'capitalized_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function fixedAssets(): HasMany
    {
        return $this->hasMany(FixedAsset::class, 'capitalization_candidate_id');
    }

    public function remainingQuantity(): float
    {
        return max((float) $this->quantity - (float) $this->quantity_capitalized, 0);
    }
}
