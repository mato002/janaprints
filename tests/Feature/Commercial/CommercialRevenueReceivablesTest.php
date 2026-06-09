<?php

namespace Tests\Feature\Commercial;

use App\Enums\CustomerInvoiceStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use App\Support\Commercial\CommercialRevenueReceivablesPresenter;
use App\Support\Commercial\SalesOrderPaymentVisibility;
use App\Support\TenantContext;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CommercialRevenueReceivablesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_sales_order_payment_visibility_states(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $customer = Customer::factory()->create(['company_id' => $company->id, 'branch_id' => $branch->id]);
        $user = User::factory()->create(['company_id' => $company->id]);

        $order = SalesOrder::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'status' => SalesOrderStatus::Confirmed,
            'total_amount' => 10000,
        ]);

        $this->assertSame('uninvoiced', SalesOrderPaymentVisibility::resolve($order)['status']);

        CustomerInvoice::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'sales_order_id' => $order->id,
            'invoice_number' => 'INV-UNPAID-001',
            'invoice_type' => 'standard',
            'invoice_date' => now()->toDateString(),
            'status' => CustomerInvoiceStatus::Posted,
            'subtotal' => 10000,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => 10000,
            'amount_paid' => 0,
            'balance_due' => 10000,
            'created_by' => $user->id,
        ]);

        $order->load('invoices');
        $this->assertSame('unpaid', SalesOrderPaymentVisibility::resolve($order)['status']);

        $order->invoices()->first()->update([
            'amount_paid' => 4000,
            'balance_due' => 6000,
        ]);
        $order->load('invoices');
        $this->assertSame('partially_paid', SalesOrderPaymentVisibility::resolve($order)['status']);

        $order->invoices()->first()->update([
            'amount_paid' => 10000,
            'balance_due' => 0,
        ]);
        $order->load('invoices');
        $this->assertSame('paid', SalesOrderPaymentVisibility::resolve($order)['status']);
    }

    public function test_revenue_receivables_presenter_builds_sections(): void
    {
        [$company, $branch, $user] = $this->tenantUser([
            'invoices.view', 'sales_orders.view', 'payments.view',
        ]);

        $customer = Customer::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
        ]);

        $order = SalesOrder::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'status' => SalesOrderStatus::Confirmed,
            'total_amount' => 25000,
        ]);

        CustomerInvoice::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'sales_order_id' => $order->id,
            'invoice_number' => 'INV-REV-001',
            'invoice_type' => 'standard',
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->subDays(45)->toDateString(),
            'status' => CustomerInvoiceStatus::Posted,
            'subtotal' => 25000,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => 25000,
            'amount_paid' => 0,
            'balance_due' => 25000,
            'created_by' => $user->id,
        ]);

        $this->bindTenant($company, $branch);
        $this->actingAs($user);

        $payload = app(CommercialRevenueReceivablesPresenter::class)->build();

        $this->assertNotNull($payload);
        $this->assertCount(4, $payload['revenue_strip']);
        $this->assertCount(4, $payload['invoice_health']);
        $this->assertCount(4, $payload['receivable_aging']);
        $this->assertNotEmpty($payload['top_debtors']);
        $this->assertSame(1, $payload['payment_visibility']['summary'][2]['count']);
    }

    public function test_commercial_hub_renders_revenue_receivables_center(): void
    {
        [$company, $branch, $user] = $this->tenantUser([
            'invoices.view', 'sales_orders.view', 'quotations.view',
        ]);

        $this->bindTenant($company, $branch);

        $response = $this->actingAs($user)->get(route('admin.workspaces.commercial'));

        $response->assertOk();
        $response->assertSee(__('Revenue & Receivables Center'));
        $response->assertSee(__('Revenue Today'), false);
        $response->assertSee(__('Invoices Outstanding'), false);
        $response->assertSee(__('Receivable Aging'), false);
        $response->assertSee(__('Top Debtors'), false);
        $response->assertSee(__('Sales Order Payment Status'), false);
    }

    protected function bindTenant(Company $company, Branch $branch): void
    {
        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);
        app()->instance(TenantContext::class, new TenantContext($company, $branch, false));
    }

    /**
     * @param  list<string>  $permissions
     * @return array{0: Company, 1: Branch, 2: User}
     */
    protected function tenantUser(array $permissions, ?Company $company = null, ?Branch $branch = null): array
    {
        $company ??= Company::factory()->create();
        $branch ??= Branch::factory()->create(['company_id' => $company->id]);

        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $role = Role::findByName('Sales', 'web');
        $role->syncPermissions($permissions);
        $user->assignRole('Sales');

        return [$company, $branch, $user];
    }
}
