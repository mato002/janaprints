<?php

namespace App\Models\Inventory;

use App\Enums\InventoryVarianceReasonCategory;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryVarianceReasonCode extends Model
{
    use BelongsToTenant;

    protected bool $tenantScopedToBranch = false;

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'category',
        'requires_comment',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'category' => InventoryVarianceReasonCategory::class,
            'requires_comment' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function stockCountItems(): HasMany
    {
        return $this->hasMany(StockCountItem::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
