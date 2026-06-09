<?php

namespace Tests\Feature\Sales;

use App\Enums\CustomerPaymentMethod;
use App\Enums\CustomerPaymentStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use App\Support\Sales\CustomerCreditWalletService;
use App\Support\Sales\CustomerDepositService;
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

class CustomerDepositFoundationTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected Branch $branch;

    protected User $user;

    protected Customer $customer;

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

        app()->instance(TenantContext::class, new TenantContext($this->company, $this->branch, false));
    }

    protected function postDeposit(float $amount): \App\Models\Sales\CustomerPayment
    {
        $payment = app(CustomerPaymentService::class)->create($this->customer, $this->user->id, [
            'payment_date' => now()->toDateString(),
            'payment_method' => CustomerPaymentMethod::Cash,
            'amount' => $amount,
            'is_deposit' => true,
            'allocations' => [],
        ]);

        return app(CustomerPaymentService::class)->post($payment, $this->user->id);
    }

    public function test_create_deposit_establishes_credit_wallet(): void
    {
        $deposit = $this->postDeposit(500);

        $this->assertSame(CustomerPaymentStatus::Posted, $deposit->status);
        $this->assertEqualsWithDelta(500, (float) $deposit->credit_issued, 0.01);
        $this->assertEqualsWithDelta(500, (float) $deposit->credit_remaining, 0.01);
        $this->assertEqualsWithDelta(0, (float) $deposit->credit_applied, 0.01);

        $wallet = app(CustomerCreditWalletService::class)->summary($this->customer->id);
        $this->assertEqualsWithDelta(500, $wallet['available_credit'], 0.01);
        $this->assertEqualsWithDelta(0, $wallet['used_credit'], 0.01);
        $this->assertEqualsWithDelta(500, $wallet['remaining_credit'], 0.01);
    }

    public function test_apply_deposit_reduces_invoice_balance(): void
    {
        $deposit = $this->postDeposit(500);

        app(CustomerDepositService::class)->applyToInvoice(
            $deposit,
            $this->invoice,
            500,
            $this->user->id,
        );

        $deposit = $deposit->fresh();
        $invoice = $this->invoice->fresh();

        $this->assertEqualsWithDelta(0, (float) $deposit->credit_remaining, 0.01);
        $this->assertEqualsWithDelta(500, (float) $deposit->credit_applied, 0.01);
        $this->assertEqualsWithDelta(500, (float) $invoice->amount_paid, 0.01);
        $this->assertEqualsWithDelta(660, (float) $invoice->balance_due, 0.01);
    }

    public function test_partial_deposit_application(): void
    {
        $deposit = $this->postDeposit(300);

        app(CustomerDepositService::class)->applyToInvoice(
            $deposit,
            $this->invoice,
            200,
            $this->user->id,
        );

        $deposit = $deposit->fresh();
        $invoice = $this->invoice->fresh();
        $wallet = app(CustomerCreditWalletService::class)->summary($this->customer->id);

        $this->assertEqualsWithDelta(100, (float) $deposit->credit_remaining, 0.01);
        $this->assertEqualsWithDelta(200, (float) $deposit->credit_applied, 0.01);
        $this->assertEqualsWithDelta(200, (float) $invoice->amount_paid, 0.01);
        $this->assertEqualsWithDelta(100, $wallet['remaining_credit'], 0.01);
        $this->assertEqualsWithDelta(200, $wallet['used_credit'], 0.01);
    }

    public function test_full_deposit_use_clears_remaining_credit(): void
    {
        $deposit = $this->postDeposit(400);

        app(CustomerDepositService::class)->applyToInvoice($deposit, $this->invoice, 400, $this->user->id);

        $wallet = app(CustomerCreditWalletService::class)->summary($this->customer->id);

        $this->assertEqualsWithDelta(0, $wallet['remaining_credit'], 0.01);
        $this->assertEqualsWithDelta(400, $wallet['used_credit'], 0.01);
    }

    public function test_refund_deposit_reduces_remaining_credit(): void
    {
        $deposit = $this->postDeposit(500);

        app(CustomerDepositService::class)->refund($deposit, 200, $this->user->id);

        $deposit = $deposit->fresh();
        $wallet = app(CustomerCreditWalletService::class)->summary($this->customer->id);

        $this->assertEqualsWithDelta(200, (float) $deposit->credit_refunded, 0.01);
        $this->assertEqualsWithDelta(300, (float) $deposit->credit_remaining, 0.01);
        $this->assertEqualsWithDelta(300, $wallet['remaining_credit'], 0.01);
        $this->assertNotNull($deposit->depositRefunds()->first()?->posted_journal_id);
    }

    public function test_statement_reflects_deposit_application(): void
    {
        $deposit = $this->postDeposit(300);
        app(CustomerDepositService::class)->applyToInvoice($deposit, $this->invoice, 300, $this->user->id);

        $from = now()->toDateString();
        $statement = app(CustomerStatementService::class)->build([
            'customer_id' => $this->customer->id,
            'from_date' => $from,
            'to_date' => $from,
        ]);

        $applicationEntry = $statement['entries']->firstWhere('type', 'deposit_application');
        $this->assertNotNull($applicationEntry);
        $this->assertEqualsWithDelta(300, $applicationEntry->credit, 0.01);

        $expectedClosing = round((float) $this->invoice->total_amount - 300 - 300, 2);
        $this->assertEqualsWithDelta($expectedClosing, $statement['closing_balance'], 0.01);
        $this->assertEqualsWithDelta($expectedClosing, app(CustomerLedgerService::class)->closingBalance($this->customer->id), 0.01);
    }
}
