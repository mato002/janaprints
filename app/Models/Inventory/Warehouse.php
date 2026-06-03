<?php

namespace App\Models\Inventory;

use App\Models\Concerns\BelongsToTenant;
use Database\Factories\Inventory\WarehouseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    /** @use HasFactory<WarehouseFactory> */
    use BelongsToTenant, HasFactory;

    protected bool $tenantScopedToBranch = true;

    protected $fillable = ['company_id', 'branch_id', 'code', 'name', 'description', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
