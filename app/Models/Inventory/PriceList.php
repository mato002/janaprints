<?php

namespace App\Models\Inventory;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PriceList extends Model
{
    use BelongsToTenant;

    protected bool $tenantScopedToBranch = true;

    protected $fillable = [
        'company_id', 'branch_id', 'name', 'currency', 'effective_date', 'status',
    ];

    protected function casts(): array
    {
        return ['effective_date' => 'date'];
    }

    public function items(): HasMany
    {
        return $this->hasMany(PriceListItem::class);
    }
}
