<?php

namespace Tests\Feature\Reports;

use App\Enums\VendorStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Procurement\Vendor;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class KpiCenterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_kpi_route_loads(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['kpi.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.reports.kpi'))
            ->assertOk()
            ->assertSee(__('KPI Center'), false)
            ->assertSee('commercial', false);
    }

    public function test_kpi_permission_gate(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['crm.customers.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)->get(route('admin.reports.kpi'))->assertForbidden();
    }

    public function test_date_and_branch_filters(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['kpi.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.reports.kpi', ['branch_id' => $branch->id, 'kpi_category' => 'commercial']))
            ->assertOk()
            ->assertSee(__('Commercial KPIs'), false);
    }

    public function test_missing_tables_show_pending_source(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['kpi.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.reports.kpi'))
            ->assertOk()
            ->assertSee(__('Pending source'), false);
    }

    public function test_kpi_renders_procurement_vendor_count(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['kpi.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        Vendor::factory()->create([
            'company_id' => $company->id,
            'status' => VendorStatus::Active,
        ]);

        $this->actingAs($user)
            ->get(route('admin.reports.kpi'))
            ->assertOk()
            ->assertSee(__('Vendor count'), false)
            ->assertSee('1', false);
    }

    public function test_get_does_not_mutate_data(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['kpi.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->assertDatabaseCount('quotations', 0);

        $this->actingAs($user)->get(route('admin.reports.kpi'))->assertOk();

        $this->assertDatabaseCount('quotations', 0);
    }

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
