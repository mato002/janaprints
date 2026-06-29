<?php

namespace App\Models\Crm;

use App\Casts\FlexibleEnumCast;
use App\Enums\CustomerStatus;
use App\Enums\CustomerType;
use App\Models\Branch;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Database\Factories\Crm\CustomerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\User;

class Customer extends Model
{
    /** @use HasFactory<CustomerFactory> */
    use BelongsToTenant, HasFactory, LogsActivity;

    protected bool $tenantScopedToBranch = true;

    protected $fillable = [
        'company_id', 'branch_id', 'customer_code', 'customer_type', 'company_name',
        'contact_person', 'phone', 'alternative_phone', 'email', 'kra_pin',
        'physical_address', 'postal_address', 'city', 'website', 'credit_limit',
        'payment_terms', 'status', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'customer_type' => CustomerType::class,
            'status' => FlexibleEnumCast::class.':'.CustomerStatus::class,
            'credit_limit' => 'decimal:2',
        ];
    }

    public function getNameAttribute(): string
    {
        return $this->company_name ?: ($this->contact_person ?: (string) $this->customer_code);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(CustomerContact::class);
    }

    public function customerNotes(): HasMany
    {
        return $this->hasMany(CustomerNote::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(CustomerFile::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(CustomerActivity::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    public function segments(): BelongsToMany
    {
        return $this->belongsToMany(CustomerSegment::class, 'customer_segment_customer');
    }

    public function portalUsers(): HasMany
    {
        return $this->hasMany(User::class, 'customer_id')
            ->whereNull('employee_id');
    }

    public function portalUser(): HasOne
    {
        return $this->hasOne(User::class, 'customer_id')
            ->whereNull('employee_id');
    }

    public function hasPortalAccess(): bool
    {
        return $this->portalUsers()->exists();
    }

    public function artworks(): HasMany
    {
        return $this->hasMany(CustomerArtwork::class)->orderByDesc('uploaded_at');
    }

    public function activeArtworks(): HasMany
    {
        return $this->hasMany(CustomerArtwork::class)
            ->where('is_active_version', true)
            ->where('status', \App\Enums\CustomerArtworkStatus::Active)
            ->orderBy('artwork_name');
    }

    public function productSerialProfiles(): HasMany
    {
        return $this->hasMany(CustomerProductSerialProfile::class);
    }

    public function printSpecifications(): HasMany
    {
        return $this->hasMany(CustomerPrintSpecification::class)->orderByDesc('updated_at');
    }

    public function activePrintSpecifications(): HasMany
    {
        return $this->hasMany(CustomerPrintSpecification::class)
            ->where('status', \App\Enums\CustomerPrintSpecificationStatus::Active)
            ->whereNotNull('inventory_item_id')
            ->orderBy('name');
    }
}
