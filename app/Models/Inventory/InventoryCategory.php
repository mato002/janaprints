<?php

namespace App\Models\Inventory;

use App\Models\Concerns\BelongsToTenant;
use Database\Factories\Inventory\InventoryCategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryCategory extends Model
{
    /** @use HasFactory<InventoryCategoryFactory> */
    use BelongsToTenant, HasFactory;

    protected bool $tenantScopedToBranch = true;

    protected $fillable = ['company_id', 'branch_id', 'code', 'name', 'description', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function items(): HasMany
    {
        return $this->hasMany(InventoryItem::class);
    }
}
