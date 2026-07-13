<?php

namespace Tests\Feature\Commercial;

use App\Enums\LeadStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Crm\Lead;
use App\Models\Sales\Quotation;
use App\Models\User;
use App\Support\Crm\CustomerOperationalGuard;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CommercialComFormsFinalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_crm_section_shows_create_customer_cta(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['crm.customers.create', 'crm.customers.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $response = $this->actingAs($user)
            ->get(route('admin.workspaces.commercial.section', ['section' => 'crm']));

        $response->assertOk();
        $response->assertSee(__('Create customer'), false);
        $response->assertSee(route('admin.crm.customers.create', ['from' => 'commercial']), false);
    }

    public function test_customer_create_redirects_to_show(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['crm.customers.create']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)->post(route('admin.crm.customers.store'), [
            'company_name' => 'Acme Ltd',
            'customer_type' => 'corporate',
            'status' => 'active',
        ])->assertRedirect();

        $customer = Customer::query()->where('company_name', 'Acme Ltd')->first();
        $this->assertNotNull($customer);
    }

    public function test_quotation_create_preselects_customer_id(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['quotations.create', 'quotations.view', 'crm.customers.view']);

        $customer = Customer::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $response = $this->actingAs($user)
            ->get(route('admin.quotations.create', ['customer_id' => $customer->id]));

        $response->assertOk();
        $response->assertSee('value="'.$customer->id.'" selected', false);
    }

    public function test_quotation_create_rejects_foreign_tenant_customer(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['quotations.create', 'crm.customers.view']);
        $otherCompany = Company::factory()->create();
        $otherBranch = Branch::factory()->create(['company_id' => $otherCompany->id]);
        $foreignCustomer = Customer::factory()->create([
            'company_id' => $otherCompany->id,
            'branch_id' => $otherBranch->id,
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.quotations.create', ['customer_id' => $foreignCustomer->id]))
            ->assertNotFound();
    }

    public function test_lead_convert_to_customer_works(): void
    {
        [$company, $branch, $user] = $this->tenantUser([
            'crm.leads.view', 'crm.leads.edit', 'crm.customers.create', 'crm.customers.view',
        ]);

        $lead = Lead::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'lead_name' => 'Jane Prospect',
            'company_name' => 'Prospect Co',
            'status' => LeadStatus::Open,
            'estimated_value' => 0,
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->post(route('admin.crm.leads.convert', $lead))
            ->assertRedirect(route('admin.crm.customers.show', Customer::query()->latest('id')->first()));

        $lead->refresh();
        $this->assertNotNull($lead->customer_id);
        $this->assertSame('won', $lead->status->value);
    }

    public function test_customer_with_history_cannot_be_deleted(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['crm.customers.delete', 'crm.customers.view']);

        $customer = Customer::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
        ]);

        Quotation::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->assertTrue(app(CustomerOperationalGuard::class)->hasOperationalHistory($customer));
        $this->assertFalse($user->can('delete', $customer));
    }

    public function test_form_control_includes_commercial_create_forms(): void
    {
        $forms = array_keys(config('form_registry.forms', []));

        foreach ([
            'customer', 'lead', 'quotation', 'artwork', 'activity.create', 'segment.create',
            'commercial_complaint.create', 'commercial_support_ticket.create', 'pos_sale.create',
        ] as $key) {
            $this->assertContains($key, $forms, "Missing form registry key: {$key}");
        }

        $this->assertNotContains('customer.edit', $forms);
        $this->assertNotContains('quotation.edit', $forms);
    }

    public function test_commercial_quick_create_routes_exist(): void
    {
        foreach (config('workspaces.commercial.quick_create', []) as $item) {
            if (! empty($item['coming_soon'])) {
                continue;
            }

            $this->assertTrue(Route::has($item['route']), 'Missing quick create route: '.$item['route']);
        }
    }

    public function test_sales_section_shows_sales_order_note(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['quotations.view', 'sales_orders.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $response = $this->actingAs($user)
            ->get(route('admin.workspaces.commercial.section', ['section' => 'sales']));

        $response->assertOk();
        $response->assertSee(route('admin.sales-orders.index', ['embedded' => '1']), false);
    }

    /**
     * @param  list<string>  $permissions
     * @return array{0: Company, 1: Branch, 2: User}
     */
    protected function tenantUser(array $permissions): array
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $role = Role::findByName('Sales', 'web');
        $role->syncPermissions($permissions);
        $user->assignRole('Sales');

        return [$company, $branch, $user];
    }
}
