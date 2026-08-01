<?php

namespace Tests\Feature\Commercial;

use App\Enums\CustomerStatus;
use App\Enums\ProductionJobCardStatus;
use App\Enums\ProductionType;
use App\Enums\SalesOrderStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Production\JobCostSheet;
use App\Models\Production\ProductionJobCard;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use App\Support\Commercial\Intelligence\CommercialBranchProfitabilityService;
use App\Support\Commercial\Intelligence\CommercialCustomerProfitabilityService;
use App\Support\Commercial\Intelligence\CommercialExecutiveDashboardService;
use App\Support\Commercial\Intelligence\CommercialJobProfitabilityService;
use App\Support\Commercial\Intelligence\CommercialOutsourceProfitabilityService;
use App\Support\Commercial\Intelligence\CommercialWasteIntelligenceService;
use App\Support\Reports\IntelligenceScope;
use App\Support\TenantContext;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Commercial\Concerns\GetsEmbeddedWorkspaceReports;
use Tests\TestCase;

class CommercialIntelligenceC9Test extends TestCase
{
    use GetsEmbeddedWorkspaceReports;
    use RefreshDatabase;

    protected Company $company;

    protected Branch $branch;

    protected User $user;

    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);

        $this->company = Company::query()->where('code', 'JANA')->firstOrFail();
        $this->branch = Branch::query()->where('company_id', $this->company->id)->firstOrFail();
        $this->user = User::factory()->create([
            'company_id' => $this->company->id,
            'default_branch_id' => $this->branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $this->user->assignRole('Company Admin');
        $this->user->givePermissionTo(['reports.view', 'intelligence.commercial.view']);

        $this->customer = Customer::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'status' => CustomerStatus::Active,
        ]);

        app()->instance(TenantContext::class, new TenantContext($this->company, $this->branch, false));
        session(['active_company_id' => $this->company->id, 'active_branch_id' => $this->branch->id]);
    }

    public function test_job_profitability_snapshot(): void
    {
        $sheet = $this->seedCostSheet(12000, 7000, 500, 800);

        $snapshot = app(CommercialJobProfitabilityService::class)->snapshot($sheet->jobCard);

        $this->assertEquals(12000.0, $snapshot['revenue']);
        $this->assertEquals(7000.0, $snapshot['material_cost']);
        $this->assertEquals(500.0, $snapshot['wastage_cost']);
        $this->assertEquals(800.0, $snapshot['outsource_cost']);
        $this->assertGreaterThan(0, $snapshot['estimated_profit']);
        $this->assertGreaterThan(0, $snapshot['estimated_margin_percent']);
    }

    public function test_customer_profitability_profile(): void
    {
        $this->seedCostSheet(8000, 5000);

        $profile = app(CommercialCustomerProfitabilityService::class)->profile($this->customer);

        $this->assertArrayHasKey('total_orders', $profile);
        $this->assertArrayHasKey('estimated_profit', $profile);
        $this->assertArrayHasKey('estimated_margin_percent', $profile);
    }

    public function test_branch_profitability_aggregate(): void
    {
        $this->seedCostSheet(10000, 6000);

        $scope = $this->scope();
        $rows = app(CommercialBranchProfitabilityService::class)->aggregate($scope);

        $this->assertNotEmpty($rows);
        $this->assertEquals($this->branch->id, $rows[0]['branch_id']);
    }

    public function test_waste_intelligence_summary(): void
    {
        $scope = $this->scope();
        $summary = app(CommercialWasteIntelligenceService::class)->summary($scope);

        $this->assertArrayHasKey('waste_cost', $summary);
        $this->assertArrayHasKey('top_reasons', $summary);
        $this->assertArrayHasKey('by_branch', $summary);
    }

    public function test_outsource_profitability_by_job(): void
    {
        $sheet = $this->seedCostSheet(15000, 4000, 0, 6000);
        $sheet->jobCard->update([
            'outsource_vendor_id' => null,
            'outsource_actual_cost' => 6000,
            'status' => ProductionJobCardStatus::Outsourced,
            'outsourced_at' => now(),
        ]);

        $rows = app(CommercialOutsourceProfitabilityService::class)->byJob($this->scope());

        $this->assertIsArray($rows);
    }

    public function test_executive_dashboard_widgets(): void
    {
        $this->seedCostSheet(9000, 4500);

        $widgets = app(CommercialExecutiveDashboardService::class)->widgets($this->scope());

        $this->assertNotEmpty($widgets['kpis']);
        $this->assertArrayHasKey('top_customers', $widgets);
        $this->assertArrayHasKey('waste', $widgets);
    }

    public function test_commercial_intelligence_report_route(): void
    {
        $this->getEmbeddedReport($this->user, 'admin.reports.commercial-intelligence')
            ->assertOk()
            ->assertSee(__('Commercial Intelligence'));
    }

    public function test_tenant_isolation_on_job_profitability(): void
    {
        $this->seedCostSheet(5000, 3000);

        $companyB = Company::factory()->create();
        $branchB = Branch::factory()->create(['company_id' => $companyB->id]);

        $scopeB = new IntelligenceScope(
            companyId: $companyB->id,
            branchId: $branchB->id,
            fromDate: now()->startOfMonth()->toDateString(),
            toDate: now()->toDateString(),
        );

        $paginator = app(CommercialJobProfitabilityService::class)->paginate($scopeB);
        $this->assertSame(0, $paginator->total());
    }

    public function test_branch_isolation_on_branch_profitability(): void
    {
        $this->seedCostSheet(12000, 7000);

        $otherBranch = Branch::factory()->create(['company_id' => $this->company->id]);
        $scope = new IntelligenceScope(
            companyId: $this->company->id,
            branchId: $otherBranch->id,
            fromDate: now()->startOfMonth()->toDateString(),
            toDate: now()->toDateString(),
        );

        $rows = app(CommercialBranchProfitabilityService::class)->aggregate($scope);
        $this->assertEmpty($rows);
    }

    protected function scope(): IntelligenceScope
    {
        return new IntelligenceScope(
            companyId: $this->company->id,
            branchId: $this->branch->id,
            fromDate: now()->startOfMonth()->toDateString(),
            toDate: now()->toDateString(),
        );
    }

    protected function seedCostSheet(
        float $revenue,
        float $materialCost,
        float $wastageCost = 0,
        float $outsourceCost = 0,
    ): JobCostSheet {
        $order = SalesOrder::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'status' => SalesOrderStatus::Confirmed,
            'total_amount' => $revenue,
        ]);

        $jobCard = ProductionJobCard::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'sales_order_id' => $order->id,
            'production_type' => ProductionType::Digital,
            'status' => ProductionJobCardStatus::Completed,
        ]);

        $totalCost = $materialCost + $wastageCost + $outsourceCost;
        $profit = $revenue - $totalCost;

        return JobCostSheet::query()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'production_job_card_id' => $jobCard->id,
            'material_cost' => $materialCost,
            'wastage_cost' => $wastageCost,
            'outsourced_cost' => $outsourceCost,
            'total_cost' => $totalCost,
            'revenue' => $revenue,
            'gross_profit' => $profit,
            'gross_margin_percent' => $revenue > 0 ? round(($profit / $revenue) * 100, 2) : 0,
            'calculated_at' => now(),
            'status' => 'calculated',
        ]);
    }
}
