<?php

namespace App\Models\Procurement;

use App\Enums\SupplierQuotationStatus;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\LogsActivity;
use Database\Factories\Procurement\SupplierQuotationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupplierQuotation extends Model
{
    /** @use HasFactory<SupplierQuotationFactory> */
    use BelongsToTenant, HasFactory, LogsActivity;

    protected bool $tenantScopedToBranch = true;

    protected $fillable = [
        'company_id',
        'branch_id',
        'rfq_id',
        'purchase_request_id',
        'vendor_id',
        'quotation_number',
        'quotation_date',
        'valid_until',
        'status',
        'subtotal',
        'tax_amount',
        'total_amount',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quotation_date' => 'date',
            'valid_until' => 'date',
            'status' => SupplierQuotationStatus::class,
            'subtotal' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    public function rfq(): BelongsTo
    {
        return $this->belongsTo(Rfq::class);
    }

    public function purchaseRequest(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SupplierQuotationItem::class);
    }
}
