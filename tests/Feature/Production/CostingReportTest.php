<?php

namespace Tests\Feature\Production;

use App\Enums\CustomerStatus;
use App\Enums\ProductionType;
use App\Enums\SalesOrderStatus;
use App\Jobs\Commercial\ProcessCommercialReportExportJob;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Production\JobCostSheet;
use App\Models\Production\ProductionJobCard;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use App\Support\Production\Reports\CostingReportQueries;
use App\Support\Production\Reports\CostingReportScope;
use App\Support\TenantContext;
use Database\Seeders\ProductionFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CostingReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_costing_reports_center_loads_with_permission(): void
    {
        [$company, $branch, $customer, $user, $jobCard] = $this->seedCostSheet();

        $this->bindTenant($company, $branch);

        $this->actingAs($user)
            ->get(route('admin.production.reports.index'))
            ->assertOk()
            ->assertSee(__('Costing Reports'), false)
            ->assertSee(__('Job Profitability'), false)
            ->assertSee(__('Product Cost Analysis'), false)
            ->assertSee(__('Paper Consumption'), false)
            ->assertSee(__('Ink Consumption'), false)
            ->assertSee(__('Production Cost Summary'), false)
            ->assertSee(__('Customer Profitability'), false)
            ->assertSee(__('Monthly Margin'), false)
            ->assertSee($jobCard->job_card_number, false)
            ->assertSee($customer->company_name, false);
    }

    public function test_job_profitability_tab_shows_cost_breakdown(): void
    {
        [$company, $branch, , $user, $jobCard] = $this->seedCostSheet();

        $this->bindTenant($company, $branch);

        $this->actingAs($user)
            ->get(route('admin.production.reports.index', ['tab' => 'job_profitability']))
            ->assertOk()
            ->assertSee($jobCard->job_card_number, false)
            ->assertSee('KES 10,000', false)
            ->assertSee('KES 1,500', false);
    }

    public function test_material_cost_rollup_in_product_cost_analysis(): void
    {
        [$company, $branch, , $user] = $this->seedCostSheet(materialCost: 1500);

        $this->bindTenant($company, $branch);

        $scope = new CostingReportScope(
            companyId: $company->id,
            branchId: $branch->id,
            fromDate: now()->subMonth()->toDateString(),
            toDate: now()->addDay()->toDateString(),
            tab: 'product_cost',
        );

        $rows = app(CostingReportQueries::class)->paginateProductCostAnalysis($scope);
        $row = collect($rows->items())->first();

        $this->assertNotNull($row);
        $this->assertSame('KES 1,500', $row['material_cost']);
        $this->assertSame('KES 2,600', $row['total_cost']);

        $this->actingAs($user)
            ->get(route('admin.production.reports.index', ['tab' => 'product_cost']))
            ->assertOk()
            ->assertSee('KES 1,500', false);
    }

    public function test_margin_calculation_matches_job_cost_sheet_truth(): void
    {
        [$company, $branch] = $this->seedCostSheet();

        $scope = new CostingReportScope(
            companyId: $company->id,
            branchId: $branch->id,
            fromDate: now()->subMonth()->toDateString(),
            toDate: now()->addDay()->toDateString(),
        );

        $queries = app(CostingReportQueries::class);
        $totals = $queries->scopedTotals($scope);

        $this->assertSame(10000.0, $totals['revenue']);
        $this->assertSame(2600.0, $totals['total_cost']);
        $this->assertSame(7400.0, $totals['gross_profit']);
        $this->assertSame(74.0, $totals['margin_percent']);

        $monthly = $queries->paginateMonthlyMargin($scope);
        $monthRow = collect($monthly->items())->first();

        $this->assertNotNull($monthRow);
        $this->assertSame('74.0%', $monthRow['margin_percent']);
    }

    public function test_export_queues_background_job(): void
    {
        Queue::fake();

        [$company, $branch, , $user] = $this->seedCostSheet(permissions: ['reports.costing.view', 'reports.costing.export']);

        $this->bindTenant($company, $branch);

        $this->actingAs($user)
            ->post(route('admin.production.reports.export', ['tab' => 'job_profitability']), ['format' => 'csv'])
            ->assertRedirect()
            ->assertSessionHas('export_id');

        Queue::assertPushed(ProcessCommercialReportExportJob::class);
    }

    public function test_export_requires_export_permission(): void
    {
        [$company, $branch, , $user] = $this->seedCostSheet(permissions: ['reports.costing.view']);

        $this->bindTenant($company, $branch);

        $this->actingAs($user)
            ->post(route('admin.production.reports.export'), ['format' => 'csv'])
            ->assertForbidden();
    }

    public function test_unauthorized_user_receives_403(): void
    {
        [$company, $branch, , $user] = $this->seedCostSheet(permissions: ['production.view']);

        $this->bindTenant($company, $branch);

        $this->actingAs($user)
            ->get(route('admin.production.reports.index'))
            ->assertForbidden();
    }

    protected function bindTenant(Company $company, Branch $branch): void
    {
        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);
        app()->instance(TenantContext::class, new TenantContext($company, $branch, false));
    }

    /**
     * @return array{0: Company, 1: Branch, 2: Customer, 3: User, 4: ProductionJobCard}
     */
    protected function seedCostSheet(?array $permissions = null, float $materialCost = 1500): array
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $customer = Customer::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'company_name' => 'Costing Report Customer',
            'status' => CustomerStatus::Active,
        ]);

        $permissions ??= ['reports.costing.view'];
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

        $productionCost = 500 + 300 + 100 + 200;
        $totalCost = $materialCost + $productionCost;

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
            'total_cost' => $totalCost,
            'revenue' => 10000,
            'gross_profit' => 10000 - $totalCost,
            'gross_margin_percent' => round(((10000 - $totalCost) / 10000) * 100, 2),
            'calculated_at' => now(),
        ]);

        return [$company, $branch, $customer, $user, $jobCard];
    }
}
