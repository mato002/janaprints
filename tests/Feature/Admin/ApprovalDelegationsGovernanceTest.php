<?php

namespace Tests\Feature\Admin;

use App\Enums\DelegationStatus;
use App\Enums\QuotationStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Platform\ApprovalDelegation;
use App\Models\Sales\Quotation;
use App\Models\SecurityAuditEvent;
use App\Models\User;
use App\Support\Platform\ApprovalDelegationService;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\PlatformConfigurationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ApprovalDelegationsGovernanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        $this->seed(PlatformConfigurationSeeder::class);
    }

    public function test_delegations_dashboard_is_accessible(): void
    {
        $user = $this->userWithPermissions(['governance.delegations.view']);
        $company = Company::query()->where('code', 'JANA')->firstOrFail();

        $this->actingAs($user)
            ->get(route('admin.governance.delegations.index', ['company_id' => $company->id]))
            ->assertOk()
            ->assertSee(__('Approval Delegations'))
            ->assertSee(__('Delegator'));
    }

    public function test_admin_can_create_delegation(): void
    {
        $actor = $this->userWithPermissions([
            'governance.delegations.view',
            'governance.delegations.create',
        ]);
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();

        $delegator = $this->makeCompanyUser($company, $branch, 'Branch Manager', ['quotations.approve']);
        $delegate = $this->makeCompanyUser($company, $branch, 'Sales', ['quotations.view']);

        $this->actingAs($actor)
            ->post(route('admin.governance.delegations.store'), [
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'delegator_user_id' => $delegator->id,
                'delegate_user_id' => $delegate->id,
                'modules' => ['commercial'],
                'approval_types' => ['quotation_approval'],
                'reason' => 'annual_leave',
                'start_date' => now()->toDateString(),
                'end_date' => now()->addDays(7)->toDateString(),
                'notes' => 'Annual leave coverage',
            ])
            ->assertRedirect(route('admin.governance.delegations.index', [
                'company_id' => $company->id,
                'branch_id' => $branch->id,
            ]));

        $delegation = ApprovalDelegation::query()
            ->where('delegator_user_id', $delegator->id)
            ->where('delegate_user_id', $delegate->id)
            ->first();

        $this->assertNotNull($delegation);
        $this->assertSame(DelegationStatus::Active, $delegation->status);
        $this->assertDatabaseHas('security_audit_events', [
            'action' => 'delegation.created',
            'module' => 'governance',
            'entity' => 'approval_delegation',
        ]);
    }

    public function test_delegate_can_approve_quotation_through_delegation(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();

        $delegator = $this->makeCompanyUser($company, $branch, 'Branch Manager', ['quotations.approve']);
        $delegate = $this->makeCompanyUser($company, $branch, 'Sales Delegate', ['quotations.view']);

        ApprovalDelegation::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'delegator_user_id' => $delegator->id,
            'delegate_user_id' => $delegate->id,
            'modules' => ['commercial'],
            'approval_types' => ['quotation_approval'],
            'reason' => 'annual_leave',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(5)->toDateString(),
            'status' => DelegationStatus::Active,
            'created_by_user_id' => $delegator->id,
        ]);

        $quotation = Quotation::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'status' => QuotationStatus::PendingApproval,
            'prepared_by' => $delegate->id,
        ]);

        $this->actingAs($delegate)
            ->post(route('admin.quotations.approve', $quotation))
            ->assertRedirect();

        $quotation->refresh();
        $this->assertSame(QuotationStatus::Sent, $quotation->status);
        $this->assertSame($delegate->id, $quotation->approved_by);

        $this->assertTrue(
            SecurityAuditEvent::query()->where('action', 'quotation.approved_via_delegation')->exists()
        );
    }

    public function test_expired_delegation_is_not_operational(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();
        $service = app(ApprovalDelegationService::class);

        $delegator = $this->makeCompanyUser($company, $branch, 'Branch Manager', ['quotations.approve']);
        $delegate = $this->makeCompanyUser($company, $branch, 'Sales Delegate', ['quotations.view']);

        $delegation = ApprovalDelegation::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'delegator_user_id' => $delegator->id,
            'delegate_user_id' => $delegate->id,
            'modules' => ['commercial'],
            'approval_types' => ['quotation_approval'],
            'reason' => 'temporary_absence',
            'start_date' => now()->subDays(10)->toDateString(),
            'end_date' => now()->subDay()->toDateString(),
            'status' => DelegationStatus::Active,
            'created_by_user_id' => $delegator->id,
        ]);

        $service->syncStatuses($company->id);

        $delegation->refresh();
        $this->assertSame(DelegationStatus::Expired, $delegation->status);
        $this->assertFalse($service->canActAsDelegate(
            $delegate,
            'quotation_approval',
            'commercial',
            $company->id,
            $branch->id,
            'quotations.approve',
        ));
    }

    public function test_expired_delegate_cannot_approve_quotation(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();

        $delegator = $this->makeCompanyUser($company, $branch, 'Branch Manager', ['quotations.approve']);
        $delegate = $this->makeCompanyUser($company, $branch, 'Sales Delegate', ['quotations.view']);

        ApprovalDelegation::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'delegator_user_id' => $delegator->id,
            'delegate_user_id' => $delegate->id,
            'modules' => ['commercial'],
            'approval_types' => ['quotation_approval'],
            'reason' => 'sick_leave',
            'start_date' => now()->subDays(10)->toDateString(),
            'end_date' => now()->subDay()->toDateString(),
            'status' => DelegationStatus::Expired,
            'created_by_user_id' => $delegator->id,
        ]);

        $quotation = Quotation::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'status' => QuotationStatus::PendingApproval,
        ]);

        $this->actingAs($delegate)
            ->post(route('admin.quotations.approve', $quotation))
            ->assertForbidden();
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

        $role = Role::create(['name' => 'Delegation Tester '.uniqid(), 'guard_name' => 'web']);
        $role->syncPermissions($permissions);
        $user->assignRole($role);

        return $user;
    }

    /**
     * @param  list<string>  $permissions
     */
    protected function makeCompanyUser(Company $company, Branch $branch, string $roleLabel, array $permissions): User
    {
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $role = Role::create(['name' => $roleLabel.' '.uniqid(), 'guard_name' => 'web']);
        $role->syncPermissions($permissions);
        $user->assignRole($role);

        return $user;
    }
}
