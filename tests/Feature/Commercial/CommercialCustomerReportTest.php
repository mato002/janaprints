<?php

namespace Tests\Feature\Commercial;

use App\Enums\SalesOrderStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CommercialCustomerReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_customer_reports_requires_permission(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['crm.customers.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.commercial.reports.customers.index'))
            ->assertForbidden();
    }

    public function test_customer_reports_index_loads(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['commercial.reports.customers.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.commercial.reports.customers.index'))
            ->assertOk()
            ->assertSee(__('Customer Reports'), false)
            ->assertSee(__('Customer Dashboard'), false)
            ->assertSee(__('Customer Summary'), false);
    }

    public function test_customer_reports_show_kpis_for_customers_and_orders(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['commercial.reports.customers.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $customer = Customer::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
        ]);

        SalesOrder::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'status' => SalesOrderStatus::Confirmed,
            'order_date' => now()->toDateString(),
            'total_amount' => 18000,
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->get(route('admin.commercial.reports.customers.index'))
            ->assertOk()
            ->assertSee(__('Total Customers'), false)
            ->assertSee(__('Top Customer Revenue'), false)
            ->assertSee('18,000', false);
    }

    public function test_filters_persist_in_query_string(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['commercial.reports.customers.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.commercial.reports.customers.index', [
                'from_date' => '2026-01-01',
                'to_date' => '2026-01-31',
                'branch_id' => $branch->id,
                'tab' => 'growth',
                'search' => 'ACME',
                'activity_status' => 'active',
            ]))
            ->assertOk()
            ->assertSee('value="2026-01-01"', false)
            ->assertSee('value="2026-01-31"', false)
            ->assertSee('ACME', false)
            ->assertSee(__('Customer Growth'), false);
    }

    public function test_export_requires_permission(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['commercial.reports.customers.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->post(route('admin.commercial.reports.customers.export', ['tab' => 'summary']), ['format' => 'csv'])
            ->assertForbidden();
    }

    public function test_export_streams_file(): void
    {
        [$company, $branch, $user] = $this->tenantUser([
            'commercial.reports.customers.view',
            'commercial.reports.customers.export',
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $response = $this->actingAs($user)
            ->post(route('admin.commercial.reports.customers.export', ['tab' => 'summary']), ['format' => 'csv']);

        $response->assertOk();
        $response->assertHeader('X-Erp-Export', 'direct');
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment', (string) $response->headers->get('Content-Disposition'));
    }

    public function test_customer_list_tab_renders_enum_labels(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['commercial.reports.customers.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        Customer::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'company_name' => 'Enum Test Customer',
        ]);

        $this->actingAs($user)
            ->get(route('admin.commercial.reports.customers.index', [
                'tab' => 'active',
                'embedded' => '1',
            ]))
            ->assertOk()
            ->assertSee('Enum Test Customer', false)
            ->assertSee('Corporate', false);
    }

    /**
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
        Role::findByName('Viewer', 'web')->syncPermissions($permissions);
        $user->assignRole('Viewer');

        return [$company, $branch, $user];
    }
}
