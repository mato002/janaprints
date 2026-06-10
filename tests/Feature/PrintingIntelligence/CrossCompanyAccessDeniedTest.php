<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Enums\CalibrationRuleStatus;
use App\Enums\CalibrationRuleType;
use App\Models\Branch;
use App\Models\Company;
use App\Models\PrintingIntelligence\PrintCalibrationRule;
use App\Models\User;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CrossCompanyAccessDeniedTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_submit_calibration_rule_from_other_company_returns_forbidden(): void
    {
        $jana = Company::query()->where('code', 'JANA')->firstOrFail();
        $janaBranch = Branch::query()->where('company_id', $jana->id)->firstOrFail();

        $otherCompany = Company::factory()->create(['code' => 'FOREIGN']);
        $otherBranch = Branch::factory()->create(['company_id' => $otherCompany->id]);

        $foreignRule = PrintCalibrationRule::query()->create([
            'company_id' => $otherCompany->id,
            'rule_type' => CalibrationRuleType::InkYield,
            'rule_key' => 'default_cmyk_coverage_factor',
            'current_value' => 1.0,
            'proposed_value' => 1.15,
            'status' => CalibrationRuleStatus::Draft,
            'reason' => __('Foreign company rule'),
        ]);

        $user = User::factory()->create([
            'company_id' => $jana->id,
            'default_branch_id' => $janaBranch->id,
            'email_verified_at' => now(),
        ]);
        Role::findByName('Storekeeper', 'web')->syncPermissions([
            'printing.calibration.manage',
            'printing.calibration.view',
        ]);
        $user->assignRole('Storekeeper');

        $this->actingAs($user)
            ->withSession(['active_company_id' => $jana->id, 'active_branch_id' => $janaBranch->id])
            ->post(route('admin.printing-intelligence.calibration.submit', $foreignRule))
            ->assertForbidden();
    }
}
