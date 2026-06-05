<?php

namespace App\Models\Assets;

use App\Enums\MaintenanceFrequencyType;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaintenancePlan extends Model
{
    use BelongsToTenant;

    protected bool $tenantScopedToBranch = true;

    protected $fillable = [
        'company_id',
        'branch_id',
        'fixed_asset_id',
        'plan_name',
        'frequency_type',
        'frequency_value',
        'next_due_date',
        'last_completed_date',
        'is_active',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'frequency_type' => MaintenanceFrequencyType::class,
            'next_due_date' => 'date',
            'last_completed_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(FixedAsset::class, 'fixed_asset_id');
    }

    public function workOrders(): HasMany
    {
        return $this->hasMany(MaintenanceWorkOrder::class);
    }
}
