<?php

namespace Tests\Feature\Admin;

use App\Enums\CustomerStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Crm\Lead;
use App\Models\Crm\LeadStage;
use App\Models\User;
use Database\Seeders\CrmFoundationSeeder;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\PlatformConfigurationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ModalFormWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        $this->seed(PlatformConfigurationSeeder::class);
    }

    public function test_customer_index_uses_modal_create_links(): void
    {
        [$company, $branch, $user] = $this->tenantContext();

        $this->actingAs($user)
            ->get(route('admin.crm.customers.index'))
            ->assertOk()
            ->assertSee('data-erp-modal-open', false)
            ->assertSee(route('admin.crm.customers.create'), false);
    }

    public function test_customer_create_renders_inside_modal_frame(): void
    {
        [$company, $branch, $user] = $this->tenantContext();

        $this->actingAs($user)
            ->withHeader('Turbo-Frame', 'erp-form-modal')
            ->get(route('admin.crm.customers.create'))
            ->assertOk()
            ->assertSee('data-erp-form-modal-panel', false)
            ->assertSee(__('Create customer'), false);
    }

    public function test_modal_store_validation_redirects_back_to_create_form(): void
    {
        [$company, $branch, $user] = $this->tenantContext();
        $createUrl = route('admin.crm.customers.create');

        $this->actingAs($user)
            ->post(route('admin.crm.customers.store'), [
                '_erp_modal' => '1',
                '_erp_modal_return' => $createUrl,
                'status' => 'active',
                'credit_limit' => 0,
            ])
            ->assertStatus(422)
            ->assertSee('data-erp-form-modal-panel', false)
            ->assertSee('data-erp-validation-errors', false);
    }

    public function test_modal_store_validation_uses_inferred_create_route_when_return_url_is_workspace(): void
    {
        [$company, $branch, $user] = $this->tenantContext();
        $workspaceUrl = route('admin.workspaces.commercial.section', ['section' => 'customers']);

        $this->actingAs($user)
            ->post(route('admin.crm.customers.store'), [
                '_erp_modal' => '1',
                '_erp_modal_return' => $workspaceUrl,
                'status' => 'active',
                'credit_limit' => 0,
            ])
            ->assertStatus(422)
            ->assertSee('data-erp-form-modal-panel', false)
            ->assertSee('data-erp-validation-message', false)
            ->assertSee(__('The customer type field is required.'), false);
    }

    public function test_customer_store_from_modal_with_empty_credit_limit_succeeds(): void
    {
        [$company, $branch, $user] = $this->tenantContext();

        $response = $this->actingAs($user)
            ->withHeader('Turbo-Frame', 'erp-form-modal')
            ->post(route('admin.crm.customers.store'), [
                'customer_type' => 'corporate',
                'company_name' => 'Empty Credit Customer',
                'status' => 'active',
                'credit_limit' => '',
            ]);

        $response->assertOk();
        $response->assertSee('data-erp-modal-success', false);

        $this->assertDatabaseHas('customers', [
            'company_name' => 'Empty Credit Customer',
            'company_id' => $company->id,
            'credit_limit' => 0,
        ]);
    }

    public function test_customer_store_from_modal_returns_success_marker_without_redirect(): void
    {
        [$company, $branch, $user] = $this->tenantContext();

        $response = $this->actingAs($user)
            ->withHeader('Turbo-Frame', 'erp-form-modal')
            ->post(route('admin.crm.customers.store'), [
                'customer_type' => 'corporate',
                'company_name' => 'Modal Customer Ltd',
                'status' => 'active',
                'credit_limit' => 0,
            ]);

        $response->assertOk();
        $response->assertSee('data-erp-modal-success', false);
        $response->assertSee(__('Customer created.'), false);

        $this->assertDatabaseHas('customers', [
            'company_name' => 'Modal Customer Ltd',
            'company_id' => $company->id,
        ]);
    }

    public function test_customer_store_from_commercial_workspace_modal_returns_success_marker(): void
    {
        [$company, $branch, $user] = $this->tenantContext();

        $createUrl = route('admin.crm.customers.create', ['from' => 'commercial']);

        $response = $this->actingAs($user)
            ->withHeader('Turbo-Frame', 'erp-form-modal')
            ->post(route('admin.crm.customers.store'), [
                '_erp_modal' => '1',
                '_erp_modal_return' => $createUrl,
                'customer_type' => 'corporate',
                'company_name' => 'Commercial Desk Customer Ltd',
                'status' => 'active',
                'credit_limit' => 0,
            ]);

        $response->assertOk();
        $response->assertSee('data-erp-modal-success', false);
        $response->assertSee(__('Customer created.'), false);

        $this->assertDatabaseHas('customers', [
            'company_name' => 'Commercial Desk Customer Ltd',
            'company_id' => $company->id,
        ]);
    }

    public function test_customer_store_from_sales_desk_modal_returns_success_marker_and_persists(): void
    {
        [$company, $branch, $user] = $this->tenantContext();
        $createUrl = route('admin.crm.customers.create', ['from' => 'sales-desk']);

        $response = $this->actingAs($user)
            ->post(route('admin.crm.customers.store'), [
                '_erp_modal' => '1',
                '_erp_modal_return' => $createUrl,
                'from' => 'sales-desk',
                'customer_type' => 'corporate',
                'company_name' => 'Sales Desk Modal Customer Ltd',
                'status' => 'active',
                'credit_limit' => 0,
            ]);

        $response->assertOk();

        $customer = Customer::query()->where('company_name', 'Sales Desk Modal Customer Ltd')->firstOrFail();

        $response->assertSee('data-erp-modal-success', false);
        $response->assertSee(__('Customer created.'), false);
        $response->assertSee('admin/sales/desk?customer='.$customer->getRouteKey(), false);
        $response->assertSee('step=2', false);

        $this->assertDatabaseHas('customers', [
            'company_name' => 'Sales Desk Modal Customer Ltd',
            'company_id' => $company->id,
        ]);
    }

    public function test_customer_store_outside_modal_still_redirects_to_show(): void
    {
        [$company, $branch, $user] = $this->tenantContext();

        $response = $this->actingAs($user)->post(route('admin.crm.customers.store'), [
            'customer_type' => 'corporate',
            'company_name' => 'Full Page Customer Ltd',
            'status' => 'active',
            'credit_limit' => 0,
        ]);

        $customer = Customer::query()->where('company_name', 'Full Page Customer Ltd')->firstOrFail();

        $response->assertRedirect(route('admin.crm.customers.show', $customer));
    }

    public function test_lead_store_from_modal_returns_success_marker(): void
    {
        [$company, $branch, $user] = $this->tenantContext();

        $stage = LeadStage::query()->where('company_id', $company->id)->firstOrFail();

        $response = $this->actingAs($user)
            ->withHeader('Turbo-Frame', 'erp-form-modal')
            ->post(route('admin.crm.leads.store'), [
                'lead_name' => 'Modal Lead',
                'stage_id' => $stage->id,
                'status' => 'open',
                'estimated_value' => 1200,
            ]);

        $response->assertOk();
        $response->assertSee('data-erp-modal-success', false);
        $response->assertSee(__('Lead created.'), false);

        $this->assertDatabaseHas('leads', [
            'lead_name' => 'Modal Lead',
            'company_id' => $company->id,
        ]);
    }

    public function test_lead_store_validation_keeps_modal_form_with_errors(): void
    {
        [$company, $branch, $user] = $this->tenantContext();
        $createUrl = route('admin.crm.leads.create');

        $this->actingAs($user)
            ->from($createUrl)
            ->withHeader('Turbo-Frame', 'erp-form-modal')
            ->followingRedirects()
            ->post(route('admin.crm.leads.store'), [
                '_erp_modal' => '1',
                '_erp_modal_return' => $createUrl,
                'status' => 'open',
            ])
            ->assertOk()
            ->assertSee('data-erp-form-modal-panel', false)
            ->assertSee('data-erp-validation-errors', false)
            ->assertSee('data-erp-field-error="lead_name"', false);
    }

    public function test_segment_store_from_modal_returns_success_marker_via_middleware(): void
    {
        [$company, $branch, $user] = $this->tenantContext();

        $response = $this->actingAs($user)
            ->withHeader('Turbo-Frame', 'erp-form-modal')
            ->post(route('admin.crm.segments.store'), [
                'company_id' => $company->id,
                'name' => 'VIP Clients',
                'code' => 'VIP',
                'is_active' => '1',
            ]);

        $response->assertOk();
        $response->assertSee('data-erp-modal-success', false);
        $response->assertSee(__('Segment created.'), false);
    }

    public function test_lead_update_from_modal_returns_success_marker(): void
    {
        [$company, $branch, $user] = $this->tenantContext(['crm.leads.edit']);

        $stage = LeadStage::query()->where('company_id', $company->id)->firstOrFail();

        $this->actingAs($user)->post(route('admin.crm.leads.store'), [
            'lead_name' => 'Original Lead',
            'stage_id' => $stage->id,
            'status' => 'open',
            'estimated_value' => 500,
        ]);

        $lead = Lead::query()->where('lead_name', 'Original Lead')->firstOrFail();

        $response = $this->actingAs($user)
            ->withHeader('Turbo-Frame', 'erp-form-modal')
            ->put(route('admin.crm.leads.update', $lead), [
                'lead_name' => 'Updated Lead',
                'status' => 'open',
                'estimated_value' => 1000,
            ]);

        $response->assertOk();
        $response->assertSee('data-erp-modal-success', false);

        $this->assertDatabaseHas('leads', [
            'id' => $lead->id,
            'lead_name' => 'Updated Lead',
        ]);
    }

    public function test_admin_layout_includes_modal_and_drawer_hosts(): void
    {
        [$company, $branch, $user] = $this->tenantContext();

        $this->actingAs($user)
            ->get(route('admin.crm.customers.index'))
            ->assertOk()
            ->assertSee('id="erp-modal-overlay"', false)
            ->assertSee('id="erp-preview-drawer"', false)
            ->assertSee('id="erp-toast-host"', false);
    }

    /**
     * @param  list<string>  $extraPermissions
     * @return array{0: Company, 1: Branch, 2: User}
     */
    protected function tenantContext(array $extraPermissions = []): array
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $this->seedCrm($company);

        $permissions = array_values(array_unique([
            'crm.customers.view',
            'crm.customers.create',
            'crm.customers.edit',
            'crm.leads.view',
            'crm.leads.create',
            'crm.leads.edit',
            ...$extraPermissions,
        ]));

        $user = $this->userWithRole('Sales', $company, $branch, $permissions);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        return [$company, $branch, $user];
    }

    protected function seedCrm(Company $company): void
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
