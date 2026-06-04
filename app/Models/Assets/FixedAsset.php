<?php

namespace App\Models\Assets;

use App\Enums\FixedAssetStatus;
use App\Models\Branch;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\LogsActivity;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FixedAsset extends Model
{
    use BelongsToTenant, LogsActivity;

    protected $fillable = [
        'company_id',
        'branch_id',
        'asset_category_id',
        'asset_number',
        'asset_name',
        'barcode',
        'serial_number',
        'acquisition_date',
        'acquisition_cost',
        'residual_value',
        'accumulated_depreciation',
        'status',
        'assigned_to_user_id',
        'assigned_to_branch_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'acquisition_date' => 'date',
            'acquisition_cost' => 'decimal:2',
            'residual_value' => 'decimal:2',
            'accumulated_depreciation' => 'decimal:2',
            'status' => FixedAssetStatus::class,
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'asset_category_id');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function assignedBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'assigned_to_branch_id');
    }

    public function maintenances(): HasMany
    {
        return $this->hasMany(AssetMaintenance::class);
    }

    public function depreciationEntries(): HasMany
    {
        return $this->hasMany(AssetDepreciationEntry::class);
    }

    public function netBookValue(): float
    {
        return max(0, (float) $this->acquisition_cost - (float) $this->accumulated_depreciation);
    }
}
