<?php

namespace Tests\Feature\Sales;

use App\Enums\ArReconciliationCheckStatus;
use App\Enums\ArReconciliationExceptionType;
use App\Enums\CustomerInvoiceStatus;
use App\Enums\CustomerPaymentMethod;
use App\Enums\CustomerPaymentStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Accounting\AccountingPeriod;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use App\Support\Accounting\Close\FinancialIntegrityService;
use App\Support\Accounting\Close\PeriodCloseService;
use App\Support\Sales\AccountsReceivableReconciliationService;
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
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AccountsReceivableReconciliationTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected Branch $branch;

    protected User $user;

    protected Customer $customer;

    protected AccountingPeriod $period;

    protected AccountsReceivableReconciliationService $reconciliation;

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

        $this->period = AccountingPeriod::query()
            ->where('company_id', $this->company->id)
            ->where('is_current', true)
            ->firstOrFail();

        $this->reconciliation = app(AccountsReceivableReconciliationService::class);

        app()->instance(TenantContext::class, new TenantContext($this->company, $this->branch, false));
    }

    protected function postInvoice(?string $documentDate = null): CustomerInvoice
    {
        $documentDate ??= $this->period->end_date->toDateString();

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
            'item_name' => 'Print run',
            'quantity' => 1,
            'unit_price' => 1000,
            'line_total' => 1000,
            'sort_order' => 1,
        ]);

        $service = app(CustomerInvoiceService::class);
        $invoice = $service->createFromSalesOrder($order, $this->user->id, [
            'invoice_date' => $documentDate,
        ]);
        $service->approve($invoice, $this->user->id);
        $service->post($invoice->fresh(), $this->user->id);

        return $invoice->fresh();
    }

    public function test_perfect_match_when_invoice_and_payment_are_posted(): void
    {
        $invoice = $this->postInvoice();

        $payment = app(CustomerPaymentService::class)->create($this->customer, $this->user->id, [
            'payment_date' => $this->period->end_date->toDateString(),
            'payment_method' => CustomerPaymentMethod::Cash,
            'amount' => (float) $invoice->total_amount,
            'allocations' => [
                ['customer_invoice_id' => $invoice->id, 'amount' => (float) $invoice->total_amount],
            ],
        ]);
        app(CustomerPaymentService::class)->post($payment, $this->user->id);

        $report = $this->reconciliation->build([
            'company_id' => $this->company->id,
            'as_of_date' => $this->period->end_date->toDateString(),
        ]);

        $this->assertTrue($report['is_resolved']);
        foreach ($report['checks'] as $check) {
            $this->assertSame(ArReconciliationCheckStatus::Matched->value, $check['status']);
        }
    }

    public function test_variance_detection_when_operational_ar_differs_from_gl(): void
    {
        $invoice = $this->postInvoice();

        $invoice->update(['balance_due' => 500]);

        $report = $this->reconciliation->build([
            'company_id' => $this->company->id,
            'as_of_date' => $this->period->end_date->toDateString(),
        ]);

        $subledgerCheck = collect($report['checks'])->firstWhere('key', 'subledger_vs_gl');
        $this->assertSame(ArReconciliationCheckStatus::Variance->value, $subledgerCheck['status']);
        $this->assertFalse($report['is_resolved']);
    }

    public function test_exception_detection_for_missing_journal(): void
    {
        $invoice = $this->postInvoice();
        $invoice->update(['posted_journal_id' => null]);

        $report = $this->reconciliation->build([
            'company_id' => $this->company->id,
            'as_of_date' => $this->period->end_date->toDateString(),
        ]);

        $this->assertFalse($report['is_resolved']);
        $this->assertTrue(
            collect($report['exceptions'])->contains(
                fn (array $row) => $row['type'] === ArReconciliationExceptionType::MissingJournal->value
            ),
        );
    }

    public function test_unallocated_deposit_is_warning_not_blocking(): void
    {
        $this->postInvoice();

        $payment = app(CustomerPaymentService::class)->create($this->customer, $this->user->id, [
            'payment_date' => now()->toDateString(),
            'payment_method' => CustomerPaymentMethod::Cash,
            'amount' => 250,
            'is_deposit' => true,
            'allocations' => [],
        ]);
        app(CustomerPaymentService::class)->post($payment, $this->user->id);

        $report = $this->reconciliation->build([
            'company_id' => $this->company->id,
            'as_of_date' => $this->period->end_date->toDateString(),
        ]);

        $depositException = collect($report['exceptions'])->firstWhere(
            'type',
            ArReconciliationExceptionType::UnallocatedDeposit->value,
        );

        $this->assertNotNull($depositException);
        $this->assertSame('warning', $depositException['severity']);
    }

    public function test_period_close_blocked_when_reconciliation_unresolved(): void
    {
        $invoice = $this->postInvoice();
        $invoice->update(['posted_journal_id' => null]);

        $this->expectException(ValidationException::class);

        try {
            app(PeriodCloseService::class)->close($this->period, $this->user->id);
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('ar_reconciliation', $e->errors());
            throw $e;
        }
    }

    public function test_period_close_gate_passes_when_ar_is_clean(): void
    {
        $invoice = $this->postInvoice();

        $payment = app(CustomerPaymentService::class)->create($this->customer, $this->user->id, [
            'payment_date' => $this->period->end_date->toDateString(),
            'payment_method' => CustomerPaymentMethod::Bank,
            'amount' => (float) $invoice->total_amount,
            'allocations' => [
                ['customer_invoice_id' => $invoice->id, 'amount' => (float) $invoice->total_amount],
            ],
        ]);
        app(CustomerPaymentService::class)->post($payment, $this->user->id);

        $report = $this->reconciliation->buildForPeriod($this->period);

        foreach ($report['checks'] as $check) {
            $this->assertSame(
                ArReconciliationCheckStatus::Matched->value,
                $check['status'],
                $check['key'].': expected '.$check['expected'].' actual '.$check['actual'].' diff '.$check['difference'],
            );
        }

        $this->assertTrue($report['is_resolved']);

        $snapshot = app(FinancialIntegrityService::class)->validateForPeriodClose($this->period);
        $this->assertTrue($snapshot['ar_reconciliation']['is_resolved']);
    }
}
