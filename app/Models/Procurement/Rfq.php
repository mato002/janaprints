<?php

namespace App\Models\Procurement;

use App\Enums\RfqStatus;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\LogsActivity;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Rfq extends Model
{
    use BelongsToTenant, LogsActivity;

    protected bool $tenantScopedToBranch = true;

    protected $fillable = [
        'company_id',
        'branch_id',
        'purchase_request_id',
        'rfq_number',
        'issue_date',
        'closing_date',
        'status',
        'awarded_vendor_id',
        'award_type',
        'purchase_order_id',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'closing_date' => 'date',
            'status' => RfqStatus::class,
        ];
    }

    public function purchaseRequest(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function awardedVendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'awarded_vendor_id');
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function vendors(): HasMany
    {
        return $this->hasMany(RfqVendor::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(RfqItem::class);
    }

    public function responses(): HasMany
    {
        return $this->hasMany(RfqVendorResponse::class);
    }

    public function comparison(): HasOne
    {
        return $this->hasOne(VendorComparison::class);
    }

    public function awardLines(): HasMany
    {
        return $this->hasMany(RfqAwardLine::class);
    }
}
