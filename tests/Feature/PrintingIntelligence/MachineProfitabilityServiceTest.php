<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Enums\AssetType;
use App\Enums\FixedAssetStatus;
use App\Enums\ProfitabilitySnapshotType;
use App\Models\Assets\AssetCategory;
use App\Models\Assets\FixedAsset;
use App\Models\Assets\MachineProfile;
use App\Models\PrintingIntelligence\PrintProfitabilitySnapshot;
use App\Services\PrintingIntelligence\MachineProfitabilityService;
use Database\Seeders\OrganizationFoundationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MachineProfitabilityServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_identifies_best_and_worst_machines(): void
    {
        $company = \App\Models\Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = \App\Models\Branch::query()->where('company_id', $company->id)->firstOrFail();
        $machineA = $this->makeMachine($company, $branch, 'Press A');
        $machineB = $this->makeMachine($company, $branch, 'Press B');

        foreach ([[$machineA->id, 5000, 2000], [$machineB->id, 3000, 2800]] as [$machineId, $revenue, $cost]) {
            PrintProfitabilitySnapshot::query()->create([
                'company_id' => $company->id,
                'machine_profile_id' => $machineId,
                'snapshot_type' => ProfitabilitySnapshotType::Job,
                'revenue' => $revenue,
                'total_cost' => $cost,
                'gross_profit' => $revenue - $cost,
                'gross_margin_percent' => (($revenue - $cost) / $revenue) * 100,
                'snapshot_date' => now()->toDateString(),
            ]);
        }

        $result = app(MachineProfitabilityService::class)->analyze(['company_id' => $company->id]);

        $this->assertSame('Press A', $result['best_performing']['machine_name']);
        $this->assertSame('Press B', $result['worst_performing']['machine_name']);
    }

    protected function makeMachine(\App\Models\Company $company, \App\Models\Branch $branch, string $code): MachineProfile
    {
        $asset = FixedAsset::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'asset_category_id' => AssetCategory::query()->firstOrCreate(
                ['company_id' => $company->id, 'code' => 'MCH'],
                ['name' => 'Machines', 'asset_type' => AssetType::Machine->value, 'useful_life_months' => 84, 'is_active' => true],
            )->id,
            'asset_number' => 'MA-'.uniqid(),
            'asset_name' => $code,
            'acquisition_cost' => 100000,
            'acquisition_date' => now()->toDateString(),
            'status' => FixedAssetStatus::Active,
        ]);

        return MachineProfile::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'fixed_asset_id' => $asset->id,
            'machine_code' => $code,
            'machine_type' => 'digital_press',
            'capacity_unit' => 'jobs',
        ]);
    }
}
