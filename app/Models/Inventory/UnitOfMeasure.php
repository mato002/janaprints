<?php

namespace App\Models\Inventory;

use App\Models\Concerns\BelongsToTenant;
use Database\Factories\Inventory\UnitOfMeasureFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnitOfMeasure extends Model
{
    /** @use HasFactory<UnitOfMeasureFactory> */
    use BelongsToTenant, HasFactory;

    protected bool $tenantScopedToBranch = true;

    protected $table = 'units_of_measure';

    protected $fillable = ['company_id', 'branch_id', 'code', 'name', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
