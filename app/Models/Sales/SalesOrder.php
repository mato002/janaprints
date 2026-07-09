<?php

namespace App\Models\Sales;

use App\Enums\FulfilmentMethod;
use App\Enums\ProductionPriority;
use App\Enums\CustomerInvoiceStatus;
use App\Enums\SalesOrderBillingType;
use App\Enums\SalesOrderStatus;
use App\Models\Artwork\ArtworkRequest;
use App\Models\Crm\CustomerArtwork;
use App\Models\Crm\CustomerPrintSpecification;
use App\Models\Inventory\InventoryItem;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\HasPublicHash;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionSpecification;
use App\Models\Concerns\LogsActivity;
use App\Models\Crm\Customer;
use App\Models\User;
use Database\Factories\Sales\SalesOrderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SalesOrder extends Model
{
    /** @use HasFactory<SalesOrderFactory> */
    use BelongsToTenant, HasFactory, HasPublicHash, LogsActivity;

    protected bool $tenantScopedToBranch = true;

    protected $fillable = [
        'company_id', 'branch_id', 'customer_id', 'customer_print_specification_id',
        'quotation_id', 'artwork_request_id',
        'inventory_item_id', 'uses_existing_artwork', 'customer_artwork_id',
        'artwork_confirmed_by', 'artwork_confirmed_at',
        'order_number', 'order_date', 'required_date', 'priority', 'status',
        'subtotal', 'tax_amount', 'discount_amount', 'total_amount',
        'invoiced_subtotal', 'invoiced_tax_amount', 'invoiced_total',
        'notes', 'created_by',
        'is_direct_order', 'repeat_source_sales_order_id',
        'fulfilment_method',
        'billing_type', 'payment_terms_days',
        'required_deposit_amount', 'deposit_invoiced_amount', 'deposit_paid_amount',
    ];

    protected function casts(): array
    {
        return [
            'status' => SalesOrderStatus::class,
            'order_date' => 'date',
            'required_date' => 'date',
            'subtotal' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'invoiced_subtotal' => 'decimal:2',
            'invoiced_tax_amount' => 'decimal:2',
            'invoiced_total' => 'decimal:2',
            'uses_existing_artwork' => 'boolean',
            'artwork_confirmed_at' => 'datetime',
            'is_direct_order' => 'boolean',
            'priority' => ProductionPriority::class,
            'fulfilment_method' => FulfilmentMethod::class,
            'billing_type' => SalesOrderBillingType::class,
            'payment_terms_days' => 'integer',
            'required_deposit_amount' => 'decimal:2',
            'deposit_invoiced_amount' => 'decimal:2',
            'deposit_paid_amount' => 'decimal:2',
        ];
    }

    public function customerPrintSpecification(): BelongsTo
    {
        return $this->belongsTo(CustomerPrintSpecification::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function artworkRequest(): BelongsTo
    {
        return $this->belongsTo(ArtworkRequest::class);
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function customerArtwork(): BelongsTo
    {
        return $this->belongsTo(CustomerArtwork::class);
    }

    public function artworkConfirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'artwork_confirmed_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesOrderItem::class)->orderBy('sort_order');
    }

    public function orderNotes(): HasMany
    {
        return $this->hasMany(SalesOrderNote::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(SalesOrderAttachment::class);
    }

    public function conversion(): HasOne
    {
        return $this->hasOne(QuotationConversion::class);
    }

    public function jobCard(): HasOne
    {
        return $this->hasOne(ProductionJobCard::class);
    }

    public function repeatSource(): BelongsTo
    {
        return $this->belongsTo(self::class, 'repeat_source_sales_order_id');
    }

    public function productionSpecifications(): HasMany
    {
        return $this->hasMany(ProductionSpecification::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(CustomerInvoice::class);
    }

    public function pendingInvoiceTotal(): float
    {
        return (float) $this->invoices()
            ->whereIn('status', [
                CustomerInvoiceStatus::Draft,
                CustomerInvoiceStatus::Approved,
            ])
            ->sum('total_amount');
    }

    public function remainingInvoiceTotal(): float
    {
        return round(max(0, (float) $this->total_amount - (float) $this->invoiced_total - $this->pendingInvoiceTotal()), 2);
    }

    public function transitionTo(SalesOrderStatus $status): void
    {
        if (! $this->status->canTransitionTo($status)) {
            throw new \InvalidArgumentException(
                "Cannot transition from {$this->status->value} to {$status->value}",
            );
        }

        $this->update(['status' => $status]);
    }
}
