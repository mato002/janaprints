<?php

namespace App\Models\Sales;

use App\Enums\FulfilmentMethod;
use App\Enums\ProductionDestination;
use App\Enums\ProductionPriority;
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
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
        'order_number', 'order_date', 'required_date', 'priority', 'production_destination', 'status',
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
            'production_destination' => ProductionDestination::class,
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

    public function linkedInvoices(): BelongsToMany
    {
        return $this->belongsToMany(CustomerInvoice::class, 'customer_invoice_sales_orders')
            ->withPivot(['allocated_subtotal', 'allocated_tax', 'allocated_total'])
            ->withTimestamps();
    }

    public function pendingInvoiceTotal(): float
    {
        return app(\App\Support\Sales\CustomerInvoiceService::class)->reservedInvoiceTotal($this);
    }

    public function remainingInvoiceTotal(): float
    {
        return round(max(0, $this->billedTotal() - (float) $this->invoiced_total - $this->pendingInvoiceTotal()), 2);
    }

    public function billedTotal(): float
    {
        $this->loadMissing(['items.productionSpecification', 'customerPrintSpecification']);

        if ($this->items->isNotEmpty()) {
            $fromQtyPrice = round((float) $this->items->sum(
                fn ($item) => $this->billedQuantityForItem($item) * (float) $item->unit_price
            ), 2);

            if ($fromQtyPrice > 0) {
                return $fromQtyPrice;
            }

            $fromLineTotal = round((float) $this->items->sum('line_total'), 2);
            if ($fromLineTotal > 0) {
                return $fromLineTotal;
            }
        }

        $header = round((float) $this->total_amount, 2);
        if ($header > 0) {
            return $header;
        }

        $spec = $this->customerPrintSpecification;

        if (! $spec) {
            return 0.0;
        }

        $quantity = (float) ($this->items->first()?->quantity ?? 0);
        if ($quantity <= 0) {
            $quantity = (float) ($spec->default_quantity ?? 1);
        }

        $price = (float) ($spec->default_unit_price ?? 0);
        if ($price <= 0) {
            $sheet = is_array($spec->job_sheet_payload) ? $spec->job_sheet_payload : [];
            $price = (float) ($sheet['price'] ?? 0);
            if ($price <= 0 && isset($sheet['selling_price']) && (float) $sheet['selling_price'] > 0 && $quantity > 0) {
                $price = round((float) $sheet['selling_price'] / $quantity, 2);
            }
        }

        return round($quantity * $price, 2);
    }

    public function syncStoredCommercialsFromLines(): void
    {
        $this->loadMissing(['items.productionSpecification', 'customerPrintSpecification']);

        if ($this->items->isEmpty()) {
            return;
        }

        foreach ($this->items as $item) {
            $quantity = $this->billedQuantityForItem($item);
            $expected = round($quantity * (float) $item->unit_price, 2);
            $updates = [];

            if (abs((float) $item->quantity - $quantity) > 0.0005) {
                $updates['quantity'] = $quantity;
            }

            if (round((float) $item->line_total, 2) !== $expected) {
                $updates['line_total'] = $expected;
            }

            if ($updates !== []) {
                $item->forceFill($updates)->saveQuietly();
            }
        }

        $this->unsetRelation('items');
        $this->recalculateTotalsFromItems();
    }

    public function recalculateTotalsFromItems(): void
    {
        $this->loadMissing('items');

        $subtotal = round((float) $this->items->sum(
            fn ($item) => (float) $item->quantity * (float) $item->unit_price
        ), 2);

        $this->items->each(function ($item) {
            $expected = round((float) $item->quantity * (float) $item->unit_price, 2);
            if (round((float) $item->line_total, 2) !== $expected) {
                $item->forceFill(['line_total' => $expected])->saveQuietly();
            }
        });

        if (round((float) $this->subtotal, 2) !== $subtotal || round((float) $this->total_amount, 2) !== $subtotal) {
            $this->forceFill([
                'subtotal' => $subtotal,
                'total_amount' => $subtotal,
            ])->saveQuietly();
        }
    }

    protected function billedQuantityForItem(SalesOrderItem $item): float
    {
        $quantity = (float) $item->quantity;
        $price = (float) $item->unit_price;

        if ($quantity > 1 || $price <= 0) {
            return max($quantity, 0);
        }

        $stored = (float) ($item->line_total ?: $this->total_amount);
        $looksUnmultiplied = $stored <= 0 || abs($stored - $price) < 0.02;

        if (! $looksUnmultiplied) {
            return max($quantity, 0);
        }

        $productionQty = (float) ($item->productionSpecification?->quantity ?? 0);
        $specQty = (float) ($this->customerPrintSpecification?->default_quantity ?? 0);
        $candidate = $productionQty > 1 ? $productionQty : $specQty;

        return $candidate > 1 ? $candidate : max($quantity, 0);
    }

    /**
     * @return array<string, mixed>
     */
    public function productionJobAttributes(): array
    {
        if (! $this->production_destination) {
            return [];
        }

        $attributes = [
            'production_destination' => $this->production_destination->value,
        ];

        if ($type = $this->production_destination->productionType()) {
            $attributes['production_type'] = $type->value;
        }

        return $attributes;
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
