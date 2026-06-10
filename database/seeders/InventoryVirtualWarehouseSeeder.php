<?php

namespace Database\Seeders;

use App\Enums\VirtualWarehouseRole;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Inventory\Warehouse;
use Illuminate\Database\Seeder;

class InventoryVirtualWarehouseSeeder extends Seeder
{
    public function run(): void
    {
        Company::query()->each(function (Company $company) {
            $this->seedForCompany($company->id);
        });
    }

    public function seedForCompany(int $companyId): void
    {
        $branchId = Branch::query()
            ->where('company_id', $companyId)
            ->orderByDesc('is_head_office')
            ->orderBy('id')
            ->value('id');

        if ($branchId === null) {
            return;
        }

        foreach (VirtualWarehouseRole::seededRoles() as $role) {
            $description = $role->isAccountingOnlyLayer()
                ? __('Reserved virtual location. WIP is accounting-only in Jana Prints—not used for inventory quantity tracking.')
                : __('System-managed virtual location for :role inventory.', ['role' => $role->label()]);

            Warehouse::query()->updateOrCreate(
                [
                    'company_id' => $companyId,
                    'virtual_role' => $role,
                ],
                [
                    'branch_id' => $branchId,
                    'code' => $role->defaultCode(),
                    'name' => $role->defaultName(),
                    'description' => $description,
                    'is_active' => true,
                    'is_virtual' => true,
                ],
            );
        }
    }
}
