<?php

namespace App\Models\Crm;

use App\Enums\CustomerPrintSpecificationStatus;
use App\Enums\FulfilmentMethod;
use App\Enums\SalesOrderBillingType;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\LogsActivity;
use App\Models\Inventory\InventoryItem;
use App\Models\Production\ProductionJobCard;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CustomerPrintSpecification extends Model
{
    use BelongsToTenant, LogsActivity;

    protected bool $tenantScopedToBranch = true;

    protected $fillable = [
        'company_id',
        'branch_id',
        'customer_id',
        'inventory_item_id',
        'specification_code',
        'name',
        'description',
        'status',
        'production_notes',
        'commercial_notes',
        'customer_instructions',
        'default_quantity',
        'default_unit_price',
        'default_billing_type',
        'default_fulfilment_method',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => CustomerPrintSpecificationStatus::class,
            'default_quantity' => 'decimal:3',
            'default_unit_price' => 'decimal:2',
            'default_billing_type' => SalesOrderBillingType::class,
            'default_fulfilment_method' => FulfilmentMethod::class,
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function salesOrders(): HasMany
    {
        return $this->hasMany(SalesOrder::class, 'customer_print_specification_id');
    }

    public function jobCards(): HasMany
    {
        return $this->hasMany(ProductionJobCard::class, 'customer_print_specification_id');
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function artworkVersions(): HasMany
    {
        return $this->hasMany(CustomerArtwork::class, 'customer_print_specification_id')
            ->orderByDesc('version_number');
    }

    public function activeArtworkVersion(): HasOne
    {
        return $this->hasOne(CustomerArtwork::class, 'customer_print_specification_id')
            ->where('is_active_version', true)
            ->where('status', \App\Enums\CustomerArtworkStatus::Active);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function isSelectableForOrders(): bool
    {
        return $this->status->isSelectableForOrders()
            && $this->inventory_item_id !== null;
    }

    public function isReadOnly(): bool
    {
        return $this->status->isReadOnly();
    }

    public function hasOperationalUsage(): bool
    {
        if ($this->relationLoaded('salesOrders')) {
            if ($this->salesOrders->isNotEmpty()) {
                return true;
            }
        } elseif ($this->salesOrders()->exists()) {
            return true;
        }

        if ($this->relationLoaded('jobCards')) {
            return $this->jobCards->isNotEmpty();
        }

        return $this->jobCards()->exists();
    }
}
