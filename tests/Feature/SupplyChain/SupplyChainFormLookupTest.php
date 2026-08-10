<?php

namespace Tests\Feature\SupplyChain;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use Database\Seeders\InventoryFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SupplyChainFormLookupTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_inventory_item_form_has_category_brand_and_uom_plus_buttons(): void
    {
        [$company, $branch, $user] = $this->storeContext();

        $this->actingAs($user)
            ->get(route('admin.inventory.items.create'))
            ->assertOk()
            ->assertSee('erp-lookup-select__add', false)
            ->assertSee('erpLookupCreate', false);
    }

    public function test_inventory_item_modal_store_validation_returns_field_messages(): void
    {
        [$company, $branch, $user] = $this->storeContext();
        $workspaceUrl = route('admin.workspaces.supply-chain.section', ['section' => 'catalogue']);

        $this->actingAs($user)
            ->post(route('admin.inventory.items.store'), [
                '_erp_modal' => '1',
                '_erp_modal_return' => $workspaceUrl,
                'stock_role' => 'raw_material',
            ])
            ->assertStatus(422)
            ->assertSee('data-erp-form-modal-panel', false)
            ->assertSee('data-erp-validation-message', false);
    }

    public function test_price_list_create_renders_modal_panel(): void
    {
        [$company, $branch, $user] = $this->storeContext();

        $this->actingAs($user)
            ->withHeader('Turbo-Frame', 'erp-form-modal')
            ->get(route('admin.inventory.catalogue.price-lists.create'))
            ->assertOk()
            ->assertSee('data-erp-form-modal-panel', false)
            ->assertSee(__('New price list'), false);
    }

    public function test_price_list_index_links_open_create_in_modal(): void
    {
        [$company, $branch, $user] = $this->storeContext();

        $this->actingAs($user)
            ->get(route('admin.inventory.catalogue.price-lists.index'))
            ->assertOk()
            ->assertSee('data-erp-modal-open', false)
            ->assertSee(route('admin.inventory.catalogue.price-lists.create'), false);
    }

    public function test_stock_receipt_create_has_warehouse_plus_button(): void
    {
        [$company, $branch, $user] = $this->storeContext(['inventory.receive']);

        $this->actingAs($user)
            ->get(route('admin.inventory.receipts.create'))
            ->assertOk()
            ->assertSee('erp-lookup-select__add', false)
            ->assertSee('erpLookupCreate', false);
    }

    /**
     * @param  list<string>  $extraPermissions
     * @return array{0: Company, 1: Branch, 2: User}
     */
    protected function storeContext(array $extraPermissions = []): array
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $this->seed(InventoryFoundationSeeder::class);

        $permissions = array_values(array_unique([
            'catalogue.view',
            'catalogue.create',
            'inventory.view',
            'inventory.create',
            ...$extraPermissions,
        ]));

        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $role = Role::findByName('Storekeeper', 'web');
        $role->syncPermissions($permissions);
        $user->assignRole('Storekeeper');

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        return [$company, $branch, $user];
    }
}
