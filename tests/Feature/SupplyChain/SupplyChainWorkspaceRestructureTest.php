<?php

namespace Tests\Feature\SupplyChain;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Support\Navigation\SupplyChainWorkspacePresenter;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SupplyChainWorkspaceRestructureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_supply_chain_hub_shows_seven_workspace_cards_only(): void
    {
        $user = $this->companyAdmin();

        $response = $this->actingAs($user)->get(route('admin.workspaces.supply-chain'));

        $response->assertOk();
        $response->assertSee(__('Catalogue'), false);
        $response->assertSee(__('Store Operations'), false);
        $response->assertSee(__('Procurement'), false);
        $response->assertSee(__('Inventory Control'), false);
        $response->assertSee(__('Costing'), false);
        $response->assertSee(__('Assets'), false);
        $response->assertSee(__('Reports'), false);
        $response->assertDontSee(__('Catalogue Dashboard'), false);
        $response->assertDontSee(__('Store Dashboard'), false);
    }

    public function test_catalogue_section_lists_catalogue_features(): void
    {
        $user = $this->companyAdmin();

        $response = $this->actingAs($user)->get(route('admin.workspaces.supply-chain.section', ['section' => 'catalogue']));

        $response->assertOk();
        $response->assertSee(route('admin.inventory.items.index'), false);
        $response->assertSee(route('admin.inventory.catalogue.categories.index'), false);
        $response->assertSee(route('admin.inventory.catalogue.price-lists.index'), false);
    }

    public function test_store_operations_section_lists_store_features(): void
    {
        $user = $this->companyAdmin();

        $response = $this->actingAs($user)->get(route('admin.workspaces.supply-chain.section', ['section' => 'store-operations']));

        $response->assertOk();
        $response->assertSee(route('admin.inventory.warehouses.index'), false);
        $response->assertSee(route('admin.inventory.receipts.index'), false);
        $response->assertSee(route('admin.inventory.issues.index'), false);
        $response->assertSee(route('admin.inventory.transfers.index'), false);
        $response->assertSee(route('admin.inventory.movements.index'), false);
    }

    public function test_existing_inventory_and_procurement_routes_remain_registered(): void
    {
        $this->assertTrue(Route::has('admin.inventory.items.index'));
        $this->assertTrue(Route::has('admin.procurement.vendors.index'));
        $this->assertTrue(Route::has('admin.inventory.valuation.index'));
    }

    public function test_supply_chain_active_routes_include_child_modules(): void
    {
        $patterns = app(SupplyChainWorkspacePresenter::class)->collectActiveRoutes();

        $this->assertContains('admin.workspaces.supply-chain', $patterns);
        $this->assertContains('admin.inventory.items.*', $patterns);
        $this->assertContains('admin.procurement.*', $patterns);
    }

    protected function companyAdmin(): User
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);
        $user->assignRole('Company Admin');

        return $user;
    }
}
