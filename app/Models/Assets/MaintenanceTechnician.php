<?php

namespace App\Models\Assets;

use App\Enums\MaintenanceTechnicianStatus;
use App\Enums\MaintenanceTechnicianType;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Procurement\Vendor;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaintenanceTechnician extends Model
{
    use BelongsToTenant;

    protected bool $tenantScopedToBranch = true;

    protected $fillable = [
        'company_id',
        'branch_id',
        'user_id',
        'vendor_id',
        'technician_type',
        'name',
        'phone',
        'email',
        'specialization',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'technician_type' => MaintenanceTechnicianType::class,
            'status' => MaintenanceTechnicianStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function assignedWorkOrders(): HasMany
    {
        return $this->hasMany(MaintenanceWorkOrder::class, 'assigned_technician_id');
    }
}
