<?php

namespace Database\Seeders;

use App\Enums\InventoryVarianceReasonCategory;
use App\Models\Company;
use App\Models\Inventory\InventoryVarianceReasonCode;
use Illuminate\Database\Seeder;

class InventoryVarianceReasonCodeSeeder extends Seeder
{
    /**
     * @var list<array{code: string, name: string, category: InventoryVarianceReasonCategory, requires_comment: bool}>
     */
    private array $defaults = [
        ['code' => 'COUNT-ERR', 'name' => 'Counting error', 'category' => InventoryVarianceReasonCategory::CountingError, 'requires_comment' => true],
        ['code' => 'PAPER-DMG', 'name' => 'Paper damage', 'category' => InventoryVarianceReasonCategory::PaperDamage, 'requires_comment' => true],
        ['code' => 'INK-DRY', 'name' => 'Dried ink', 'category' => InventoryVarianceReasonCategory::DriedInk, 'requires_comment' => true],
        ['code' => 'STORE-DMG', 'name' => 'Storage damage', 'category' => InventoryVarianceReasonCategory::StorageDamage, 'requires_comment' => true],
        ['code' => 'MACH-CAL', 'name' => 'Machine calibration', 'category' => InventoryVarianceReasonCategory::MachineCalibration, 'requires_comment' => false],
        ['code' => 'THEFT', 'name' => 'Theft / loss', 'category' => InventoryVarianceReasonCategory::TheftLoss, 'requires_comment' => true],
        ['code' => 'SUP-SHORT', 'name' => 'Supplier shortage', 'category' => InventoryVarianceReasonCategory::SupplierShortage, 'requires_comment' => true],
        ['code' => 'PROD-SPOIL', 'name' => 'Production spoilage', 'category' => InventoryVarianceReasonCategory::ProductionSpoilage, 'requires_comment' => true],
        ['code' => 'OTHER', 'name' => 'Other', 'category' => InventoryVarianceReasonCategory::Other, 'requires_comment' => true],
    ];

    public function run(): void
    {
        Company::query()->each(function (Company $company) {
            foreach ($this->defaults as $row) {
                InventoryVarianceReasonCode::query()->firstOrCreate(
                    [
                        'company_id' => $company->id,
                        'code' => $row['code'],
                    ],
                    [
                        'name' => $row['name'],
                        'category' => $row['category'],
                        'requires_comment' => $row['requires_comment'],
                        'is_active' => true,
                    ],
                );
            }
        });
    }
}
