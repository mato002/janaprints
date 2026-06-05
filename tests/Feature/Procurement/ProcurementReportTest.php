<?php

namespace Tests\Feature\Procurement;

use App\Enums\PurchaseOrderStatus;
use App\Jobs\Commercial\ProcessCommercialReportExportJob;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\Vendor;
use App\Models\User;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProcurementReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_procurement_reports_requires_permission(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['procurement.orders.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.procurement.reports.index'))
            ->assertForbidden();
    }

    public function test_procurement_reports_index_loads(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['reports.procurement.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.procurement.reports.index'))
            ->assertOk()
            ->assertSee(__('Procurement Reports'), false)
            ->assertSee(__('Data Readiness'), false)
            ->assertSee(__('Procurement Dashboard'), false)
            ->assertSee(__('Purchase Summary'), false)
            ->assertSee(__('Supplier Performance'), false);
    }

    public function test_procurement_reports_show_kpis_for_orders(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['reports.procurement.view']);
        $vendor = Vendor::factory()->create(['company_id' => $company->id]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        PurchaseOrder::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'vendor_id' => $vendor->id,
            'po_number' => 'PO-RPT-001',
            'order_date' => now()->toDateString(),
            'status' => PurchaseOrderStatus::Sent,
            'subtotal' => 50000,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => 50000,
            'prepared_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->get(route('admin.procurement.reports.index'))
            ->assertOk()
            ->assertSee(__('Total Purchase Spend'), false)
            ->assertSee('50,000', false);
    }

    public function test_filters_persist_in_query_string(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['reports.procurement.view']);
        $vendor = Vendor::factory()->create(['company_id' => $company->id]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.procurement.reports.index', [
                'from_date' => '2026-01-01',
                'to_date' => '2026-01-31',
                'branch_id' => $branch->id,
                'supplier_id' => $vendor->id,
                'tab' => 'supplier_performance',
                'search' => 'PO-100',
            ]))
            ->assertOk()
            ->assertSee('value="2026-01-01"', false)
            ->assertSee('value="2026-01-31"', false)
            ->assertSee('PO-100', false)
            ->assertSee(__('Supplier Performance'), false);
    }

    public function test_export_requires_permission(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['reports.procurement.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->post(route('admin.procurement.reports.export', ['format' => 'csv']))
            ->assertForbidden();
    }

    public function test_export_queues_job(): void
    {
        Queue::fake();

        [$company, $branch, $user] = $this->tenantUser(['reports.procurement.view', 'reports.procurement.export']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->post(route('admin.procurement.reports.export', [
                'format' => 'csv',
                'tab' => 'summary',
            ]))
            ->assertRedirect(route('admin.procurement.reports.index'));

        $this->assertDatabaseHas('report_exports', [
            'company_id' => $company->id,
            'module' => 'procurement',
            'tab' => 'summary',
            'format' => 'csv',
        ]);

        Queue::assertPushed(ProcessCommercialReportExportJob::class);
    }

    public function test_supply_chain_workspace_links_procurement_reports(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);
        $user->assignRole('Company Admin');

        $response = $this->actingAs($user)->get(route('admin.workspaces.supply-chain.section', ['section' => 'reports']));

        $response->assertOk();
        $response->assertSee(route('admin.procurement.reports.index'), false);
        $response->assertSee(__('Procurement Reports'), false);
    }

    /**
     * @param  list<string>  $permissions
     * @return array{0: Company, 1: Branch, 2: User}
     */
    protected function tenantUser(array $permissions): array
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);

        $role = Role::create(['name' => 'procurement-report-tester-'.uniqid(), 'guard_name' => 'web']);
        $role->givePermissionTo($permissions);
        $user->assignRole($role);

        return [$company, $branch, $user];
    }
}
