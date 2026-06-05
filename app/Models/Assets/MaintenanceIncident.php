<?php

namespace App\Models\Assets;

use App\Enums\MaintenancePriority;
use App\Models\Concerns\BelongsToTenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceIncident extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'company_id',
        'fixed_asset_id',
        'maintenance_work_order_id',
        'incident_no',
        'severity',
        'title',
        'description',
        'reported_at',
        'reported_by',
    ];

    protected function casts(): array
    {
        return [
            'severity' => MaintenancePriority::class,
            'reported_at' => 'datetime',
        ];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(FixedAsset::class, 'fixed_asset_id');
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(MaintenanceWorkOrder::class, 'maintenance_work_order_id');
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }
}
