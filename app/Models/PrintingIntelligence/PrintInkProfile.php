<?php

namespace App\Models\PrintingIntelligence;

use App\Enums\PrintInkType;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Inventory\InventoryItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PrintInkProfile extends Model
{
    use BelongsToTenant;

    protected $table = 'print_ink_profiles';

    protected bool $tenantScopedToBranch = false;

    protected $fillable = [
        'company_id',
        'name',
        'ink_type',
        'inventory_item_id',
        'cartridge_cost',
        'estimated_yield_pages',
        'estimated_yield_sq_m',
        'estimated_ml',
        'cost_per_ml',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'ink_type' => PrintInkType::class,
            'cartridge_cost' => 'decimal:2',
            'estimated_yield_sq_m' => 'decimal:3',
            'estimated_ml' => 'decimal:3',
            'cost_per_ml' => 'decimal:4',
            'active' => 'boolean',
        ];
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function inkEstimates(): HasMany
    {
        return $this->hasMany(PrintArtworkInkEstimate::class, 'ink_profile_id');
    }
}
