<?php

namespace App\Services\Inventory;

use App\Enums\VirtualWarehouseRole;
use App\Models\Inventory\Warehouse;
use Database\Seeders\InventoryVirtualWarehouseSeeder;
use Illuminate\Support\Collection;

/**
 * Resolves system-managed virtual warehouses for inventory lifecycle stages.
 *
 * Inventory quantity truth: Raw Materials (physical) → FG → In Transit → Delivered.
 * WIP ({@see VirtualWarehouseRole::Wip}) is accounting-only and reserved for future partial-production use.
 *
 * @see config('inventory_lifecycle')
 */
class VirtualWarehouseResolverService
{
    public function resolveByRole(int $companyId, VirtualWarehouseRole|string $role): ?Warehouse
    {
        $value = $role instanceof VirtualWarehouseRole ? $role->value : $role;

        return Warehouse::query()
            ->where('company_id', $companyId)
            ->virtual()
            ->where('virtual_role', $value)
            ->where('is_active', true)
            ->first();
    }

    public function rawMaterials(int $companyId): ?Warehouse
    {
        return $this->resolveByRole($companyId, VirtualWarehouseRole::RawMaterial);
    }

    public function workInProgress(int $companyId): ?Warehouse
    {
        return $this->resolveByRole($companyId, VirtualWarehouseRole::Wip);
    }

    public function finishedGoods(int $companyId): ?Warehouse
    {
        return $this->resolveByRole($companyId, VirtualWarehouseRole::FinishedGoods);
    }

    public function inTransit(int $companyId): ?Warehouse
    {
        return $this->resolveByRole($companyId, VirtualWarehouseRole::InTransit);
    }

    public function quarantine(int $companyId): ?Warehouse
    {
        return $this->resolveByRole($companyId, VirtualWarehouseRole::Quarantine);
    }

    /**
     * @return Collection<int, Warehouse>
     */
    public function ensureDefaults(int $companyId): Collection
    {
        app(InventoryVirtualWarehouseSeeder::class)->seedForCompany($companyId);

        return Warehouse::query()
            ->where('company_id', $companyId)
            ->virtual()
            ->whereIn('virtual_role', collect(VirtualWarehouseRole::seededRoles())->map->value)
            ->orderBy('virtual_role')
            ->get();
    }
}
