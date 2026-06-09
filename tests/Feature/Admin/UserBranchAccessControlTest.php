<?php

namespace Tests\Feature\Admin;

use App\Enums\LeadStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Lead;
use App\Models\User;
use App\Support\Security\UserBranchAccessService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserBranchAccessControlTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_assigned_branch_access(): void
    {
        [$company, $branchA] = $this->branches();
        $user = $this->userWithBranches($company, [$branchA->id], $branchA->id, ['crm.leads.view']);
        $lead = $this->leadForBranch($company, $branchA);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branchA->id]);

        $this->actingAs($user)
            ->get(route('admin.crm.leads.show', $lead))
            ->assertOk();
    }

    public function test_multiple_branch_assignment(): void
    {
        [$company, $branchA, $branchB] = $this->branches();
        $user = $this->userWithBranches($company, [$branchA->id, $branchB->id], $branchA->id, ['crm.leads.view']);
        $leadB = $this->leadForBranch($company, $branchB);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branchA->id]);

        $this->actingAs($user)
            ->post(route('admin.context.update'), [
                'company_id' => $company->id,
                'branch_id' => $branchB->id,
            ])
            ->assertRedirect();

        $this->assertSame($branchB->id, session('active_branch_id'));

        $this->actingAs($user)
            ->get(route('admin.crm.leads.show', $leadB))
            ->assertOk();
    }

    public function test_unauthorized_switch(): void
    {
        [$company, $branchA, $branchB, $branchC] = $this->branches(3);
        $user = $this->userWithBranches($company, [$branchA->id, $branchB->id], $branchA->id, ['crm.leads.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branchA->id]);

        $this->actingAs($user)
            ->post(route('admin.context.update'), [
                'company_id' => $company->id,
                'branch_id' => $branchC->id,
            ])
            ->assertForbidden();

        $this->assertSame($branchA->id, session('active_branch_id'));
    }

    public function test_unauthorized_access(): void
    {
        [$company, $branchA, $branchB] = $this->branches();
        $user = $this->userWithBranches($company, [$branchA->id], $branchA->id, ['crm.leads.view']);
        $foreignLead = $this->leadForBranch($company, $branchB);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branchA->id]);

        $this->actingAs($user)
            ->get(route('admin.crm.leads.show', $foreignLead))
            ->assertForbidden();
    }

    public function test_hq_access(): void
    {
        [$company, $branchA, $branchB, $branchC] = $this->branches(3);
        $user = $this->userWithBranches($company, [$branchA->id], $branchA->id, ['crm.leads.view', 'reports.consolidated.view']);
        $leadC = $this->leadForBranch($company, $branchC);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branchA->id]);

        $this->actingAs($user)
            ->post(route('admin.context.update'), [
                'company_id' => $company->id,
                'branch_id' => '',
            ])
            ->assertRedirect();

        $this->assertNull(session('active_branch_id'));

        $this->actingAs($user)
            ->post(route('admin.context.update'), [
                'company_id' => $company->id,
                'branch_id' => $branchC->id,
            ])
            ->assertRedirect();

        $this->assertSame($branchC->id, session('active_branch_id'));

        session(['active_company_id' => $company->id, 'active_branch_id' => null]);

        $this->actingAs($user)
            ->get(route('admin.crm.leads.show', $leadC))
            ->assertOk();
    }

    public function test_middleware_resets_unassigned_session_branch(): void
    {
        [$company, $branchA, $branchB] = $this->branches();
        $user = $this->userWithBranches($company, [$branchA->id], $branchA->id, ['crm.leads.view']);

        session([
            'active_company_id' => $company->id,
            'active_branch_id' => $branchB->id,
        ]);

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertOk();

        $this->assertSame($branchA->id, tenant()->branchId());
    }

    public function test_all_branches_context_blocked_without_hq_permission(): void
    {
        [$company, $branchA] = $this->branches();
        $user = $this->userWithBranches($company, [$branchA->id], $branchA->id, ['crm.leads.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branchA->id]);

        $this->actingAs($user)
            ->post(route('admin.context.update'), [
                'company_id' => $company->id,
                'branch_id' => '',
            ])
            ->assertForbidden();
    }

    /**
     * @return array{0: Company, 1: Branch, 2?: Branch, 3?: Branch, 4?: Branch}
     */
    protected function branches(int $branchCount = 2): array
    {
        $company = Company::factory()->create();
        $definitions = [
            ['code' => 'HQ', 'name' => 'Head Office', 'is_head_office' => true],
            ['code' => 'BR2', 'name' => 'Branch Two'],
            ['code' => 'BR3', 'name' => 'Branch Three'],
            ['code' => 'BR4', 'name' => 'Branch Four'],
        ];

        $created = collect();
        for ($i = 0; $i < $branchCount; $i++) {
            $created->push(Branch::factory()->create(array_merge(
                ['company_id' => $company->id],
                $definitions[$i] ?? ['code' => 'BR'.($i + 1), 'name' => 'Branch '.($i + 1)],
            )));
        }

        return array_merge([$company], $created->all());
    }

    protected function userWithBranches(Company $company, array $branchIds, int $primaryBranchId, array $permissions): User
    {
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $primaryBranchId,
        ]);

        app(UserBranchAccessService::class)->syncAssignments($user, $branchIds, $primaryBranchId);

        $role = Role::create(['name' => 'test-role-'.uniqid(), 'guard_name' => 'web']);
        $role->givePermissionTo($permissions);
        $user->assignRole($role);

        return $user;
    }

    protected function leadForBranch(Company $company, Branch $branch): Lead
    {
        return Lead::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'lead_name' => 'Lead '.$branch->code,
            'status' => LeadStatus::Open,
        ]);
    }
}
