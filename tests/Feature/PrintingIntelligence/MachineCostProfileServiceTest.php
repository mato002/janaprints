<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Models\Assets\AssetCategory;
use App\Models\Assets\FixedAsset;
use App\Models\Assets\MachineProfile;
use App\Models\Branch;
use App\Models\Company;
use App\Enums\AssetType;
use App\Enums\FixedAssetStatus;
use App\Services\PrintingIntelligence\MachineCostProfileService;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MachineCostProfileServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_cost_per_hour_and_estimated_machine_cost(): void
    {
        $machine = $this->makeMachine([
            'cost_per_hour' => 1200,
            'power_rating_kw' => 2.5,
            'average_setup_minutes' => 30,
            'maintenance_cost_factor' => 1.1,
        ]);

        $service = app(MachineCostProfileService::class);

        $this->assertEqualsWithDelta(1200, $service->costPerHour($machine), 0.01);
        $this->assertGreaterThan(0, $service->estimatedSetupCost($machine));
        $this->assertGreaterThan(0, $service->estimatedMachineCost($machine, 2));
    }

    public function test_estimated_electricity_cost_uses_config_rate(): void
    {
        config(['printing_intelligence.electricity_rate_per_kwh' => 20]);
        $machine = $this->makeMachine(['power_rating_kw' => 3]);

        $cost = app(MachineCostProfileService::class)->estimatedElectricityCost($machine, 2);

        $this->assertEqualsWithDelta(120, $cost, 0.01);
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
            'asset_name' => 'Test Press',
            'acquisition_cost' => 100000,
            'acquisition_date' => now()->toDateString(),
            'status' => FixedAssetStatus::Active,
        ]);

        return MachineProfile::query()->create(array_merge([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'fixed_asset_id' => $asset->id,
            'machine_code' => 'MC-'.uniqid(),
            'machine_type' => 'digital_press',
            'capacity_unit' => 'jobs',
        ], $overrides));
    }
}
