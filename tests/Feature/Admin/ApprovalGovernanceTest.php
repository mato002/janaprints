<?php

namespace Tests\Feature\Admin;

use App\Enums\ApprovalRuleType;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Platform\ApprovalRule;
use App\Models\User;
use App\Support\Platform\ApprovalRulesService;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\PlatformConfigurationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ApprovalGovernanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        $this->seed(PlatformConfigurationSeeder::class);
    }

    public function test_approvals_admin_page_is_accessible(): void
    {
        $user = $this->userWithPermissions(['settings.view']);

        $this->actingAs($user)
            ->get(route('admin.settings.approvals.index'))
            ->assertOk()
            ->assertSee(__('Approval Rules'))
            ->assertSee(__('Quotation Approval'))
            ->assertSee(__('Discount Approval'))
            ->assertDontSee(__('Minimum approvers'));
    }

    public function test_approval_rule_workspace_shows_editor(): void
    {
        $user = $this->userWithPermissions(['settings.view', 'settings.manage']);
        $company = Company::query()->where('code', 'JANA')->firstOrFail();

        $this->actingAs($user)
            ->get(route('admin.settings.approvals.index', [
                'company_id' => $company->id,
                'rule' => 'quotation_approval',
            ]))
            ->assertOk()
            ->assertSee(__('Quotation Approval'))
            ->assertSee(__('Minimum approvers'))
            ->assertSee(__('Amount ≥'));
    }

    public function test_company_admin_can_save_approval_tiers(): void
    {
        $user = $this->userWithPermissions(['settings.view', 'settings.manage']);
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();

        $this->actingAs($user)
            ->put(route('admin.settings.approvals.update'), [
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'rules' => [
                    'quotation_approval' => [
                        'is_enabled' => '1',
                        'min_approvers' => '2',
                        'tiers' => [
                            ['threshold_amount' => '75000', 'approver_role' => 'Branch Manager', 'approver_permission' => 'quotations.approve'],
                        ],
                    ],
                ],
            ])
            ->assertRedirect(route('admin.settings.approvals.index', [
                'company_id' => $company->id,
                'branch_id' => $branch->id,
            ]));

        $rule = ApprovalRule::query()
            ->where('company_id', $company->id)
            ->where('branch_id', $branch->id)
            ->where('rule_type', ApprovalRuleType::QuotationApproval->value)
            ->first();

        $this->assertTrue($rule->is_enabled);
        $this->assertSame(2, $rule->min_approvers);
        $this->assertSame(75000.0, (float) $rule->settings_json['tiers'][0]['threshold_amount']);
    }

    public function test_quotation_threshold_matching_uses_highest_tier(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $service = app(ApprovalRulesService::class);

        $result = $service->evaluate(
            ApprovalRuleType::QuotationApproval,
            150000,
            null,
            $company->id,
            null,
        );

        $this->assertTrue($result['requires_approval']);
        $this->assertSame(100000.0, (float) $result['tier']['threshold_amount']);
        $this->assertSame('Company Admin', $result['approver_role']);
        $this->assertSame('quotations.approve', $result['approver_permission']);
    }

    public function test_discount_percent_threshold_evaluation(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $service = app(ApprovalRulesService::class);

        $this->assertFalse($service->requiresApproval(
            ApprovalRuleType::DiscountApproval,
            null,
            4,
            $company->id,
            null,
        ));

        $this->assertTrue($service->requiresApproval(
            ApprovalRuleType::DiscountApproval,
            null,
            12,
            $company->id,
            null,
        ));

        $evaluation = $service->evaluate(
            ApprovalRuleType::DiscountApproval,
            null,
            12,
            $company->id,
            null,
        );

        $this->assertSame(10.0, (float) $evaluation['tier']['threshold_percent']);
        $this->assertSame('Branch Manager', $evaluation['approver_role']);
    }

    public function test_branch_override_takes_precedence_over_company_default(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();
        $service = app(ApprovalRulesService::class);

        ApprovalRule::query()->updateOrCreate(
            [
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'rule_type' => ApprovalRuleType::QuotationApproval->value,
            ],
            [
                'is_enabled' => true,
                'min_approvers' => 1,
                'settings_json' => [
                    'tiers' => [
                        ['threshold_amount' => 25000, 'approver_role' => 'Sales', 'approver_permission' => 'quotations.approve'],
                    ],
                ],
            ],
        );

        $result = $service->evaluate(
            ApprovalRuleType::QuotationApproval,
            30000,
            null,
            $company->id,
            $branch->id,
        );

        $this->assertTrue($result['requires_approval']);
        $this->assertSame('branch', $result['scope']);
        $this->assertSame('Sales', $result['approver_role']);
        $this->assertSame(25000.0, (float) $result['tier']['threshold_amount']);
    }

    public function test_company_default_fallback_when_branch_has_no_tiers(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branchB = Branch::factory()->create([
            'company_id' => $company->id,
            'code' => 'BR2',
            'name' => 'Branch Two',
        ]);
        $service = app(ApprovalRulesService::class);

        ApprovalRule::query()->updateOrCreate(
            [
                'company_id' => $company->id,
                'branch_id' => $branchB->id,
                'rule_type' => ApprovalRuleType::QuotationApproval->value,
            ],
            [
                'is_enabled' => true,
                'min_approvers' => 1,
                'settings_json' => ['tiers' => []],
            ],
        );

        $result = $service->evaluate(
            ApprovalRuleType::QuotationApproval,
            60000,
            null,
            $company->id,
            $branchB->id,
        );

        $this->assertTrue($result['requires_approval']);
        $this->assertSame('company', $result['scope']);
        $this->assertSame(50000.0, (float) $result['tier']['threshold_amount']);
    }

    /**
     * @param  list<string>  $permissions
     */
    protected function userWithPermissions(array $permissions): User
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();

        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $role = Role::create(['name' => 'Approval Tester', 'guard_name' => 'web']);
        $role->syncPermissions($permissions);
        $user->assignRole($role);

        return $user;
    }
}
