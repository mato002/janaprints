<?php

namespace App\Models\Assets;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MachineOperatorAssignment extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'fixed_asset_id',
        'employee_id',
        'is_primary',
        'start_date',
        'end_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(FixedAsset::class, 'fixed_asset_id');
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
