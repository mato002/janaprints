<?php

namespace Tests\Feature\Inventory;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class InventoryControlTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_inventory_control_section_shows_active_routes(): void
    {
        $user = $this->companyAdmin();

        $response = $this->actingAs($user)->get(
            route('admin.workspaces.supply-chain.section', ['section' => 'inventory-control'])
        );

        $response->assertOk();
        $response->assertSee(route('admin.inventory.stock-counts.index'), false);
        $response->assertSee(route('admin.inventory.cycle-counts.index'), false);
        $response->assertSee(route('admin.inventory.variances.index'), false);
        $response->assertSee(route('admin.inventory.reconciliations.index'), false);
        $response->assertDontSee(__('Coming Soon'), false);
    }

    public function test_inventory_control_routes_registered(): void
    {
        $this->assertTrue(Route::has('admin.inventory.stock-counts.index'));
        $this->assertTrue(Route::has('admin.inventory.cycle-counts.index'));
        $this->assertTrue(Route::has('admin.inventory.variances.index'));
        $this->assertTrue(Route::has('admin.inventory.reconciliations.index'));
    }

    public function test_variance_view_no_mutation_on_get(): void
    {
        $user = $this->companyAdmin();
        session(['active_company_id' => $user->company_id, 'active_branch_id' => $user->default_branch_id]);

        $this->actingAs($user)
            ->get(route('admin.inventory.variances.index'))
            ->assertOk();
    }

    protected function companyAdmin(): User
    {
        $company = Company::query()->first() ?? Company::factory()->create();
        $branch = Branch::query()->where('company_id', $company->id)->first()
            ?? Branch::factory()->create(['company_id' => $company->id]);

        return User::query()->where('company_id', $company->id)->first()
            ?? User::factory()->create([
                'company_id' => $company->id,
                'default_branch_id' => $branch->id,
                'email_verified_at' => now(),
                'is_active' => true,
            ])->assignRole('Company Admin');
    }
}
