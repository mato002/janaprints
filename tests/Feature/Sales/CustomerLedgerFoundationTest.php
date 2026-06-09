<?php

namespace Tests\Feature\Sales;

use App\Enums\CustomerInvoiceType;
use App\Enums\CustomerPaymentMethod;
use App\Enums\SalesOrderStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use App\Support\Sales\CustomerInvoiceService;
use App\Support\Sales\CustomerLedgerService;
use App\Support\Sales\CustomerPaymentService;
use App\Support\Sales\CustomerStatementService;
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

class CustomerLedgerFoundationTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected Branch $branch;

    protected User $user;

    protected Customer $customer;

    protected CustomerInvoice $invoice;

    protected CustomerLedgerService $ledger;

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
        ]);

        $order = SalesOrder::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'status' => SalesOrderStatus::Confirmed,
            'subtotal' => 1000,
            'tax_amount' => 160,
            'total_amount' => 1160,
            'created_by' => $this->user->id,
        ]);
        $order->items()->create([
            'item_name' => 'Business cards',
            'quantity' => 1000,
            'unit_price' => 1,
            'line_total' => 1000,
            'sort_order' => 1,
        ]);

        $invoiceService = app(CustomerInvoiceService::class);
        $this->invoice = $invoiceService->createFromSalesOrder($order, $this->user->id);
        $invoiceService->approve($this->invoice, $this->user->id);
        $invoiceService->post($this->invoice->fresh(), $this->user->id);
        $this->invoice = $this->invoice->fresh();

        $this->ledger = app(CustomerLedgerService::class);

        app()->instance(TenantContext::class, new TenantContext($this->company, $this->branch, false));
    }

    public function test_invoice_posts_as_debit_entry(): void
    {
        $ledger = $this->ledger->build(['customer_id' => $this->customer->id]);

        $invoiceEntry = $ledger['entries']->firstWhere('type', 'invoice');

        $this->assertNotNull($invoiceEntry);
        $this->assertEqualsWithDelta((float) $this->invoice->total_amount, $invoiceEntry->debit, 0.01);
        $this->assertEqualsWithDelta(0, $invoiceEntry->credit, 0.01);
        $this->assertEqualsWithDelta((float) $this->invoice->total_amount, $ledger['closing_balance'], 0.01);
        $this->assertEqualsWithDelta((float) $this->invoice->total_amount, $ledger['total_charges'], 0.01);
    }

    public function test_payment_posts_as_credit_entry(): void
    {
        $amount = (float) $this->invoice->total_amount;

        $payment = app(CustomerPaymentService::class)->create($this->customer, $this->user->id, [
            'payment_date' => now()->toDateString(),
            'payment_method' => CustomerPaymentMethod::Cash,
            'amount' => $amount,
            'allocations' => [
                ['customer_invoice_id' => $this->invoice->id, 'amount' => $amount],
            ],
        ]);
        app(CustomerPaymentService::class)->post($payment, $this->user->id);

        $ledger = $this->ledger->build(['customer_id' => $this->customer->id]);
        $paymentEntry = $ledger['entries']->firstWhere('type', 'payment');

        $this->assertNotNull($paymentEntry);
        $this->assertEqualsWithDelta(0, $paymentEntry->debit, 0.01);
        $this->assertEqualsWithDelta($amount, $paymentEntry->credit, 0.01);
        $this->assertEqualsWithDelta(0, $ledger['closing_balance'], 0.01);
    }

    public function test_credit_note_posts_as_credit_entry(): void
    {
        $credit = app(CustomerInvoiceService::class)->createCreditNote($this->invoice, $this->user->id, []);
        app(CustomerInvoiceService::class)->approve($credit, $this->user->id);
        app(CustomerInvoiceService::class)->post($credit, $this->user->id);

        $ledger = $this->ledger->build(['customer_id' => $this->customer->id]);

        $invoiceEntry = $ledger['entries']->firstWhere('type', 'invoice');
        $creditEntry = $ledger['entries']->firstWhere('type', 'credit_note');

        $this->assertNotNull($invoiceEntry);
        $this->assertNotNull($creditEntry);
        $this->assertEqualsWithDelta(0, $creditEntry->debit, 0.01);
        $this->assertEqualsWithDelta((float) $credit->total_amount, $creditEntry->credit, 0.01);
        $this->assertEqualsWithDelta(0, $ledger['closing_balance'], 0.01);
        $this->assertSame(CustomerInvoiceType::CreditNote, $credit->invoice_type);
    }

    public function test_mixed_scenario_closing_balance(): void
    {
        $total = (float) $this->invoice->total_amount;
        $partial = round($total / 2, 2);

        $payment = app(CustomerPaymentService::class)->create($this->customer, $this->user->id, [
            'payment_date' => now()->toDateString(),
            'payment_method' => CustomerPaymentMethod::Bank,
            'amount' => $partial,
            'allocations' => [
                ['customer_invoice_id' => $this->invoice->id, 'amount' => $partial],
            ],
        ]);
        app(CustomerPaymentService::class)->post($payment, $this->user->id);

        $credit = app(CustomerInvoiceService::class)->createCreditNote($this->invoice, $this->user->id, []);
        app(CustomerInvoiceService::class)->approve($credit, $this->user->id);
        app(CustomerInvoiceService::class)->post($credit, $this->user->id);

        $expected = round($total - $partial - $total, 2);

        $this->assertEqualsWithDelta($expected, $this->ledger->closingBalance($this->customer->id), 0.01);
    }

    public function test_statement_opening_plus_period_equals_closing(): void
    {
        $total = (float) $this->invoice->total_amount;
        $partial = round($total / 2, 2);

        $payment = app(CustomerPaymentService::class)->create($this->customer, $this->user->id, [
            'payment_date' => now()->toDateString(),
            'payment_method' => CustomerPaymentMethod::Mpesa,
            'amount' => $partial,
            'allocations' => [
                ['customer_invoice_id' => $this->invoice->id, 'amount' => $partial],
            ],
        ]);
        app(CustomerPaymentService::class)->post($payment, $this->user->id);

        $from = now()->toDateString();
        $to = now()->toDateString();

        $statement = app(CustomerStatementService::class)->build([
            'customer_id' => $this->customer->id,
            'from_date' => $from,
            'to_date' => $to,
        ]);

        $periodMovement = $statement['total_charges'] - $statement['total_credits'];

        $this->assertEqualsWithDelta(
            $statement['opening_balance'] + $periodMovement,
            $statement['closing_balance'],
            0.01,
        );
        $this->assertEqualsWithDelta(round($total - $partial, 2), $statement['closing_balance'], 0.01);
    }

    public function test_closing_balance_matches_ledger_build(): void
    {
        $this->assertEqualsWithDelta(
            $this->ledger->build(['customer_id' => $this->customer->id])['closing_balance'],
            $this->ledger->closingBalance($this->customer->id),
            0.01,
        );
    }
}
