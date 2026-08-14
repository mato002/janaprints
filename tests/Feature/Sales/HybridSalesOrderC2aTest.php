<?php

namespace Tests\Feature\Sales;

use App\Enums\CustomerArtworkStatus;
use App\Enums\CustomerArtworkType;
use App\Enums\CustomerPrintSpecificationStatus;
use App\Enums\CustomerStatus;
use App\Enums\DomainCommunicationEvent;
use App\Enums\InventoryStockRole;
use App\Enums\SalesOrderBillingType;
use App\Enums\SalesOrderStatus;
use App\Events\Communications\DomainCommunicationEventRaised;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Crm\CustomerArtwork;
use App\Models\Crm\CustomerPrintSpecification;
use App\Models\Inventory\InventoryItem;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class HybridSalesOrderC2aTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_create_form_shows_hybrid_entry_tabs(): void
    {
        [$company, $branch, , $user] = $this->salesContext(['sales_orders.view', 'sales_orders.create', 'crm.customers.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->withHeader('Turbo-Frame', 'erp-form-modal')
            ->get(route('admin.sales-orders.create'))
            ->assertOk()
            ->assertSee(__('From Quotation'), false)
            ->assertSee(__('Direct Order'), false);
    }

    public function test_direct_order_creates_sales_order_without_quotation(): void
    {
        Event::fake([DomainCommunicationEventRaised::class]);

        [$company, $branch, $customer, $user, $item, $spec] = $this->directContext(withSpecification: true);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->post(route('admin.sales-orders.store'), [
                'entry_mode' => 'direct',
                'production_destination' => 'digital',
                'customer_id' => $customer->id,
                'customer_print_specification_id' => $spec->id,
                'quantity' => 250,
                'unit_price' => 12.5,
                'required_date' => now()->addWeek()->toDateString(),
                'notes' => 'Rush repeat flyers',
            ])
            ->assertRedirect();

        $order = SalesOrder::query()->where('customer_id', $customer->id)->latest('id')->first();

        $this->assertNotNull($order);
        $this->assertTrue($order->is_direct_order);
        $this->assertNull($order->quotation_id);
        $this->assertEquals(SalesOrderStatus::Confirmed, $order->status);
        $this->assertEquals(250 * 12.5, (float) $order->subtotal);

        Event::assertDispatched(DomainCommunicationEventRaised::class, function (DomainCommunicationEventRaised $event) {
            return $event->event === DomainCommunicationEvent::SalesOrderCreated;
        });
    }

    public function test_repeat_direct_order_clones_source_without_quotation(): void
    {
        Event::fake([DomainCommunicationEventRaised::class]);

        [$company, $branch, $customer, $user, $item, $spec, $source] = $this->directContext(withSource: true, withSpecification: true);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->post(route('admin.sales-orders.store'), [
                'entry_mode' => 'direct',
                'production_destination' => 'digital',
                'customer_id' => $customer->id,
                'customer_print_specification_id' => $spec->id,
                'repeat_source_sales_order_id' => $source->id,
                'quantity' => 1000,
                'required_date' => now()->addDays(5)->toDateString(),
                'notes' => 'Repeat with higher qty',
            ])
            ->assertRedirect();

        $repeat = SalesOrder::query()
            ->where('repeat_source_sales_order_id', $source->id)
            ->first();

        $this->assertNotNull($repeat);
        $this->assertTrue($repeat->is_direct_order);
        $this->assertNull($repeat->quotation_id);
        $this->assertEquals(1000, (float) $repeat->items->first()->quantity);
        $this->assertEquals('Repeat with higher qty', $repeat->notes);
    }

    public function test_direct_order_applies_customer_billing_defaults(): void
    {
        [$company, $branch, $customer, $user, $item, $spec] = $this->directContext([
            'payment_terms' => '50% deposit on order',
        ], withSpecification: true);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->post(route('admin.sales-orders.store'), [
                'entry_mode' => 'direct',
                'production_destination' => 'digital',
                'customer_id' => $customer->id,
                'customer_print_specification_id' => $spec->id,
                'quantity' => 10,
                'unit_price' => 100,
            ])
            ->assertRedirect();

        $order = SalesOrder::query()->where('customer_id', $customer->id)->latest('id')->firstOrFail();

        $this->assertEquals(SalesOrderBillingType::Deposit50, $order->billing_type);
        $this->assertGreaterThan(0, (float) $order->required_deposit_amount);
    }

    public function test_order_context_endpoint_returns_customer_history(): void
    {
        [$company, $branch, $customer, $user, , , $source] = $this->directContext(withSource: true, withSpecification: true);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->getJson(route('admin.sales-orders.customer-order-context', $customer))
            ->assertOk()
            ->assertJsonPath('previous_orders.0.id', $source->id)
            ->assertJsonStructure([
                'previous_orders',
                'previous_jobs',
                'artwork_library',
                'print_specifications',
                'frequent_products',
                'serial_profiles',
                'billing_defaults',
            ]);
    }

    /**
     * @return array{0: Company, 1: Branch, 2: Customer, 3: User, 4: InventoryItem, 5?: CustomerPrintSpecification|SalesOrder, 6?: SalesOrder}
     */
    protected function directContext(array $customerOverrides = [], bool $withSource = false, bool $withSpecification = false): array
    {
        [$company, $branch, $customer, $user] = $this->salesContext([
            'sales_orders.view', 'sales_orders.create', 'crm.customers.view',
        ]);

        if ($customerOverrides !== []) {
            $customer->update($customerOverrides);
        }

        $item = InventoryItem::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'item_name' => 'A4 Flyers',
            'stock_role' => InventoryStockRole::FinishedGood,
            'is_active' => true,
        ]);

        $spec = null;
        if ($withSpecification) {
            $spec = CustomerPrintSpecification::query()->create([
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'customer_id' => $customer->id,
                'inventory_item_id' => $item->id,
                'specification_code' => 'CPS-C2A-01',
                'name' => 'A4 Flyers Spec',
                'status' => CustomerPrintSpecificationStatus::Active,
                'created_by' => $user->id,
            ]);

            CustomerArtwork::query()->create([
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'customer_id' => $customer->id,
                'customer_print_specification_id' => $spec->id,
                'artwork_name' => 'A4 Flyers Spec',
                'artwork_type' => CustomerArtworkType::Layout,
                'version_number' => 1,
                'is_active_version' => true,
                'file_path' => 'customer-artworks/test.png',
                'file_name' => 'test.png',
                'original_file_name' => 'test.png',
                'status' => CustomerArtworkStatus::Active,
                'uploaded_by' => $user->id,
                'uploaded_at' => now(),
            ]);
        }

        $source = null;

        if ($withSource) {
            $source = SalesOrder::factory()->create([
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'customer_id' => $customer->id,
                'customer_print_specification_id' => $spec?->id,
                'quotation_id' => null,
                'artwork_request_id' => null,
                'inventory_item_id' => $item->id,
                'status' => SalesOrderStatus::Confirmed,
                'is_direct_order' => true,
                'subtotal' => 500,
                'total_amount' => 500,
                'created_by' => $user->id,
            ]);

            $source->items()->create([
                'item_name' => 'A4 Flyers',
                'quantity' => 500,
                'unit_price' => 1,
                'line_total' => 500,
                'sort_order' => 1,
            ]);
        }

        if ($withSource) {
            return [$company, $branch, $customer, $user, $item, $spec, $source];
        }

        if ($withSpecification) {
            return [$company, $branch, $customer, $user, $item, $spec];
        }

        return [$company, $branch, $customer, $user, $item, $source];
    }

    /**
     * @return array{0: Company, 1: Branch, 2: Customer, 3: User}
     */
    protected function salesContext(?array $permissions = null): array
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $customer = Customer::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_code' => 'CUST-C2A-01',
            'company_name' => 'C2A Customer',
            'status' => CustomerStatus::Active,
        ]);
        $permissions ??= ['sales_orders.view', 'sales_orders.create'];
        $user = $this->salesUser($company, $branch, $permissions);

        return [$company, $branch, $customer, $user];
    }

    protected function salesUser(Company $company, Branch $branch, array $permissions): User
    {
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $role = Role::findByName('Sales', 'web');
        $role->syncPermissions($permissions);
        $user->assignRole('Sales');

        return $user;
    }
}
