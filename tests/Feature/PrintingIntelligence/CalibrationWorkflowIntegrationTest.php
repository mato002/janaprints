<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Enums\CalibrationRuleStatus;
use App\Enums\CalibrationRuleType;
use App\Models\Branch;
use App\Models\Company;
use App\Models\PrintingIntelligence\PrintCalibrationRule;
use App\Models\PrintingIntelligence\PrintCalibrationRuleHistory;
use App\Models\User;
use App\Services\PrintingIntelligence\ActiveCostingProfileService;
use App\Services\PrintingIntelligence\CalibrationApprovalService;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CalibrationWorkflowIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_submit_review_approve_writes_history_without_auto_applying_to_profiles(): void
    {
        [$rule, $submitter, $approver] = $this->draftRule();

        app(CalibrationApprovalService::class)->submitForReview($rule, $submitter);
        $rule->refresh();
        $this->assertSame(CalibrationRuleStatus::PendingReview, $rule->status);

        app(CalibrationApprovalService::class)->approve($rule, $approver);
        $rule->refresh();

        $this->assertSame(CalibrationRuleStatus::Approved, $rule->status);
        $this->assertDatabaseHas('print_calibration_rule_history', [
            'print_calibration_rule_id' => $rule->id,
            'after_value' => 1.25,
        ]);

        $profile = app(ActiveCostingProfileService::class)->profile($rule->company_id);
        $this->assertEqualsWithDelta(1.25, (float) $profile['ink_yield_factor'], 0.0001);
    }

    /**
     * @return array{0: PrintCalibrationRule, 1: User, 2: User}
     */
    protected function draftRule(): array
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();

        $submitter = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
        ]);
        Role::findByName('Storekeeper', 'web')->syncPermissions(['printing.calibration.manage']);
        $submitter->assignRole('Storekeeper');

        $approver = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
        ]);
        Role::findByName('Storekeeper', 'web')->syncPermissions(['printing.calibration.approve', 'printing.calibration.review']);
        $approver->assignRole('Storekeeper');

        $rule = PrintCalibrationRule::query()->create([
            'company_id' => $company->id,
            'rule_type' => CalibrationRuleType::InkYield,
            'rule_key' => 'default_cmyk_coverage_factor',
            'current_value' => 1.0,
            'proposed_value' => 1.25,
            'status' => CalibrationRuleStatus::Draft,
            'reason' => __('Test calibration'),
            'rule_version' => 'PI3-V2',
        ]);

        return [$rule, $submitter, $approver];
    }
}
