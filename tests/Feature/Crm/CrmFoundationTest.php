<?php

namespace Tests\Feature\Crm;

use App\Enums\ActivityType;
use App\Enums\CustomerStatus;
use App\Models\ActivityLog;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Crm\CustomerActivity;
use App\Models\Crm\CustomerSegment;
use App\Models\Crm\Lead;
use App\Models\Crm\LeadStage;
use App\Models\User;
use Database\Seeders\CrmFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CrmFoundationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_company_isolation_for_customers(): void
    {
        $companyA = Company::factory()->create(['code' => 'CA']);
        $companyB = Company::factory()->create(['code' => 'CB']);
        $branchA = Branch::factory()->create(['company_id' => $companyA->id, 'code' => 'BA']);
        $branchB = Branch::factory()->create(['company_id' => $companyB->id, 'code' => 'BB']);

        $this->seedCompanyCrm($companyA);
        $this->seedCompanyCrm($companyB);

        $salesA = $this->userWithRole('Sales', $companyA, $branchA, ['crm.customers.view']);
        $customerB = Customer::factory()->create([
            'company_id' => $companyB->id,
            'branch_id' => $branchB->id,
            'customer_code' => 'CUST-00001',
            'company_name' => 'Other Co',
            'status' => CustomerStatus::Active,
        ]);

        $this->actingAs($salesA)
            ->get(route('admin.crm.customers.show', $customerB))
            ->assertForbidden();
    }

    public function test_sales_user_can_create_customer(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $this->seedCompanyCrm($company);

        $user = $this->userWithRole('Sales', $company, $branch, [
            'crm.customers.view', 'crm.customers.create',
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $response = $this->actingAs($user)->post(route('admin.crm.customers.store'), [
            'customer_type' => 'corporate',
            'company_name' => 'Acme Ltd',
            'status' => 'active',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('customers', [
            'company_name' => 'Acme Ltd',
            'company_id' => $company->id,
            'branch_id' => $branch->id,
        ]);
    }

    public function test_lead_creation_requires_permission(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $this->seedCompanyCrm($company);

        $viewer = $this->userWithRole('Viewer', $company, $branch, ['crm.leads.view']);

        $this->actingAs($viewer)
            ->get(route('admin.crm.leads.create'))
            ->assertForbidden();
    }

    public function test_customer_activity_is_logged(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $this->seedCompanyCrm($company);

        $user = $this->userWithRole('Sales', $company, $branch, [
            'crm.customers.view', 'crm.customers.edit', 'crm.activities.create',
        ]);

        $customer = Customer::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_code' => 'CUST-00001',
            'company_name' => 'Test Co',
            'status' => CustomerStatus::Active,
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)->post(route('admin.crm.customers.activities.store', $customer), [
            'activity_type' => ActivityType::Call->value,
            'subject' => 'Intro call',
            'activity_at' => now()->format('Y-m-d H:i:s'),
        ])->assertRedirect();

        $this->assertDatabaseHas('customer_activities', [
            'customer_id' => $customer->id,
            'subject' => 'Intro call',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'created',
            'model_type' => CustomerActivity::class,
        ]);
    }

    public function test_company_isolation_for_customer_segments(): void
    {
        $companyA = Company::factory()->create(['code' => 'SGA']);
        $companyB = Company::factory()->create(['code' => 'SGB']);
        $branchA = Branch::factory()->create(['company_id' => $companyA->id, 'code' => 'SBA']);
        Branch::factory()->create(['company_id' => $companyB->id, 'code' => 'SBB']);

        $segmentB = CustomerSegment::query()->create([
            'company_id' => $companyB->id,
            'name' => 'Other Segment',
            'code' => 'OTHER',
            'is_active' => true,
        ]);

        $user = $this->userWithRole('Sales', $companyA, $branchA, [
            'crm.customers.view', 'crm.customers.edit',
        ]);

        session(['active_company_id' => $companyA->id, 'active_branch_id' => $branchA->id]);

        $this->actingAs($user)
            ->get(route('admin.crm.segments.edit', $segmentB))
            ->assertNotFound();
    }

    public function test_sales_user_can_create_lead(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $this->seedCompanyCrm($company);
        $stage = LeadStage::query()->where('company_id', $company->id)->first();

        $user = $this->userWithRole('Sales', $company, $branch, [
            'crm.leads.view', 'crm.leads.create',
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)->post(route('admin.crm.leads.store'), [
            'lead_name' => 'Big Deal',
            'stage_id' => $stage->id,
            'status' => 'open',
        ])->assertRedirect();

        $this->assertDatabaseHas('leads', [
            'lead_name' => 'Big Deal',
            'company_id' => $company->id,
        ]);
    }

    protected function seedCompanyCrm(Company $company): void
    {
        (new CrmFoundationSeeder)->run();
    }

    protected function userWithRole(string $role, Company $company, Branch $branch, array $permissions): User
    {
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $roleModel = Role::findByName($role, 'web');
        $roleModel->syncPermissions($permissions);
        $user->assignRole($role);

        return $user;
    }
}
