<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Enums\AssetType;
use App\Enums\CalibrationRuleStatus;
use App\Enums\CalibrationRuleType;
use App\Enums\FixedAssetStatus;
use App\Models\Assets\AssetCategory;
use App\Models\Assets\FixedAsset;
use App\Models\Assets\MachineProfile;
use App\Models\Branch;
use App\Models\Company;
use App\Models\PrintingIntelligence\PrintCalibrationRule;
use App\Models\User;
use App\Services\PrintingIntelligence\ActiveCostingProfileService;
use App\Services\PrintingIntelligence\CalibrationApprovalService;
use App\Services\PrintingIntelligence\ProductionCostCalculator;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CalibrationPropagationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        config(['printing_intelligence.labour_hourly_rate' => 500]);
    }

    public function test_approved_labour_calibration_propagates_to_production_cost_calculator(): void
    {
        [$rule, $approver, $machine] = $this->labourRuleFixtures();

        app(CalibrationApprovalService::class)->submitForReview($rule, $approver);
        app(CalibrationApprovalService::class)->approve($rule->fresh(), $approver);

        $profile = app(ActiveCostingProfileService::class)->profile($rule->company_id);
        $this->assertEqualsWithDelta(875, (float) $profile['labour_rate_per_hour'], 0.01);

        $result = app(ProductionCostCalculator::class)->calculate($machine, 2.0);

        $this->assertEqualsWithDelta(875, (float) $result['metadata']['labour_hourly_rate'], 0.01);
        $this->assertEqualsWithDelta(1750, (float) $result['estimated_labour_cost'], 0.01);
    }

    /**
     * @return array{0: PrintCalibrationRule, 1: User, 2: MachineProfile}
     */
    protected function labourRuleFixtures(): array
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();

        $approver = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
        ]);
        Role::findByName('Storekeeper', 'web')->syncPermissions([
            'printing.calibration.approve',
            'printing.calibration.manage',
            'printing.calibration.review',
        ]);
        $approver->assignRole('Storekeeper');

        $rule = PrintCalibrationRule::query()->create([
            'company_id' => $company->id,
            'rule_type' => CalibrationRuleType::LabourRate,
            'rule_key' => 'labour_hourly_rate',
            'current_value' => 500,
            'proposed_value' => 875,
            'status' => CalibrationRuleStatus::Draft,
            'reason' => __('Labour rate calibration test'),
            'rule_version' => 'PI4-V2',
        ]);

        $asset = FixedAsset::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'asset_category_id' => AssetCategory::query()->firstOrCreate(
                ['company_id' => $company->id, 'code' => 'MCH'],
                ['name' => 'Machines', 'asset_type' => AssetType::Machine->value, 'useful_life_months' => 84, 'is_active' => true],
            )->id,
            'asset_number' => 'MA-LAB-'.uniqid(),
            'asset_name' => 'Labour Cal Press',
            'acquisition_cost' => 100000,
            'acquisition_date' => now()->toDateString(),
            'status' => FixedAssetStatus::Active,
        ]);

        $machine = MachineProfile::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'fixed_asset_id' => $asset->id,
            'machine_code' => 'MC-LAB-'.uniqid(),
            'machine_type' => 'digital_press',
            'cost_per_hour' => 1000,
            'average_setup_minutes' => 0,
        ]);

        return [$rule, $approver, $machine];
    }
}
