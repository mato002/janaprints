<?php

namespace Tests\Feature\Sales;

use App\Enums\CustomerInvoiceStatus;
use App\Enums\CustomerPaymentMethod;
use App\Enums\CustomerPaymentStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\CustomerPayment;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use App\Support\Sales\CustomerInvoiceService;
use App\Support\Sales\CustomerPaymentService;
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

class CustomerPaymentFoundationTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected Branch $branch;

    protected User $user;

    protected CustomerInvoice $invoice;

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

        $order = SalesOrder::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'status' => SalesOrderStatus::Confirmed,
            'subtotal' => 1000,
            'tax_amount' => 160,
            'total_amount' => 1160,
            'created_by' => $this->user->id,
        ]);
        $order->items()->create([
            'item_name' => 'Print run',
            'quantity' => 1,
            'unit_price' => 1000,
            'line_total' => 1000,
            'sort_order' => 1,
        ]);

        $invoiceService = app(CustomerInvoiceService::class);
        $this->invoice = $invoiceService->createFromSalesOrder($order, $this->user->id);
        $invoiceService->approve($this->invoice, $this->user->id);
        $invoiceService->post($this->invoice->fresh(), $this->user->id);
        $this->invoice = $this->invoice->fresh();

        app()->instance(TenantContext::class, new TenantContext($this->company, $this->branch, false));
    }

    public function test_one_payment_many_invoices(): void
    {
        $order2 = SalesOrder::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $this->invoice->customer_id,
            'status' => SalesOrderStatus::Confirmed,
            'subtotal' => 500,
            'tax_amount' => 80,
            'total_amount' => 580,
            'created_by' => $this->user->id,
        ]);
        $order2->items()->create([
            'item_name' => 'Second job',
            'quantity' => 1,
            'unit_price' => 500,
            'line_total' => 500,
            'sort_order' => 1,
        ]);
        $inv2 = app(CustomerInvoiceService::class)->createFromSalesOrder($order2, $this->user->id);
        app(CustomerInvoiceService::class)->approve($inv2, $this->user->id);
        app(CustomerInvoiceService::class)->post($inv2, $this->user->id);

        $payment = app(CustomerPaymentService::class)->create($this->invoice->customer, $this->user->id, [
            'payment_date' => now()->toDateString(),
            'payment_method' => CustomerPaymentMethod::Bank,
            'amount' => $this->invoice->total_amount + $inv2->total_amount,
            'allocations' => [
                ['customer_invoice_id' => $this->invoice->id, 'amount' => $this->invoice->total_amount],
                ['customer_invoice_id' => $inv2->id, 'amount' => $inv2->total_amount],
            ],
        ]);

        app(CustomerPaymentService::class)->post($payment, $this->user->id);

        $this->assertEqualsWithDelta(0, (float) $this->invoice->fresh()->balance_due, 0.01);
        $this->assertEqualsWithDelta(0, (float) $inv2->fresh()->balance_due, 0.01);
        $this->assertSame(CustomerPaymentStatus::Posted, $payment->fresh()->status);
    }

    public function test_many_payments_one_invoice(): void
    {
        $half = round((float) $this->invoice->total_amount / 2, 2);
        $rest = round((float) $this->invoice->total_amount - $half, 2);

        foreach ([$half, $rest] as $amount) {
            $payment = app(CustomerPaymentService::class)->create($this->invoice->customer, $this->user->id, [
                'payment_date' => now()->toDateString(),
                'payment_method' => CustomerPaymentMethod::Cash,
                'amount' => $amount,
                'allocations' => [
                    ['customer_invoice_id' => $this->invoice->id, 'amount' => $amount],
                ],
            ]);
            app(CustomerPaymentService::class)->post($payment, $this->user->id);
        }

        $this->assertEqualsWithDelta(0, (float) $this->invoice->fresh()->balance_due, 0.01);
        $this->assertSame(2, CustomerPayment::query()->where('status', CustomerPaymentStatus::Posted)->count());
    }

    public function test_customer_deposit_posts_unallocated_to_deposits(): void
    {
        $payment = app(CustomerPaymentService::class)->create($this->invoice->customer, $this->user->id, [
            'payment_date' => now()->toDateString(),
            'payment_method' => CustomerPaymentMethod::Mpesa,
            'amount' => 300,
            'is_deposit' => true,
            'allocations' => [],
        ]);

        $posted = app(CustomerPaymentService::class)->post($payment, $this->user->id);

        $this->assertNotNull($posted->posted_journal_id);
        $this->assertEqualsWithDelta(300, (float) $posted->unallocated_amount, 0.01);
    }

    public function test_aging_report_lists_outstanding_balance(): void
    {
        $report = app(\App\Support\Sales\CustomerAgingService::class)->build([
            'company_id' => $this->company->id,
        ]);

        $this->assertGreaterThan(0, $report['grand_total']);
    }
}
