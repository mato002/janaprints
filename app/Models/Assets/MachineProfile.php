<?php

namespace App\Models\Assets;

use App\Enums\MachineCapacityUnit;
use App\Enums\ProductionMachineStatus;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\LogsActivity;
use App\Models\Production\WorkCenter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MachineProfile extends Model
{
    use BelongsToTenant, LogsActivity;

    protected bool $tenantScopedToBranch = true;

    protected $fillable = [
        'company_id',
        'branch_id',
        'fixed_asset_id',
        'machine_code',
        'machine_type',
        'manufacturer',
        'model',
        'serial_number',
        'production_area',
        'installation_date',
        'capacity_unit',
        'capacity_per_hour',
        'capacity_per_shift',
        'is_primary_production_machine',
        'production_status',
        'hourly_capacity',
        'daily_capacity',
        'shift_capacity',
        'monthly_capacity',
        'current_utilization',
    ];

    protected function casts(): array
    {
        return [
            'installation_date' => 'date',
            'capacity_unit' => MachineCapacityUnit::class,
            'capacity_per_hour' => 'decimal:2',
            'capacity_per_shift' => 'decimal:2',
            'is_primary_production_machine' => 'boolean',
            'production_status' => ProductionMachineStatus::class,
            'hourly_capacity' => 'decimal:2',
            'daily_capacity' => 'decimal:2',
            'shift_capacity' => 'decimal:2',
            'monthly_capacity' => 'decimal:2',
            'current_utilization' => 'decimal:2',
        ];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(FixedAsset::class, 'fixed_asset_id');
    }

    public function workCenter(): HasOne
    {
        return $this->hasOne(WorkCenter::class, 'fixed_asset_id', 'fixed_asset_id');
    }

    public function timelineEntries(): HasMany
    {
        return $this->hasMany(MachineTimelineEntry::class, 'fixed_asset_id', 'fixed_asset_id')->latest('occurred_at');
    }

    public function jobAssignments(): HasMany
    {
        return $this->hasMany(MachineJobAssignment::class, 'fixed_asset_id', 'fixed_asset_id')->latest('assigned_at');
    }

    public function scopeProductionMachines(Builder $query): Builder
    {
        return $query->where('production_status', '!=', ProductionMachineStatus::Retired->value);
    }
}
