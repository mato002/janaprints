<?php

namespace Tests\Feature\Admin;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Models\UserBranch;
use App\Support\Security\ConsolidatedViewGovernance;
use App\Support\Security\UserBranchAccessService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ConsolidatedViewGovernanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_permissions_seeded_for_company_admin(): void
    {
        $this->assertTrue(Permission::query()->where('name', 'reports.consolidated.view')->exists());

        $role = Role::query()->where('name', 'Company Admin')->firstOrFail();

        $this->assertTrue($role->hasPermissionTo('reports.consolidated.view'));
        $this->assertFalse(Role::query()->where('name', 'Branch Manager')->firstOrFail()->hasPermissionTo('reports.consolidated.view'));
    }

    public function test_branch_user_cannot_see_all_branches_in_report_filter(): void
    {
        [$company, $branchA, $branchB] = $this->branches();
        $user = $this->branchUser($company, $branchA, ['commercial.reports.sales.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branchA->id]);

        $this->actingAs($user)
            ->get(route('admin.commercial.reports.sales.index'))
            ->assertOk()
            ->assertDontSee(__('All branches'), false);
    }

    public function test_branch_user_report_scope_locked_to_branch(): void
    {
        [$company, $branchA, $branchB] = $this->branches();
        $user = $this->branchUser($company, $branchA, ['commercial.reports.sales.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branchA->id]);

        $request = request()->merge(['branch_id' => '']);
        $request->setUserResolver(fn () => $user);

        $branchId = app(ConsolidatedViewGovernance::class)->resolveReportBranchId($user, $request);

        $this->assertSame($branchA->id, $branchId);
    }

    public function test_hq_user_can_see_all_branches_in_report_filter(): void
    {
        [$company, $branchA] = $this->branches();
        $user = $this->hqUser($company, $branchA, ['commercial.reports.sales.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branchA->id]);

        $this->actingAs($user)
            ->get(route('admin.commercial.reports.sales.index'))
            ->assertOk()
            ->assertSee(__('All branches'), false);
    }

    public function test_hq_user_can_use_consolidated_report_scope(): void
    {
        [$company, $branchA] = $this->branches();
        $user = $this->hqUser($company, $branchA, ['commercial.reports.sales.view']);

        $request = request()->merge(['branch_id' => '']);
        $request->setUserResolver(fn () => $user);

        $branchId = app(ConsolidatedViewGovernance::class)->resolveReportBranchId($user, $request);

        $this->assertNull($branchId);
    }

    public function test_manager_without_consolidated_permission_is_branch_locked(): void
    {
        [$company, $branchA, $branchB] = $this->branches();
        $user = $this->branchUser($company, $branchA, ['reports.view', 'intelligence.branch.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branchA->id]);

        $this->actingAs($user)
            ->get(route('admin.reports.branch360'))
            ->assertOk()
            ->assertDontSee(__('Branch Comparison'), false);
    }

    public function test_executive_with_consolidated_sees_branch_comparison(): void
    {
        [$company, $branchA, $branchB] = $this->branches();
        $user = $this->hqUser($company, $branchA, ['reports.view', 'intelligence.branch.view']);

        Branch::factory()->create(['company_id' => $company->id, 'code' => 'BR3', 'name' => 'Branch Three']);

        session(['active_company_id' => $company->id, 'active_branch_id' => null]);

        $this->actingAs($user)
            ->get(route('admin.reports.branch360'))
            ->assertOk()
            ->assertSee(__('Branch Comparison'), false);
    }

    public function test_executive_dashboard_branch_performance_scoped_for_branch_user(): void
    {
        [$company, $branchA, $branchB] = $this->branches();
        $user = $this->branchUser($company, $branchA, ['reports.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branchA->id]);

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee($branchA->name, false)
            ->assertDontSee($branchB->name, false);
    }

    /**
     * @return array{0: Company, 1: Branch, 2: Branch}
     */
    protected function branches(): array
    {
        $company = Company::factory()->create();
        $branchA = Branch::factory()->create(['company_id' => $company->id, 'code' => 'HQ', 'name' => 'Head Office']);
        $branchB = Branch::factory()->create(['company_id' => $company->id, 'code' => 'BR2', 'name' => 'Branch Two']);

        return [$company, $branchA, $branchB];
    }

    protected function branchUser(Company $company, Branch $branch, array $permissions): User
    {
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
        ]);

        app(UserBranchAccessService::class)->syncAssignments($user, [$branch->id], $branch->id);

        $role = Role::create(['name' => 'test-branch-'.uniqid(), 'guard_name' => 'web']);
        $role->givePermissionTo($permissions);
        $user->assignRole($role);

        return $user;
    }

    protected function hqUser(Company $company, Branch $branch, array $permissions): User
    {
        $user = $this->branchUser($company, $branch, array_merge($permissions, ['reports.consolidated.view']));

        return $user;
    }
}
