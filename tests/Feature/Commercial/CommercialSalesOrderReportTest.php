<?php

namespace Tests\Feature\Commercial;

use App\Enums\SalesOrderStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Sales\SalesOrder;
use App\Jobs\Commercial\ProcessCommercialReportExportJob;
use App\Models\CommercialReportExport;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CommercialSalesOrderReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_sales_order_reports_requires_permission(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['crm.customers.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('commercial.reports.sales_orders.index'))
            ->assertForbidden();
    }

    public function test_sales_order_reports_index_loads(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['commercial.reports.sales_orders.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('commercial.reports.sales_orders.index'))
            ->assertOk()
            ->assertSee(__('Sales Order Reports'), false)
            ->assertSee(__('Order Dashboard'), false)
            ->assertSee(__('Sales Order Summary'), false);
    }

    public function test_sales_order_reports_show_kpis_for_orders(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['commercial.reports.sales_orders.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        SalesOrder::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'status' => SalesOrderStatus::Confirmed,
            'order_date' => now()->toDateString(),
            'total_amount' => 45000,
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->get(route('commercial.reports.sales_orders.index'))
            ->assertOk()
            ->assertSee(__('Total Orders'), false)
            ->assertSee('45,000', false);
    }

    public function test_filters_persist_in_query_string(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['commercial.reports.sales_orders.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('commercial.reports.sales_orders.index', [
                'from_date' => '2026-01-01',
                'to_date' => '2026-01-31',
                'branch_id' => $branch->id,
                'tab' => 'open',
                'search' => 'SO-200',
                'quotation_source' => 'from_quotation',
            ]))
            ->assertOk()
            ->assertSee('value="2026-01-01"', false)
            ->assertSee('value="2026-01-31"', false)
            ->assertSee('SO-200', false)
            ->assertSee(__('Open Orders'), false);
    }

    public function test_export_requires_permission(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['commercial.reports.sales_orders.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->post(route('commercial.reports.sales_orders.export', ['tab' => 'summary']), ['format' => 'csv'])
            ->assertForbidden();
    }

    public function test_export_queues_job(): void
    {
        Queue::fake();

        [$company, $branch, $user] = $this->tenantUser([
            'commercial.reports.sales_orders.view',
            'commercial.reports.export',
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->post(route('commercial.reports.sales_orders.export', ['tab' => 'summary']), ['format' => 'csv'])
            ->assertRedirect()
            ->assertSessionHas('export_id');

        Queue::assertPushed(ProcessCommercialReportExportJob::class);
        $this->assertNotNull(CommercialReportExport::query()->find(session('export_id')));
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
