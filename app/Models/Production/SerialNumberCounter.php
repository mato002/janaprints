<?php

namespace App\Models\Production;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Inventory\InventoryItem;
use Illuminate\Database\Eloquent\Model;

class SerialNumberCounter extends Model
{
    use BelongsToTenant;

    protected bool $tenantScopedToBranch = true;

    protected $fillable = [
        'company_id', 'branch_id', 'inventory_item_id', 'customer_id',
        'last_serial_number',
    ];

    protected function casts(): array
    {
        return [
            'last_serial_number' => 'integer',
        ];
    }
}
