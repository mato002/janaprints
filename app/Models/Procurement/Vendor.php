<?php

namespace App\Models\Procurement;

use App\Enums\VendorStatus;
use App\Enums\VendorType;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\LogsActivity;
use Database\Factories\Procurement\VendorFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vendor extends Model
{
    /** @use HasFactory<VendorFactory> */
    use BelongsToTenant, HasFactory, LogsActivity;

    protected $fillable = [
        'company_id',
        'vendor_code',
        'vendor_name',
        'vendor_type',
        'phone',
        'email',
        'kra_pin',
        'address',
        'payment_terms',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'vendor_type' => VendorType::class,
            'status' => VendorStatus::class,
        ];
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(VendorContact::class);
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function supplierQuotations(): HasMany
    {
        return $this->hasMany(SupplierQuotation::class);
    }
}
