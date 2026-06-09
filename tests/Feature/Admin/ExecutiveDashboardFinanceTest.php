<?php

namespace Tests\Feature\Admin;

use App\Models\Accounting\AccountingPeriod;
use App\Models\Accounting\GlAccount;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Procurement\SupplierBill;
use App\Models\Procurement\Vendor;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\CustomerPayment;
use App\Models\User;
use App\Support\Accounting\Dashboard\AccountingLedgerMetricsService;
use App\Support\Accounting\JournalPostingService;
use App\Support\Dashboard\ExecutiveDashboardPresenter;
use App\Support\Dashboard\ExecutiveFinanceIntelligenceService;
use App\Support\TenantContext;
use Database\Seeders\GlAccountTypeSeeder;
use Database\Seeders\JanaPrintsAccountingPeriodsSeeder;
use Database\Seeders\JanaPrintsChartOfAccountsSeeder;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExecutiveDashboardFinanceTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected Branch $branch;

    protected User $ceo;

    protected AccountingPeriod $period;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        $this->seed(GlAccountTypeSeeder::class);
        $this->seed(JanaPrintsChartOfAccountsSeeder::class);
        $this->seed(JanaPrintsAccountingPeriodsSeeder::class);

        $this->company = Company::query()->where('code', 'JANA')->firstOrFail();
        $this->branch = Branch::query()->where('company_id', $this->company->id)->where('code', 'HQ')->firstOrFail();
        $this->ceo = User::factory()->create([
            'company_id' => $this->company->id,
            'default_branch_id' => $this->branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $this->ceo->assignRole('Company Admin');

        $this->period = AccountingPeriod::query()
            ->where('company_id', $this->company->id)
            ->where('is_current', true)
            ->firstOrFail();

        app()->instance(TenantContext::class, new TenantContext($this->company, $this->branch, false));
        session(['active_company_id' => $this->company->id, 'active_branch_id' => $this->branch->id]);

        $this->postSampleLedgerActivity();
        $this->seedOperationalFinanceData();
    }

    public function test_dashboard_finance_kpis_surface_live_values(): void
    {
        $this->actingAs($this->ceo);

        $finance = app(ExecutiveFinanceIntelligenceService::class)->build();

        $this->assertTrue($finance['available']);
        $this->assertSame('ledger', $finance['source']);
        $this->assertNotSame('—', $finance['receivables']);
        $this->assertNotSame('—', $finance['payables']);
        $this->assertNotSame('—', $finance['cash_position']);
        $this->assertNotSame('—', $finance['revenue_mtd']);
        $this->assertNotSame('—', $finance['expenses_mtd']);
        $this->assertNotSame('—', $finance['profit_mtd']);
        $this->assertNotSame('—', $finance['collections_mtd']);
        $this->assertNotSame('—', $finance['gross_margin']);
        $this->assertGreaterThan(0, $finance['receivables_raw']);
        $this->assertGreaterThan(0, $finance['collections_mtd_raw']);
    }

    public function test_presenter_kpi_strip_and_attention_use_finance_payload(): void
    {
        $this->actingAs($this->ceo);

        $payload = app(ExecutiveDashboardPresenter::class)->build();

        $receivables = collect($payload['kpi_strip'])->firstWhere('key', 'receivables');
        $payables = collect($payload['kpi_strip'])->firstWhere('key', 'payables');
        $invoices = collect($payload['attention'])->firstWhere('key', 'invoices');

        $this->assertNotSame('—', $receivables['value']);
        $this->assertSame('admin.receivables.aging', $receivables['route']);
        $this->assertNotSame('—', $payables['value']);
        $this->assertSame('admin.payables.aging', $payables['route']);
        $this->assertNotSame('—', $invoices['display']);
        $this->assertSame($payload['finance']['receivables'], $invoices['display']);
        $this->assertNotSame('—', $payload['today_ops']['collections_display']);
    }

    public function test_finance_links_visible_for_authorized_ceo(): void
    {
        $this->actingAs($this->ceo)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee(__('Financial 360'))
            ->assertSee(__('Accounting Dashboard'))
            ->assertSee(__('Receivables Intelligence'))
            ->assertSee(__('Payables Intelligence'))
            ->assertSee(route('admin.reports.financial360'), false)
            ->assertSee(route('admin.accounting.dashboard'), false)
            ->assertSee(route('admin.receivables.aging'), false)
            ->assertSee(route('admin.payables.aging'), false);
    }

    public function test_finance_links_respect_permission_visibility(): void
    {
        $viewer = User::factory()->create([
            'company_id' => $this->company->id,
            'default_branch_id' => $this->branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $viewer->assignRole('Viewer');

        $this->actingAs($viewer);

        $finance = app(ExecutiveFinanceIntelligenceService::class)->build();

        $this->assertTrue($finance['available']);
        $this->assertSame('—', $finance['receivables']);
        $this->assertSame('—', $finance['payables']);
        $this->assertSame('—', $finance['cash_position']);
        $this->assertSame(['admin.reports.financial360'], array_column($finance['links'], 'route'));

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee(route('admin.reports.financial360'), false)
            ->assertDontSee(route('admin.accounting.dashboard'), false)
            ->assertDontSee(route('admin.receivables.aging'), false)
            ->assertDontSee(route('admin.payables.aging'), false);
    }

    public function test_cross_module_consistency_with_accounting_dashboard(): void
    {
        $this->actingAs($this->ceo);

        $ledger = app(AccountingLedgerMetricsService::class)->build([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'period_id' => $this->period->id,
        ]);

        $finance = app(ExecutiveFinanceIntelligenceService::class)->build();

        $this->assertEquals($ledger['cards']['cash_position'], $finance['cash_position_raw']);
        $this->assertEquals($ledger['cards']['revenue_mtd'], $finance['revenue_raw']);
        $this->assertEquals($ledger['cards']['net_profit_mtd'], $finance['profit_raw']);
        $this->assertEquals($ledger['cards']['gross_margin_mtd'], $finance['gross_margin_raw']);
        $this->assertEquals(
            app(\App\Support\Reports\IntelligenceAggregateQueries::class)->sumReceivables(
                new \App\Support\Reports\IntelligenceScope(
                    companyId: $this->company->id,
                    branchId: $this->branch->id,
                    fromDate: now()->startOfMonth()->toDateString(),
                    toDate: now()->toDateString(),
                ),
            ),
            $finance['receivables_raw'],
        );
    }

    protected function postSampleLedgerActivity(): void
    {
        $service = app(JournalPostingService::class);
        $cash = GlAccount::query()->where('company_id', $this->company->id)->where('code', '1110')->firstOrFail();
        $revenue = GlAccount::query()->where('company_id', $this->company->id)->where('code', '4110')->firstOrFail();

        $journal = $service->createDraft([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'accounting_period_id' => $this->period->id,
            'journal_date' => $this->period->start_date->toDateString(),
            'description' => 'Executive dashboard finance seed',
        ], [
            ['gl_account_id' => $cash->id, 'debit' => 50000, 'credit' => 0],
            ['gl_account_id' => $revenue->id, 'debit' => 0, 'credit' => 50000],
        ], $this->ceo->id);

        $service->post($journal, $this->ceo->id);
    }

    protected function seedOperationalFinanceData(): void
    {
        $customer = Customer::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
        ]);

        CustomerInvoice::query()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-EXEC-001',
            'invoice_type' => 'standard',
            'invoice_date' => now()->toDateString(),
            'status' => \App\Enums\CustomerInvoiceStatus::Posted,
            'subtotal' => 12000,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => 12000,
            'amount_paid' => 0,
            'balance_due' => 12000,
            'created_by' => $this->ceo->id,
        ]);

        CustomerPayment::query()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $customer->id,
            'payment_number' => 'RCP-EXEC-001',
            'payment_date' => now()->toDateString(),
            'payment_method' => \App\Enums\CustomerPaymentMethod::Bank,
            'amount' => 4500,
            'allocated_amount' => 0,
            'unallocated_amount' => 4500,
            'status' => \App\Enums\CustomerPaymentStatus::Posted,
            'created_by' => $this->ceo->id,
            'posted_by' => $this->ceo->id,
            'posted_at' => now(),
        ]);

        $vendor = Vendor::factory()->create([
            'company_id' => $this->company->id,
        ]);

        SupplierBill::query()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'vendor_id' => $vendor->id,
            'bill_number' => 'BILL-EXEC-001',
            'bill_date' => now()->toDateString(),
            'status' => \App\Enums\SupplierBillStatus::Posted,
            'subtotal' => 8000,
            'tax_amount' => 0,
            'total_amount' => 8000,
            'amount_paid' => 0,
            'balance_due' => 8000,
            'created_by' => $this->ceo->id,
        ]);
    }
}
