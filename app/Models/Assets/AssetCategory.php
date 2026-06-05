<?php

namespace App\Models\Assets;

use App\Enums\AssetType;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssetCategory extends Model
{
    use BelongsToTenant, LogsActivity;

    protected $fillable = [
        'company_id',
        'name',
        'code',
        'asset_type',
        'useful_life_months',
        'useful_life_years',
        'depreciation_rate_percent',
        'depreciation_method',
        'default_gl_code',
        'accumulated_depreciation_gl_code',
        'depreciation_expense_gl_code',
        'asset_disposal_gl_code',
        'asset_gain_loss_gl_code',
        'description',
        'is_active',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'asset_type' => AssetType::class,
            'depreciation_rate_percent' => 'decimal:2',
            'is_active' => 'boolean',
            'archived_at' => 'datetime',
        ];
    }

    public function assets(): HasMany
    {
        return $this->hasMany(FixedAsset::class);
    }

    public function activeAssets(): HasMany
    {
        return $this->assets()->whereNull('archived_at');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->whereNull('archived_at');
    }

    public function scopeNotArchived(Builder $query): Builder
    {
        return $query->whereNull('archived_at');
    }

    public function usefulLifeMonths(): int
    {
        if ($this->useful_life_years) {
            return max(1, (int) $this->useful_life_years) * 12;
        }

        return max(1, (int) ($this->useful_life_months ?? 60));
    }

    public function isArchived(): bool
    {
        return $this->archived_at !== null;
    }
}
