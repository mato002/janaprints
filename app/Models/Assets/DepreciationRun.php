<?php

namespace App\Models\Assets;

use App\Enums\DepreciationRunStatus;
use App\Models\Concerns\BelongsToTenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DepreciationRun extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'company_id',
        'branch_id',
        'run_number',
        'period',
        'start_date',
        'end_date',
        'run_date',
        'status',
        'executed_by',
        'is_dry_run',
        'total_depreciation',
        'assets_processed',
        'preview_summary',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'run_date' => 'date',
            'status' => DepreciationRunStatus::class,
            'is_dry_run' => 'boolean',
            'total_depreciation' => 'decimal:2',
            'preview_summary' => 'array',
        ];
    }

    public function executor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'executed_by');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(AssetDepreciationEntry::class);
    }
}
