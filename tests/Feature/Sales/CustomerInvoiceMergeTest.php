<?php

namespace Tests\Feature\Sales;

use App\Enums\CustomerInvoiceCollectionStatus;
use App\Enums\CustomerInvoiceStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use App\Support\TenantContext;
use Database\Seeders\GlAccountTypeSeeder;
use Database\Seeders\JanaPrintsAccountingPeriodsSeeder;
use Database\Seeders\JanaPrintsChartOfAccountsSeeder;
use Database\Seeders\JanaPrintsPostingEngineSeeder;
use Database\Seeders\JanaPrintsTaxSeeder;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerInvoiceMergeTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected Branch $branch;

    protected User $user;

    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        $this->seed(GlAccountTypeSeeder::class);
        $this->seed(JanaPrintsChartOfAccountsSeeder::class);
        $this->seed(JanaPrintsAccountingPeriodsSeeder::class);
        $this->seed(JanaPrintsPostingEngineSeeder::class);
        $this->seed(JanaPrintsTaxSeeder::class);

        $this->company = Company::query()->where('code', 'JANA')->firstOrFail();
        $this->branch = Branch::query()->where('company_id', $this->company->id)->firstOrFail();
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
            'company_name' => 'Madinaka Ltd',
        ]);

        app()->instance(TenantContext::class, new TenantContext($this->company, $this->branch, false));
        session(['active_company_id' => $this->company->id, 'active_branch_id' => $this->branch->id]);
    }

    public function test_can_merge_two_orders_into_one_invoice(): void
    {
        $first = $this->makeOrder('Business cards', 1000, 1);
        $second = $this->makeOrder('Letterheads', 500, 2);

        $this->actingAs($this->user)
            ->post(route('admin.invoices.store-from-sales-orders'), [
                'sales_order_ids' => [$first->getRouteKey(), $second->getRouteKey()],
                'invoice_date' => now()->toDateString(),
            ])
            ->assertRedirect(route('admin.invoices.index'));

        $invoice = CustomerInvoice::query()->latest('id')->firstOrFail();

        $this->assertSame($this->customer->id, $invoice->customer_id);
        $this->assertSame(CustomerInvoiceStatus::Approved, $invoice->status);
        $this->assertSame(CustomerInvoiceCollectionStatus::Pending, $invoice->collectionStatus());
        $this->assertGreaterThanOrEqual(2, $invoice->lines()->count());
        $this->assertTrue($invoice->salesOrders->pluck('id')->contains($first->id));
        $this->assertTrue($invoice->salesOrders->pluck('id')->contains($second->id));
        $this->assertEquals(0, $first->fresh()->remainingInvoiceTotal());
        $this->assertEquals(0, $second->fresh()->remainingInvoiceTotal());
        $this->assertStringContainsString($first->order_number, $invoice->lines->pluck('item_name')->implode(' '));
        $this->assertStringContainsString($second->order_number, $invoice->lines->pluck('item_name')->implode(' '));
    }

    public function test_cannot_merge_orders_from_different_customers(): void
    {
        $first = $this->makeOrder('Business cards', 1000, 1);
        $otherCustomer = Customer::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'company_name' => 'Other Client',
        ]);
        $second = $this->makeOrder('Letterheads', 500, 2, $otherCustomer);

        $this->actingAs($this->user)
            ->from(route('admin.invoices.index'))
            ->post(route('admin.invoices.store-from-sales-orders'), [
                'sales_order_ids' => [$first->getRouteKey(), $second->getRouteKey()],
            ])
            ->assertRedirect(route('admin.invoices.index', ['view' => 'jobs']))
            ->assertSessionHas('error');
    }

    public function test_customer_search_filters_unbilled_orders(): void
    {
        $this->makeOrder('Business cards', 1000, 1);

        $this->actingAs($this->user)
            ->withHeader('Turbo-Frame', 'module-workspace-content')
            ->get(route('admin.invoices.index', ['embedded' => '1', 'view' => 'jobs', 'customer' => 'Madinaka']))
            ->assertOk()
            ->assertSee('Madinaka Ltd', false)
            ->assertSee(__('Jobs'), false)
            ->assertSee(__('Generate invoice'), false);
    }

    protected function makeOrder(string $itemName, float $quantity, float $unitPrice, ?Customer $customer = null): SalesOrder
    {
        $order = SalesOrder::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'customer_id' => ($customer ?? $this->customer)->id,
            'quotation_id' => null,
            'artwork_request_id' => null,
            'status' => SalesOrderStatus::Confirmed,
            'subtotal' => $quantity * $unitPrice,
            'tax_amount' => 0,
            'total_amount' => $quantity * $unitPrice,
            'created_by' => $this->user->id,
        ]);

        $order->items()->create([
            'item_name' => $itemName,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'line_total' => $quantity * $unitPrice,
            'sort_order' => 1,
        ]);

        return $order->fresh(['items', 'customer']);
    }
}
