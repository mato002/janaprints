<?php

namespace Tests\Feature\Admin;

use App\Enums\ApprovalChainMode;
use App\Enums\ApprovalChainRunStatus;
use App\Enums\ApprovalChainStatus;
use App\Enums\ApprovalChainStepStatus;
use App\Enums\ApprovalRuleType;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Governance\ApprovalChain;
use App\Models\Governance\ApprovalChainStep;
use App\Models\User;
use App\Support\Governance\ApprovalChainsService;
use App\Support\Platform\ApprovalRulesService;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\PlatformConfigurationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ApprovalChainsCenterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        $this->seed(PlatformConfigurationSeeder::class);
    }

    public function test_chains_dashboard_renders_seeded_chains(): void
    {
        $admin = $this->chainsAdmin();
        $company = Company::query()->where('code', 'JANA')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.governance.chains.index', [
                'company_id' => $company->id,
                'branch_id' => '',
            ]))
            ->assertOk()
            ->assertSee(__('Approval Chains'))
            ->assertSee(__('Discount Approval Chain'))
            ->assertSee(__('Approval Monitor'));
    }

    public function test_rules_service_includes_resolved_chain_without_duplicating_thresholds(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $evaluation = app(ApprovalRulesService::class)->evaluate(
            ApprovalRuleType::DiscountApproval,
            null,
            12,
            $company->id,
            null,
        );

        $this->assertTrue($evaluation['requires_approval']);
        $this->assertSame(10.0, (float) $evaluation['tier']['threshold_percent']);
        $this->assertNotNull($evaluation['approval_chain']);
        $this->assertSame('Discount Approval Chain', $evaluation['approval_chain']->name);
        $this->assertCount(3, $evaluation['chain_steps']);
    }

    public function test_single_step_chain_completes_on_approval(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $chain = $this->makeChain($company->id, null, ApprovalRuleType::StockAdjustmentApproval, ApprovalChainMode::Sequential, [
            ['approver_role' => 'Storekeeper', 'is_required' => true],
        ]);
        $customer = $this->sampleSubject($company);
        $actor = $this->chainsAdmin();

        $run = app(ApprovalChainsService::class)->startRun($chain, $customer, ['amount' => 5000], $company->id, null);
        $record = $run->stepRecords->first();

        app(ApprovalChainsService::class)->recordStepAction($record, ApprovalChainStepStatus::Approved, $actor);

        $run->refresh();
        $this->assertSame(ApprovalChainRunStatus::Approved, $run->status);
        $this->assertSame(ApprovalChainStepStatus::Approved, $record->fresh()->status);
    }

    public function test_sequential_chain_requires_step_order(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $chain = $this->makeChain($company->id, null, ApprovalRuleType::DiscountApproval, ApprovalChainMode::Sequential, [
            ['approver_role' => 'Sales', 'is_required' => true],
            ['approver_role' => 'Branch Manager', 'is_required' => true],
        ]);
        $customer = $this->sampleSubject($company);
        $actor = $this->chainsAdmin();

        $run = app(ApprovalChainsService::class)->startRun($chain, $customer, ['percent' => 15], $company->id, null);

        app(ApprovalChainsService::class)->recordStepAction(
            $run->stepRecords->firstWhere('step_number', 1),
            ApprovalChainStepStatus::Approved,
            $actor,
        );

        $run->refresh();
        $this->assertSame(ApprovalChainRunStatus::Pending, $run->status);

        app(ApprovalChainsService::class)->recordStepAction(
            $run->stepRecords->firstWhere('step_number', 2),
            ApprovalChainStepStatus::Approved,
            $actor,
        );

        $this->assertSame(ApprovalChainRunStatus::Approved, $run->fresh()->status);
    }

    public function test_parallel_chain_requires_all_required_steps(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $chain = $this->makeChain($company->id, null, ApprovalRuleType::ProcurementApproval, ApprovalChainMode::Parallel, [
            ['approver_role' => 'Company Admin', 'is_required' => true],
            ['approver_role' => 'Branch Manager', 'is_required' => true],
        ]);
        $customer = $this->sampleSubject($company);
        $actor = $this->chainsAdmin();
        $service = app(ApprovalChainsService::class);

        $run = $service->startRun($chain, $customer, ['amount' => 75000], $company->id, null);

        $service->recordStepAction($run->stepRecords->first(), ApprovalChainStepStatus::Approved, $actor);
        $this->assertSame(ApprovalChainRunStatus::Pending, $run->fresh()->status);

        $service->recordStepAction($run->stepRecords->last(), ApprovalChainStepStatus::Approved, $actor);
        $this->assertSame(ApprovalChainRunStatus::Approved, $run->fresh()->status);
    }

    public function test_conditional_chain_filters_by_context(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();

        ApprovalChain::query()
            ->where('company_id', $company->id)
            ->where('approval_rule_type', ApprovalRuleType::DiscountApproval)
            ->update(['status' => ApprovalChainStatus::Inactive]);

        $lowChain = $this->makeChain($company->id, null, ApprovalRuleType::DiscountApproval, ApprovalChainMode::Conditional, [
            ['approver_role' => 'Sales', 'is_required' => true],
        ], ['min_percent' => 5, 'max_percent' => 9.99]);

        $highChain = $this->makeChain($company->id, null, ApprovalRuleType::DiscountApproval, ApprovalChainMode::Conditional, [
            ['approver_role' => 'Branch Manager', 'is_required' => true],
        ], ['min_percent' => 10]);

        $service = app(ApprovalChainsService::class);

        $this->assertSame('Sales', $service->resolveChain(
            ApprovalRuleType::DiscountApproval,
            $company->id,
            null,
            ['percent' => 7],
        )?->steps->first()?->approver_role);

        $this->assertSame('Branch Manager', $service->resolveChain(
            ApprovalRuleType::DiscountApproval,
            $company->id,
            null,
            ['percent' => 12],
        )?->steps->first()?->approver_role);

        $this->assertNotSame($lowChain->id, $highChain->id);
    }

    public function test_rejection_marks_run_rejected(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $chain = $this->makeChain($company->id, null, ApprovalRuleType::PaymentApproval, ApprovalChainMode::Sequential, [
            ['approver_role' => 'Company Admin', 'is_required' => true],
            ['approver_role' => 'Branch Manager', 'is_required' => true],
        ]);
        $run = app(ApprovalChainsService::class)->startRun(
            $chain,
            $this->sampleSubject($company),
            ['amount' => 200000],
            $company->id,
            null,
        );

        app(ApprovalChainsService::class)->recordStepAction(
            $run->stepRecords->first(),
            ApprovalChainStepStatus::Rejected,
            $this->chainsAdmin(),
        );

        $this->assertSame(ApprovalChainRunStatus::Rejected, $run->fresh()->status);
    }

    public function test_permission_enforcement_blocks_view_without_rights(): void
    {
        $user = User::factory()->create([
            'company_id' => 1,
            'default_branch_id' => 1,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $user->assignRole('Designer');

        $this->actingAs($user)
            ->get(route('admin.governance.chains.index'))
            ->assertForbidden();
    }

    public function test_workflow_governance_section_links_to_approval_chains(): void
    {
        $admin = $this->chainsAdmin();

        $this->actingAs($admin)
            ->get(route('admin.workspaces.administration.section', ['section' => 'workflow-governance']))
            ->assertOk()
            ->assertSee(route('admin.governance.chains.index'), false)
            ->assertSee(__('Approval Chains'));
    }

    public function test_admin_can_create_and_activate_chain(): void
    {
        $admin = $this->chainsAdmin();
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.governance.chains.store'), [
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'name' => 'Custom Payment Chain',
                'module' => 'finance',
                'document_type' => 'payment',
                'approval_rule_type' => ApprovalRuleType::PaymentApproval->value,
                'approval_mode' => ApprovalChainMode::Sequential->value,
                'steps' => [
                    ['approver_role' => 'Company Admin', 'is_required' => 1],
                ],
            ])
            ->assertRedirect(route('admin.governance.chains.index', [
                'company_id' => $company->id,
                'branch_id' => $branch->id,
            ]));

        $chain = ApprovalChain::query()->where('name', 'Custom Payment Chain')->firstOrFail();
        $this->assertSame(ApprovalChainStatus::Draft, $chain->status);

        $this->actingAs($admin)
            ->patch(route('admin.governance.chains.activate', $chain))
            ->assertRedirect();

        $this->assertSame(ApprovalChainStatus::Active, $chain->fresh()->status);
    }

    /**
     * @param  list<array{approver_role?: string, is_required?: bool}>  $steps
     * @param  array<string, float>  $conditions
     */
    protected function makeChain(
        int $companyId,
        ?int $branchId,
        ApprovalRuleType $ruleType,
        ApprovalChainMode $mode,
        array $steps,
        array $conditions = [],
    ): ApprovalChain {
        $chain = ApprovalChain::query()->create([
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'name' => $ruleType->value.' test chain',
            'module' => 'sales',
            'document_type' => null,
            'approval_rule_type' => $ruleType,
            'approval_mode' => $mode,
            'status' => ApprovalChainStatus::Active,
            'condition_json' => $conditions === [] ? null : $conditions,
        ]);

        foreach ($steps as $index => $step) {
            ApprovalChainStep::query()->create([
                'approval_chain_id' => $chain->id,
                'step_number' => $index + 1,
                'approver_role' => $step['approver_role'] ?? 'Company Admin',
                'is_required' => $step['is_required'] ?? true,
            ]);
        }

        return $chain->fresh('steps');
    }

    protected function sampleSubject(Company $company): Customer
    {
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();

        return Customer::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
        ]);
    }

    protected function chainsAdmin(): User
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $role = Role::create(['name' => 'Chains Admin', 'guard_name' => 'web']);
        $role->syncPermissions([
            'governance.chains.view',
            'governance.chains.create',
            'governance.chains.edit',
            'governance.chains.activate',
        ]);
        $user->assignRole($role);

        return $user;
    }
}
