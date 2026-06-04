<?php

namespace Tests\Feature\Reports;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class Inventory360Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_inventory_360_loads_on_empty_database(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['reports.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.reports.inventory360'))
            ->assertOk()
            ->assertSee(__('Inventory 360'), false);
    }

    public function test_permission_gate(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['crm.customers.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)->get(route('admin.reports.inventory360'))->assertForbidden();
    }

    public function test_branch_and_warehouse_filters(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['reports.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.reports.inventory360', ['branch_id' => $branch->id, 'warehouse_id' => '']))
            ->assertOk();
    }

    public function test_get_is_read_only(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['reports.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->assertDatabaseCount('inventory_movements', 0);
        $this->actingAs($user)->get(route('admin.reports.inventory360'))->assertOk();
        $this->assertDatabaseCount('inventory_movements', 0);
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
