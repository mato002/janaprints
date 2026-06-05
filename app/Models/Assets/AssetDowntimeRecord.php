<?php

namespace App\Models\Assets;

use App\Enums\DowntimeImpactLevel;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetDowntimeRecord extends Model
{
    use BelongsToTenant;

    protected bool $tenantScopedToBranch = true;

    protected $fillable = [
        'company_id',
        'branch_id',
        'fixed_asset_id',
        'maintenance_work_order_id',
        'start_time',
        'end_time',
        'duration_minutes',
        'reason',
        'impact_level',
    ];

    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'impact_level' => DowntimeImpactLevel::class,
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (AssetDowntimeRecord $record) {
            if ($record->start_time && $record->end_time) {
                $record->duration_minutes = max(0, (int) $record->start_time->diffInMinutes($record->end_time));
            }
        });
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(FixedAsset::class, 'fixed_asset_id');
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(MaintenanceWorkOrder::class, 'maintenance_work_order_id');
    }

    public function durationHours(): float
    {
        return round($this->duration_minutes / 60, 2);
    }

    public function durationDays(): float
    {
        return round($this->duration_minutes / 1440, 2);
    }

    public function isActive(): bool
    {
        return $this->end_time === null;
    }
}
