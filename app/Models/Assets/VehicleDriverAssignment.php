<?php

namespace App\Models\Assets;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleDriverAssignment extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'vehicle_asset_id',
        'employee_id',
        'assigned_date',
        'license_number',
        'end_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'assigned_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(FixedAsset::class, 'vehicle_asset_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function isActive(): bool
    {
        return $this->end_date === null || $this->end_date->isFuture();
    }
}
