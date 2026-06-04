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

class Procurement360Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_procurement_360_loads(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['reports.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.reports.procurement360'))
            ->assertOk()
            ->assertSee(__('Procurement 360'), false);
    }

    public function test_procurement_360_with_vendors(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['reports.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        Vendor::factory()->create([
            'company_id' => $company->id,
            'status' => VendorStatus::Active,
        ]);

        $this->actingAs($user)
            ->get(route('admin.reports.procurement360'))
            ->assertOk()
            ->assertSee(__('Vendor Performance'), false)
            ->assertSee(__('Active Vendors'), false);
    }

    public function test_rfq_placeholder_when_no_rfqs(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['reports.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $response = $this->actingAs($user)->get(route('admin.reports.procurement360'));

        $response->assertOk();
        if (! \Illuminate\Support\Facades\Schema::hasTable('rfqs')) {
            $response->assertSee(__('Module not ready'), false);
        }
    }

    public function test_vendor_filter_does_not_crash(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['reports.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.reports.procurement360', ['vendor_id' => '']))
            ->assertOk();
    }

    public function test_no_mutation_on_get(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['reports.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->assertDatabaseCount('purchase_requests', 0);
        $this->actingAs($user)->get(route('admin.reports.procurement360'))->assertOk();
        $this->assertDatabaseCount('purchase_requests', 0);
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
