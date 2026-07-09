<?php

namespace App\Models\Assets;

use App\Enums\MaintenancePriority;
use App\Enums\MaintenanceType;
use App\Enums\MaintenanceWorkOrderStatus;
use App\Models\Branch;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\HasPublicHash;
use App\Models\Concerns\LogsActivity;
use App\Models\Procurement\Vendor;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MaintenanceWorkOrder extends Model
{
    use BelongsToTenant, HasPublicHash, LogsActivity;

    protected bool $tenantScopedToBranch = true;

    protected $fillable = [
        'company_id',
        'branch_id',
        'fixed_asset_id',
        'work_order_no',
        'maintenance_type',
        'priority',
        'status',
        'opened_at',
        'scheduled_for',
        'completed_at',
        'requested_by',
        'assigned_to',
        'assigned_technician_id',
        'vendor_id',
        'maintenance_plan_id',
        'description',
        'findings',
        'resolution',
    ];

    protected function casts(): array
    {
        return [
            'maintenance_type' => MaintenanceType::class,
            'priority' => MaintenancePriority::class,
            'status' => MaintenanceWorkOrderStatus::class,
            'opened_at' => 'datetime',
            'scheduled_for' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(FixedAsset::class, 'fixed_asset_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(MaintenanceTechnician::class, 'assigned_technician_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(MaintenancePlan::class, 'maintenance_plan_id');
    }

    public function incident(): HasOne
    {
        return $this->hasOne(MaintenanceIncident::class);
    }

    public function downtimeRecords(): HasMany
    {
        return $this->hasMany(AssetDowntimeRecord::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(MaintenanceLog::class)->latest('logged_at');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(MaintenanceWorkOrderStatusHistory::class)->latest('changed_at');
    }

    public function blocksProduction(): bool
    {
        if (! $this->status->blocksProduction()) {
            return false;
        }

        return $this->maintenance_type->blocksProduction()
            || $this->maintenance_type === MaintenanceType::Emergency
            || $this->priority->isCritical();
    }
}
