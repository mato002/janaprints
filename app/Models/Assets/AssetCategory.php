<?php

namespace App\Models\Assets;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssetCategory extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'company_id',
        'name',
        'code',
        'useful_life_months',
        'depreciation_rate_percent',
        'depreciation_method',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'depreciation_rate_percent' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function assets(): HasMany
    {
        return $this->hasMany(FixedAsset::class);
    }
}
