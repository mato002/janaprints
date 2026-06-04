<?php

namespace Tests\Feature\Production;

use App\Enums\CustomerStatus;
use App\Enums\ProductionType;
use App\Enums\SalesOrderStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Production\JobCostSheet;
use App\Models\Production\ProductionJobCard;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use App\Services\Production\JobProfitabilityDashboardService;
use App\Support\TenantContext;
use Database\Seeders\ProductionFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class JobProfitabilityDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_profitability_dashboard_loads(): void
    {
        [$company, $branch, , $user] = $this->profitabilityContext(['production.costing.view', 'production.view']);

        $this->bindTenant($company, $branch);

        $this->actingAs($user)
            ->get(route('admin.production.costing.dashboard'))
            ->assertOk()
            ->assertSee(__('Job Profitability Command Center'), false)
            ->assertSee(__('Revenue This Month'), false)
            ->assertSee(__('Profitability Health'), false)
            ->assertSee(__('Top Profitable Jobs'), false)
            ->assertSee(__('Loss-Making Jobs'), false)
            ->assertSee(__('Product / Service Profitability'), false)
            ->assertSee(__('Most Profitable Customers'), false)
            ->assertSee(__('Branch Profitability'), false)
            ->assertSee(__('Cost Driver Breakdown'), false)
            ->assertSee(__('Profitability Alerts'), false);
    }

    public function test_dashboard_renders_with_cost_sheet_data(): void
    {
        [$company, $branch, $customer, $user, $jobCard] = $this->seedProfitableJob();

        $this->bindTenant($company, $branch);

        $this->actingAs($user)
            ->get(route('admin.production.costing.dashboard'))
            ->assertOk()
            ->assertSee($jobCard->job_card_number, false)
            ->assertSee($customer->company_name, false)
            ->assertSee(__('Healthy Jobs'), false);
    }

    protected function bindTenant(Company $company, Branch $branch): void
    {
        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);
        app()->instance(TenantContext::class, new TenantContext($company, $branch, false));
    }

    public function test_kpi_strip_and_health_panel_from_service(): void
    {
        [$company, $branch, , $user] = $this->seedProfitableJob();
        $this->bindTenant($company, $branch);

        $payload = app(JobProfitabilityDashboardService::class)->build(request(), $user);

        $this->assertCount(8, $payload['kpis']);
        $this->assertArrayHasKey('healthy', $payload['health']);
        $this->assertArrayHasKey('loss_making', $payload['health']);
        $this->assertArrayHasKey('missing_costing', $payload['health']);
        $this->assertGreaterThan(0, $payload['health']['healthy']['count']);
    }

    public function test_loss_making_jobs_render(): void
    {
        [$company, $branch, , $user, $jobCard] = $this->seedLossJob();

        $this->bindTenant($company, $branch);

        $this->actingAs($user)
            ->get(route('admin.production.costing.dashboard'))
            ->assertOk()
            ->assertSee($jobCard->job_card_number, false)
            ->assertSee(__('Review costing inputs'), false);
    }

    public function test_cost_driver_empty_state_when_no_breakdown(): void
    {
        [$company, $branch, , $user] = $this->profitabilityContext(['production.costing.view']);

        $this->bindTenant($company, $branch);

        $this->actingAs($user)
            ->get(route('admin.production.costing.dashboard'))
            ->assertOk()
            ->assertSee(__('Cost driver breakdown will appear once detailed costing inputs are available.'), false);
    }

    public function test_cost_drivers_render_when_material_cost_exists(): void
    {
        [$company, $branch, , $user] = $this->seedProfitableJob(materialCost: 1500);

        $this->bindTenant($company, $branch);

        $this->actingAs($user)
            ->get(route('admin.production.costing.dashboard'))
            ->assertOk()
            ->assertSee(__('Materials'), false);
    }

    public function test_margin_category_filter_works(): void
    {
        [$company, $branch, , $user] = $this->seedProfitableJob();

        $this->bindTenant($company, $branch);

        $this->actingAs($user)
            ->get(route('admin.production.costing.dashboard', ['margin_category' => 'healthy']))
            ->assertOk()
            ->assertSee(__('Healthy Jobs'), false);
    }

    public function test_date_filter_preserves_query_string(): void
    {
        [$company, $branch, , $user] = $this->profitabilityContext(['production.costing.view']);

        $this->bindTenant($company, $branch);

        $this->actingAs($user)
            ->get(route('admin.production.costing.dashboard', [
                'date_from' => '2026-01-01',
                'date_to' => '2026-06-30',
            ]))
            ->assertOk()
            ->assertSee('value="2026-01-01"', false)
            ->assertSee('value="2026-06-30"', false);
    }

    public function test_job_360_link_when_user_has_permission(): void
    {
        [$company, $branch, , $user, $jobCard] = $this->seedProfitableJob(['production.costing.view', 'production.view']);
        $this->bindTenant($company, $branch);
        $this->actingAs($user);

        $payload = app(JobProfitabilityDashboardService::class)->build(request(), $user);
        $row = $payload['top_profitable']->first();

        $this->assertTrue($payload['can_view_job_360']);
        $this->assertNotNull($row);
        $this->assertNotNull($row['job_360_url']);
        $this->assertStringContainsString((string) $jobCard->id, $row['job_360_url']);
    }

    public function test_job_360_link_hidden_without_view_permission(): void
    {
        [$company, $branch, , $user] = $this->seedProfitableJob(['production.costing.view']);
        $this->bindTenant($company, $branch);
        $this->actingAs($user);

        $payload = app(JobProfitabilityDashboardService::class)->build(request(), $user);
        $row = $payload['top_profitable']->first();

        $this->assertFalse($payload['can_view_job_360']);
        $this->assertNotNull($row);
        $this->assertNull($row['job_360_url']);
    }

    public function test_customer_360_link_when_user_has_crm_permission(): void
    {
        [$company, $branch, $customer, $user] = $this->seedProfitableJob(['production.costing.view', 'crm.customers.view']);
        $this->bindTenant($company, $branch);
        $this->actingAs($user);

        $payload = app(JobProfitabilityDashboardService::class)->build(request(), $user);
        $topCustomer = $payload['top_customers'][0] ?? null;

        $this->assertTrue($payload['can_view_customer_360']);
        $this->assertNotNull($topCustomer);
        $this->assertNotNull($topCustomer['customer_url']);
        $this->assertStringContainsString((string) $customer->id, $topCustomer['customer_url']);
    }

    public function test_no_export_button_when_route_missing(): void
    {
        [$company, $branch, , $user] = $this->profitabilityContext(['production.costing.view']);

        $this->bindTenant($company, $branch);

        $this->assertFalse(Route::has('admin.production.costing.export'));

        $this->actingAs($user)
            ->get(route('admin.production.costing.dashboard'))
            ->assertOk()
            ->assertDontSee(__('Export'), false);
    }

    public function test_empty_dataset_does_not_break_page(): void
    {
        [$company, $branch, , $user] = $this->profitabilityContext(['production.costing.view']);

        $this->bindTenant($company, $branch);

        $this->actingAs($user)
            ->get(route('admin.production.costing.dashboard'))
            ->assertOk()
            ->assertSee(__('No profitable jobs found yet.'), false)
            ->assertSee(__('No loss-making jobs found.'), false);
    }

    public function test_unauthorized_user_receives_403(): void
    {
        [$company, $branch, , $user] = $this->profitabilityContext(['production.view']);

        $this->bindTenant($company, $branch);

        $this->actingAs($user)
            ->get(route('admin.production.costing.dashboard'))
            ->assertForbidden();
    }

    public function test_dashboard_query_count_is_bounded(): void
    {
        [$company, $branch, , $user] = $this->seedProfitableJob(['production.costing.view', 'production.view']);

        $this->bindTenant($company, $branch);

        DB::flushQueryLog();
        DB::enableQueryLog();

        $this->actingAs($user)->get(route('admin.production.costing.dashboard'))->assertOk();

        $this->assertLessThan(60, count(DB::getQueryLog()));
    }

    /**
     * @return array{0: Company, 1: Branch, 2: Customer, 3: User}
     */
    protected function profitabilityContext(?array $permissions = null): array
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $customer = Customer::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'company_name' => 'Profitability Test Customer',
            'status' => CustomerStatus::Active,
        ]);
        $permissions ??= ['production.costing.view'];
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $role = Role::findByName('Production', 'web');
        $role->syncPermissions($permissions);
        $user->assignRole('Production');

        $this->seed(ProductionFoundationSeeder::class);

        return [$company, $branch, $customer, $user];
    }

    /**
     * @return array{0: Company, 1: Branch, 2: Customer, 3: User, 4: ProductionJobCard}
     */
    protected function seedProfitableJob(?array $permissions = null, float $materialCost = 0): array
    {
        [$company, $branch, $customer, $user] = $this->profitabilityContext(
            $permissions ?? ['production.costing.view', 'production.view'],
        );

        $salesOrder = SalesOrder::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'status' => SalesOrderStatus::Confirmed,
            'total_amount' => 10000,
            'created_by' => $user->id,
        ]);

        $jobCard = ProductionJobCard::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'sales_order_id' => $salesOrder->id,
            'production_type' => ProductionType::Digital,
            'created_by' => $user->id,
        ]);

        JobCostSheet::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'production_job_card_id' => $jobCard->id,
            'material_cost' => $materialCost,
            'labor_cost' => 500,
            'machine_cost' => 300,
            'finishing_cost' => 200,
            'outsourced_cost' => 0,
            'overhead_cost' => 100,
            'total_cost' => $materialCost + 1100,
            'revenue' => 10000,
            'gross_profit' => 10000 - ($materialCost + 1100),
            'gross_margin_percent' => round(((10000 - ($materialCost + 1100)) / 10000) * 100, 2),
            'calculated_at' => now(),
        ]);

        return [$company, $branch, $customer, $user, $jobCard];
    }

    /**
     * @return array{0: Company, 1: Branch, 2: Customer, 3: User, 4: ProductionJobCard}
     */
    protected function seedLossJob(): array
    {
        [$company, $branch, $customer, $user] = $this->profitabilityContext(['production.costing.view', 'production.view']);

        $salesOrder = SalesOrder::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'status' => SalesOrderStatus::Confirmed,
            'total_amount' => 3000,
            'created_by' => $user->id,
        ]);

        $jobCard = ProductionJobCard::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'sales_order_id' => $salesOrder->id,
            'production_type' => ProductionType::Offset,
            'created_by' => $user->id,
        ]);

        JobCostSheet::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'production_job_card_id' => $jobCard->id,
            'material_cost' => 4000,
            'total_cost' => 4000,
            'revenue' => 3000,
            'gross_profit' => -1000,
            'gross_margin_percent' => -33.33,
            'calculated_at' => now(),
        ]);

        return [$company, $branch, $customer, $user, $jobCard];
    }
}
