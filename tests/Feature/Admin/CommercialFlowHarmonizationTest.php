<?php

namespace Tests\Feature\Admin;

use App\Enums\CustomerPrintSpecificationStatus;
use App\Enums\CustomerStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Crm\CustomerPrintSpecification;
use App\Models\Inventory\InventoryItem;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class CommercialFlowHarmonizationTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected Branch $branch;

    protected User $user;

    protected Customer $customer;

    protected InventoryItem $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->company = Company::factory()->create();
        $this->branch = Branch::factory()->create(['company_id' => $this->company->id]);
        $this->user = User::factory()->create([
            'company_id' => $this->company->id,
            'default_branch_id' => $this->branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $this->user->assignRole('Company Admin');

        $this->customer = Customer::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'status' => CustomerStatus::Active,
        ]);

        $this->product = InventoryItem::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
        ]);

        session(['active_company_id' => $this->company->id, 'active_branch_id' => $this->branch->id]);
    }

    public function test_customer_360_shows_harmonized_commercial_actions(): void
    {
        $this->actingAs($this->user)
            ->get(route('admin.crm.customers.show', ['customer' => $this->customer, 'tab' => 'commercial']))
            ->assertOk()
            ->assertSee(__('Create Quotation'), false)
            ->assertSee(__('Create Direct Order'), false)
            ->assertSee(__('Create from Quotation'), false)
            ->assertSee(__('Receive Payment'), false)
            ->assertDontSee(__('Create sales order'), false);
    }

    public function test_direct_order_entry_opens_direct_tab(): void
    {
        $this->actingAs($this->user)
            ->withHeader('Turbo-Frame', 'erp-form-modal')
            ->get(route('admin.sales-orders.create', [
                'customer_id' => $this->customer->id,
                'tab' => 'direct',
            ]))
            ->assertOk()
            ->assertSee(__('Direct Order'), false)
            ->assertSee('erp-form-modal__actions--sticky', false);
    }

    public function test_quotation_entry_opens_quotation_tab_even_with_customer(): void
    {
        $this->actingAs($this->user)
            ->withHeader('Turbo-Frame', 'erp-form-modal')
            ->get(route('admin.sales-orders.create', [
                'customer_id' => $this->customer->id,
                'tab' => 'quotation',
            ]))
            ->assertOk()
            ->assertSee(__('From Quotation'), false)
            ->assertSee(__('Create from quotation'), false);
    }

    public function test_existing_customer_without_tab_defaults_to_direct(): void
    {
        $this->actingAs($this->user)
            ->withHeader('Turbo-Frame', 'erp-form-modal')
            ->get(route('admin.sales-orders.create', [
                'customer_id' => $this->customer->id,
            ]))
            ->assertOk()
            ->assertSee(__('Direct Order'), false);
    }

    public function test_repeat_order_route_is_available_from_customer_360(): void
    {
        $order = SalesOrder::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'status' => SalesOrderStatus::Confirmed,
            'is_direct_order' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('admin.crm.customers.repeat-order', [$this->customer, $order]));

        $newOrder = SalesOrder::query()->where('repeat_source_sales_order_id', $order->id)->first();
        $this->assertNotNull($newOrder);
        $response->assertRedirect(route('admin.sales-orders.show', $newOrder));
    }

    public function test_print_specification_direct_order_link_uses_direct_tab(): void
    {
        $spec = CustomerPrintSpecification::query()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'inventory_item_id' => $this->product->id,
            'specification_code' => 'CPS-FLOW-01',
            'name' => 'Flow Spec',
            'status' => CustomerPrintSpecificationStatus::Active,
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);

        $this->actingAs($this->user)
            ->get(route('admin.crm.customers.show', [
                'customer' => $this->customer,
                'tab' => 'print-specifications',
            ]))
            ->assertOk()
            ->assertSee('tab=direct', false)
            ->assertSee('print_specification_id='.$spec->id, false);
    }

    public function test_canonical_order_context_route_exists(): void
    {
        $this->assertTrue(Route::has('admin.sales-orders.customer-order-context'));
        $this->assertFalse(Route::has('admin.crm.customers.order-context'));
    }

    public function test_tenant_isolation_on_customer_360(): void
    {
        $otherCompany = Company::factory()->create();
        $otherCustomer = Customer::factory()->create([
            'company_id' => $otherCompany->id,
            'branch_id' => Branch::factory()->create(['company_id' => $otherCompany->id])->id,
        ]);

        $this->actingAs($this->user)
            ->get(route('admin.crm.customers.show', $otherCustomer))
            ->assertForbidden();
    }
}
