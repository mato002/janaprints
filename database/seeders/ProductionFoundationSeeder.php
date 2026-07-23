<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Company;
use Illuminate\Support\Facades\Schema;
use App\Models\Production\ProductionStage;
use App\Models\Production\WorkCenter;
use Illuminate\Database\Seeder;

class ProductionFoundationSeeder extends Seeder
{
    /**
     * @var list<array{code: string, name: string, requires_machine: bool}>
     */
    private array $workCenters = [
        ['code' => 'DESIGN', 'name' => 'Design', 'requires_machine' => false],
        ['code' => 'PREPRESS', 'name' => 'Prepress', 'requires_machine' => false],
        ['code' => 'DIGITAL', 'name' => 'Digital Printing', 'requires_machine' => true],
        ['code' => 'OFFSET', 'name' => 'Offset Printing', 'requires_machine' => true],
        ['code' => 'LARGE_FORMAT', 'name' => 'Large Format', 'requires_machine' => true],
        ['code' => 'FINISHING', 'name' => 'Finishing', 'requires_machine' => false],
        ['code' => 'PACKAGING', 'name' => 'Packaging', 'requires_machine' => false],
    ];

    /**
     * @var list<array{code: string, name: string, sort: int}>
     */
    private array $stages = [
        ['code' => 'PENDING', 'name' => 'Pending', 'sort' => 1],
        ['code' => 'PREPRESS', 'name' => 'Prepress', 'sort' => 2],
        ['code' => 'PRINTING', 'name' => 'Printing', 'sort' => 3],
        ['code' => 'FINISHING', 'name' => 'Finishing', 'sort' => 4],
        ['code' => 'QC', 'name' => 'QC', 'sort' => 5],
        ['code' => 'DISPATCH', 'name' => 'Ready For Dispatch', 'sort' => 6],
    ];

    public function run(): void
    {
        if (! Schema::hasTable('work_centers')) {
            return;
        }

        $companies = Company::query()->get();

        foreach ($companies as $company) {
            foreach (Branch::query()->where('company_id', $company->id)->get() as $branch) {
                $this->seedForBranch($company->id, $branch->id);
            }
        }
    }

    protected function seedForBranch(int $companyId, int $branchId): void
    {
        foreach ($this->workCenters as $center) {
            WorkCenter::query()->updateOrCreate(
                ['company_id' => $companyId, 'branch_id' => $branchId, 'code' => $center['code']],
                [
                    'name' => $center['name'],
                    'is_active' => true,
                    'requires_machine' => $center['requires_machine'],
                ],
            );
        }

        foreach ($this->stages as $stage) {
            ProductionStage::query()->firstOrCreate(
                ['company_id' => $companyId, 'branch_id' => $branchId, 'code' => $stage['code']],
                ['name' => $stage['name'], 'sort_order' => $stage['sort'], 'is_active' => true],
            );
        }
    }
}
