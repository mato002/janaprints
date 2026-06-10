<?php

namespace App\Models\Inventory;

use App\Enums\VirtualWarehouseRole;
use App\Models\Concerns\BelongsToTenant;
use App\Models\User;
use Database\Factories\Inventory\WarehouseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Warehouse extends Model
{
    /** @use HasFactory<WarehouseFactory> */
    use BelongsToTenant, HasFactory;

    protected bool $tenantScopedToBranch = true;

    protected $fillable = [
        'company_id',
        'branch_id',
        'code',
        'name',
        'description',
        'is_active',
        'is_virtual',
        'virtual_role',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_virtual' => 'boolean',
            'virtual_role' => VirtualWarehouseRole::class,
        ];
    }

    public function managers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_warehouse')
            ->withPivot('is_manager')
            ->withTimestamps()
            ->wherePivot('is_manager', true);
    }

    public function scopePhysical(Builder $query): Builder
    {
        return $query->where('is_virtual', false);
    }

    public function scopeVirtual(Builder $query): Builder
    {
        return $query->where('is_virtual', true);
    }

    public function scopeForVirtualRole(Builder $query, VirtualWarehouseRole|string $role): Builder
    {
        $value = $role instanceof VirtualWarehouseRole ? $role->value : $role;

        return $query->where('is_virtual', true)->where('virtual_role', $value);
    }

    public function isProtectedVirtual(): bool
    {
        if (! $this->is_virtual || $this->virtual_role === null) {
            return false;
        }

        return $this->virtual_role->blocksDirectReceipt();
    }
}
