<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Enums\AssetType;
use App\Enums\FixedAssetStatus;
use App\Models\Assets\AssetCategory;
use App\Models\Assets\FixedAsset;
use App\Models\Assets\MachineProfile;
use App\Models\Branch;
use App\Models\Company;
use App\Services\PrintingIntelligence\MachineCostProfileService;
use App\Services\PrintingIntelligence\ProductionCostCalculator;
use App\Services\PrintingIntelligence\QuotationPricingService;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ElectricityCostIntegrityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        config(['printing_intelligence.electricity_rate_per_kwh' => 25]);
    }

    public function test_machine_cost_excludes_electricity_and_pricing_counts_it_once(): void
    {
        $machine = $this->makeMachine([
            'cost_per_hour' => 1000,
            'power_rating_kw' => 2,
            'average_setup_minutes' => 0,
        ]);

        $machineService = app(MachineCostProfileService::class);
        $runHours = 2.0;

        $machineCost = $machineService->estimatedMachineCost($machine, $runHours);
        $electricityCost = $machineService->estimatedElectricityCost($machine, $runHours);

        $this->assertGreaterThan(0, $machineCost);
        $this->assertEqualsWithDelta(100, $electricityCost, 0.01);

        $rollup = app(ProductionCostCalculator::class)->calculate($machine, $runHours);
        $this->assertEqualsWithDelta($machineCost, $rollup['estimated_machine_cost'], 0.01);
        $this->assertEqualsWithDelta($electricityCost, $rollup['estimated_electricity_cost'], 0.01);
        $this->assertLessThan(
            $machineCost + $electricityCost,
            $rollup['estimated_machine_cost'],
            'Machine cost line must not embed electricity.',
        );

        $components = [
            'material_cost' => 100,
            'ink_cost' => 50,
            'machine_cost' => $machineCost,
            'labour_cost' => 150,
            'electricity_cost' => $electricityCost,
            'overhead_cost' => 45,
            'wastage_cost' => 10,
        ];

        $priced = app(QuotationPricingService::class)->price($components, 20, 35);

        $this->assertEqualsWithDelta(
            array_sum($components),
            $priced['estimated_total_cost'],
            0.01,
            'PI5 total must include machine and electricity exactly once.',
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
            'asset_number' => 'MA-ELEC-'.uniqid(),
            'asset_name' => 'Electricity Test Press',
            'acquisition_cost' => 100000,
            'acquisition_date' => now()->toDateString(),
            'status' => FixedAssetStatus::Active,
        ]);

        return MachineProfile::query()->create(array_merge([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'fixed_asset_id' => $asset->id,
            'machine_code' => 'MC-ELEC-'.uniqid(),
            'machine_type' => 'digital_press',
        ], $overrides));
    }
}
