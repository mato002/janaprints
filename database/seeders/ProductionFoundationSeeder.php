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
     * @var list<array{code: string, name: string}>
     */
    private array $workCenters = [
        ['code' => 'DESIGN', 'name' => 'Design'],
        ['code' => 'PREPRESS', 'name' => 'Prepress'],
        ['code' => 'DIGITAL', 'name' => 'Digital Printing'],
        ['code' => 'OFFSET', 'name' => 'Offset Printing'],
        ['code' => 'LARGE_FORMAT', 'name' => 'Large Format'],
        ['code' => 'FINISHING', 'name' => 'Finishing'],
        ['code' => 'PACKAGING', 'name' => 'Packaging'],
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
            WorkCenter::query()->firstOrCreate(
                ['company_id' => $companyId, 'branch_id' => $branchId, 'code' => $center['code']],
                ['name' => $center['name'], 'is_active' => true],
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
