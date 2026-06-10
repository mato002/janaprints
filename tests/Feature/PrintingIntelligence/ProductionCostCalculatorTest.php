<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Models\Assets\AssetCategory;
use App\Models\Assets\FixedAsset;
use App\Models\Assets\MachineProfile;
use App\Models\Branch;
use App\Models\Company;
use App\Enums\AssetType;
use App\Enums\FixedAssetStatus;
use App\Services\PrintingIntelligence\ProductionCostCalculator;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionCostCalculatorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        config([
            'printing_intelligence.labour_hourly_rate' => 500,
            'printing_intelligence.default_overhead_percent' => 10,
        ]);
    }

    public function test_calculates_machine_labour_overhead_and_total(): void
    {
        $machine = $this->makeMachine([
            'cost_per_hour' => 1000,
            'power_rating_kw' => 2,
            'average_setup_minutes' => 30,
        ]);

        $result = app(ProductionCostCalculator::class)->calculate($machine, 2, 50, 0);

        $this->assertGreaterThan(0, $result['estimated_machine_cost']);
        $this->assertGreaterThan(0, $result['estimated_labour_cost']);
        $this->assertGreaterThan(0, $result['estimated_overhead_cost']);
        $this->assertEqualsWithDelta(
            $result['estimated_machine_cost'] + $result['estimated_electricity_cost'] + $result['estimated_labour_cost'] + $result['estimated_overhead_cost'] + 50,
            $result['estimated_total_production_cost'],
            0.05,
        );
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    protected function makeMachine(array $overrides = []): MachineProfile
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();

        $asset = FixedAsset::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'asset_category_id' => AssetCategory::query()->firstOrCreate(
                ['company_id' => $company->id, 'code' => 'MCH'],
                ['name' => 'Machines', 'asset_type' => AssetType::Machine->value, 'useful_life_months' => 84, 'is_active' => true],
            )->id,
            'asset_number' => 'MA-'.uniqid(),
            'asset_name' => 'Cost Press',
            'acquisition_cost' => 100000,
            'acquisition_date' => now()->toDateString(),
            'status' => FixedAssetStatus::Active,
        ]);

        return MachineProfile::query()->create(array_merge([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'fixed_asset_id' => $asset->id,
            'machine_code' => 'MC-COST-'.uniqid(),
            'machine_type' => 'digital_press',
        ], $overrides));
    }
}
