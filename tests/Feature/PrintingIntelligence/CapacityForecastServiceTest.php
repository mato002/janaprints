<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Enums\AssetType;
use App\Enums\FixedAssetStatus;
use App\Enums\ProfitabilitySnapshotType;
use App\Models\Assets\AssetCategory;
use App\Models\Assets\FixedAsset;
use App\Models\Assets\MachineProfile;
use App\Models\PrintingIntelligence\PrintProfitabilitySnapshot;
use App\Services\PrintingIntelligence\CapacityForecastService;
use Database\Seeders\OrganizationFoundationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CapacityForecastServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_identifies_bottlenecks(): void
    {
        $company = \App\Models\Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = \App\Models\Branch::query()->where('company_id', $company->id)->firstOrFail();
        $machine = $this->makeMachine($company, $branch);

        for ($i = 0; $i < 8; $i++) {
            PrintProfitabilitySnapshot::query()->create([
                'company_id' => $company->id,
                'machine_profile_id' => $machine->id,
                'snapshot_type' => ProfitabilitySnapshotType::Job,
                'revenue' => 1000,
                'total_cost' => 600,
                'gross_profit' => 400,
                'snapshot_date' => now()->toDateString(),
            ]);
        }

        $result = app(CapacityForecastService::class)->forecast(['company_id' => $company->id]);

        $this->assertNotEmpty($result['machines']);
        $this->assertNotNull($result['overall_utilization_forecast']['forecast_value']);
    }

    protected function makeMachine(\App\Models\Company $company, \App\Models\Branch $branch): MachineProfile
    {
        $asset = FixedAsset::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'asset_category_id' => AssetCategory::query()->firstOrCreate(
                ['company_id' => $company->id, 'code' => 'MCH'],
                ['name' => 'Machines', 'asset_type' => AssetType::Machine->value, 'useful_life_months' => 84, 'is_active' => true],
            )->id,
            'asset_number' => 'MA-'.uniqid(),
            'asset_name' => 'Press',
            'acquisition_cost' => 100000,
            'acquisition_date' => now()->toDateString(),
            'status' => FixedAssetStatus::Active,
        ]);

        return MachineProfile::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'fixed_asset_id' => $asset->id,
            'machine_code' => 'MC-1',
            'machine_type' => 'digital_press',
            'capacity_unit' => 'jobs',
        ]);
    }
}
