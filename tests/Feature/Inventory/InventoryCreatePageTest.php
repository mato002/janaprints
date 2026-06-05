<?php

namespace Tests\Feature\Inventory;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InventoryCreatePageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_supply_chain_quick_create_routes_exist(): void
    {
        foreach (config('workspaces.supply-chain.quick_create', []) as $item) {
            if (! empty($item['coming_soon'])) {
                continue;
            }

            $this->assertTrue(Route::has($item['route']), 'Missing quick create route: '.$item['route']);
        }
    }

    public function test_receipts_create_path_does_not_match_show_route(): void
    {
        $matched = app('router')->getRoutes()->match(
            \Illuminate\Http\Request::create('/admin/inventory/receipts/create', 'GET')
        );

        $this->assertSame('admin.inventory.receipts.create', $matched->getName());
    }

    public function test_issues_create_path_does_not_match_show_route(): void
    {
        $matched = app('router')->getRoutes()->match(
            \Illuminate\Http\Request::create('/admin/inventory/issues/create', 'GET')
        );

        $this->assertSame('admin.inventory.issues.create', $matched->getName());
    }

    public function test_stock_receipt_create_renders_form(): void
    {
        [$company, $branch, $user] = $this->storekeeper(['inventory.view', 'inventory.receive']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.inventory.receipts.create'))
            ->assertOk()
            ->assertSee(__('New stock receipt'), false)
            ->assertSee(__('Add line'), false)
            ->assertSee('name="warehouse_id"', false);
    }

    public function test_stock_issue_create_renders_form(): void
    {
        [$company, $branch, $user] = $this->storekeeper(['inventory.view', 'inventory.issue']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.inventory.issues.create'))
            ->assertOk()
            ->assertSee(__('New Stock Issue'), false)
            ->assertSee('name="destination"', false);
    }

    /**
     * @param  list<string>  $permissions
     * @return array{0: Company, 1: Branch, 2: User}
     */
    protected function storekeeper(array $permissions): array
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $role = Role::findByName('Storekeeper', 'web');
        $role->syncPermissions($permissions);
        $user->assignRole('Storekeeper');

        return [$company, $branch, $user];
    }
}
