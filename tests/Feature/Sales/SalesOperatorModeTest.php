<?php

namespace Tests\Feature\Sales;

use App\Enums\CustomerArtworkStatus;
use App\Enums\CustomerArtworkType;
use App\Enums\CustomerPrintSpecificationStatus;
use App\Enums\CustomerStatus;
use App\Enums\InventoryStockRole;
use App\Enums\SalesOrderStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Crm\CustomerArtwork;
use App\Models\Crm\CustomerPrintSpecification;
use App\Models\Inventory\InventoryItem;
use App\Models\Production\ProductionJobCard;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use App\Support\Sales\SalesOperatorMode;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SalesOperatorModeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_sales_role_user_prefers_operator_mode_but_admins_do_not(): void
    {
        $sales = $this->userWithRole('Sales');
        $admin = $this->userWithRole('Company Admin');

        $this->assertTrue(SalesOperatorMode::enabledFor($sales));
        $this->assertTrue($sales->prefersSalesOperatorMode());
        $this->assertFalse(SalesOperatorMode::enabledFor($admin));
        $this->assertFalse($admin->prefersSalesOperatorMode());
    }

    public function test_sales_operator_login_lands_on_desk(): void
    {
        $sales = $this->userWithRole('Sales');

        $this->post(route('admin.login'), [
            'email' => $sales->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.sales.desk'));
    }

    public function test_company_admin_login_still_lands_on_dashboard(): void
    {
        $admin = $this->userWithRole('Company Admin');

        $this->post(route('admin.login'), [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard', absolute: false));
    }

    public function test_sales_desk_opens_create_forms_in_modals(): void
    {
        $sales = $this->userWithRole('Sales');

        $this->actingAs($sales)
            ->get(route('admin.sales.desk'))
            ->assertOk()
            ->assertSee(__('Sales desk'))
            ->assertSee(__('1. Find or create customer'))
            ->assertSee(__('Create customer'))
            ->assertSee('data-erp-modal-open', false)
            ->assertSee('/admin/crm/customers/create?from=sales-desk', false);

        $this->actingAs($sales)
            ->get(route('admin.crm.customers.create', ['from' => 'sales-desk']))
            ->assertOk()
            ->assertSee('name="from"', false)
            ->assertSee('value="sales-desk"', false);
    }

    public function test_operator_commercial_workspace_redirects_to_desk_unless_desk_flag(): void
    {
        $sales = $this->userWithRole('Sales');

        $this->actingAs($sales)
            ->get(route('admin.workspaces.commercial'))
            ->assertRedirect(route('admin.sales.desk'));

        $response = $this->actingAs($sales)
            ->get(route('admin.workspaces.commercial', ['desk' => 1]));

        $this->assertNotEquals(
            route('admin.sales.desk'),
            $response->headers->get('Location'),
            'desk=1 must not bounce Sales operators back to the walk-in desk',
        );

        $this->assertTrue(
            $response->isSuccessful() || $response->isRedirection(),
            'Full Commercial desk request should render or redirect into the commercial shell',
        );
    }

    public function test_existing_forms_return_to_desk_and_can_release(): void
    {
        Storage::fake('local');

        $sales = $this->userWithRole('Sales');
        $companyId = $sales->company_id;
        $branchId = $sales->default_branch_id;

        session(['active_company_id' => $companyId, 'active_branch_id' => $branchId]);

        $this->actingAs($sales)
            ->from(route('admin.sales.desk'))
            ->post(route('admin.crm.customers.store'), [
                'from' => 'sales-desk',
                'company_name' => 'Second Desk Client',
                'customer_type' => 'individual',
            ])
            ->assertRedirect(route('admin.sales.desk', [
                'customer' => Customer::query()->where('company_name', 'Second Desk Client')->firstOrFail()->getRouteKey(),
                'step' => 2,
            ]));

        $customer = Customer::query()->where('company_name', 'Second Desk Client')->firstOrFail();

        $product = InventoryItem::factory()->create([
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'item_name' => 'Walk-in Flyers',
            'stock_role' => InventoryStockRole::FinishedGood,
            'is_active' => true,
        ]);

        $this->actingAs($sales)
            ->post(route('admin.crm.customers.print-specifications.store', $customer), [
                'from' => 'sales-desk',
                'inventory_item_id' => $product->id,
                'name' => 'Walk-in Flyers Spec',
                'status' => CustomerPrintSpecificationStatus::Active->value,
                'default_unit_price' => 10,
            ])
            ->assertRedirect();

        $spec = CustomerPrintSpecification::query()->where('name', 'Walk-in Flyers Spec')->firstOrFail();

        CustomerArtwork::query()->create([
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'customer_id' => $customer->id,
            'customer_print_specification_id' => $spec->id,
            'artwork_name' => $spec->name,
            'artwork_type' => CustomerArtworkType::Layout,
            'version_number' => 1,
            'is_active_version' => true,
            'file_path' => 'customer-artworks/test/layout.png',
            'file_name' => 'layout.png',
            'original_file_name' => 'layout.png',
            'status' => CustomerArtworkStatus::Active,
            'uploaded_by' => $sales->id,
            'uploaded_at' => now(),
        ]);

        $this->actingAs($sales)
            ->post(route('admin.sales-orders.store'), [
                'from' => 'sales-desk',
                'entry_mode' => 'direct',
                'customer_id' => $customer->id,
                'customer_print_specification_id' => $spec->id,
                'quantity' => 100,
                'unit_price' => 10,
                'required_date' => now()->addDays(5)->toDateString(),
                'priority' => 'normal',
                'send_to_production' => '1',
            ])
            ->assertRedirect();

        $order = SalesOrder::query()->latest('id')->firstOrFail();
        $this->assertSame(SalesOrderStatus::ReadyForProduction, $order->status);
        $this->assertDatabaseHas('production_job_cards', [
            'sales_order_id' => $order->id,
        ]);
        $this->assertInstanceOf(ProductionJobCard::class, $order->jobCard);

        $this->actingAs($sales)
            ->get(route('admin.sales.desk', [
                'customer' => $customer->getRouteKey(),
                'order' => $order->getRouteKey(),
                'step' => 4,
            ]))
            ->assertOk()
            ->assertSee(__('5. Walk-in complete'))
            ->assertSee($order->order_number)
            ->assertSee('data-erp-modal-open', false)
            ->assertSee('from=sales-desk', false);

        $this->actingAs($sales)
            ->withHeaders(['Turbo-Frame' => 'erp-form-modal'])
            ->get(route('admin.sales-orders.show', [$order, 'from' => 'sales-desk']))
            ->assertOk()
            ->assertSee('data-erp-form-modal-panel', false)
            ->assertSee($order->order_number, false);
    }

    public function test_public_customer_hash_does_not_resolve_to_wrong_numeric_id(): void
    {
        $sales = $this->userWithRole('Sales');
        $companyId = $sales->company_id;
        $branchId = $sales->default_branch_id;
        session(['active_company_id' => $companyId, 'active_branch_id' => $branchId]);

        $wrong = Customer::factory()->create([
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'company_name' => 'Wrong Client Co',
            'status' => CustomerStatus::Active,
        ]);

        $right = Customer::factory()->create([
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'company_name' => 'Right Client Co',
            'status' => CustomerStatus::Active,
        ]);

        // Hash that MySQL would coerce to $wrong->id when compared as integer.
        $prefix = (string) $wrong->id;
        $hash = $prefix.str_repeat('A', 16 - strlen($prefix));
        \Illuminate\Support\Facades\DB::table('customers')->where('id', $right->id)->update(['public_id' => $hash]);

        $this->actingAs($sales)
            ->get(route('admin.sales.desk', [
                'customer' => $hash,
                'step' => 2,
            ]))
            ->assertOk()
            ->assertSee('Right Client Co')
            ->assertDontSee('Wrong Client Co');
    }

    public function test_sales_desk_shows_work_queue_and_unified_search(): void
    {
        $sales = $this->userWithRole('Sales');
        $companyId = $sales->company_id;
        $branchId = $sales->default_branch_id;
        session(['active_company_id' => $companyId, 'active_branch_id' => $branchId]);

        $customer = Customer::factory()->create([
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'company_name' => 'Queue Test Client',
            'status' => CustomerStatus::Active,
        ]);

        $this->actingAs($sales)
            ->get(route('admin.sales.desk'))
            ->assertOk()
            ->assertSee(__("Today's sales work"))
            ->assertSee(__('Quote requests'))
            ->assertSee(__('New quote'));

        $this->actingAs($sales)
            ->getJson(route('admin.sales.desk.customers.search', ['q' => 'Queue Test']))
            ->assertOk()
            ->assertJsonPath('results.0.kind', 'customer')
            ->assertJsonPath('results.0.label', 'Queue Test Client');

        $this->actingAs($sales)
            ->get(route('admin.sales.desk', [
                'customer' => $customer->getRouteKey(),
                'step' => 2,
            ]))
            ->assertOk()
            ->assertSee('Queue Test Client')
            ->assertSee(__('Contact'), false);
    }

    protected function userWithRole(string $role): User
    {
        $user = User::factory()->create([
            'company_id' => Company::query()->where('code', 'JANA')->value('id'),
            'default_branch_id' => Branch::query()->where('code', 'HQ')->value('id'),
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $user->assignRole(Role::findByName($role, 'web'));

        return $user;
    }
}
