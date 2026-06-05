<?php

namespace App\Models\Assets;

use App\Enums\AssetWarrantyStatus;
use App\Models\Company;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Procurement\Vendor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetWarranty extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'company_id',
        'fixed_asset_id',
        'vendor_id',
        'warranty_start',
        'warranty_end',
        'coverage',
        'support_contact',
        'reference_number',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'warranty_start' => 'date',
            'warranty_end' => 'date',
            'status' => AssetWarrantyStatus::class,
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(FixedAsset::class, 'fixed_asset_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}
