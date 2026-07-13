<?php

namespace Tests\Feature\Accounting;

use App\Enums\BudgetStatus;
use App\Enums\JournalStatus;
use App\Models\Accounting\AccountingPeriod;
use App\Models\Accounting\BankAccount;
use App\Models\Accounting\Currency;
use App\Models\Accounting\GlAccount;
use App\Models\Accounting\JournalLine;
use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Support\Accounting\BankReconciliationService;
use App\Support\Accounting\BudgetService;
use App\Support\Accounting\ExchangeRateService;
use App\Support\Accounting\JournalPostingService;
use App\Support\Accounting\Reports\CashFlowStatementReportService;
use App\Support\TenantContext;
use Database\Seeders\GlAccountTypeSeeder;
use Database\Seeders\JanaPrintsAccountingPeriodsSeeder;
use Database\Seeders\JanaPrintsChartOfAccountsSeeder;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountingCapabilityModulesTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected Branch $branch;

    protected User $user;

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
        $this->branch = Branch::query()->where('company_id', $this->company->id)->firstOrFail();
        $this->user = User::factory()->create([
            'company_id' => $this->company->id,
            'default_branch_id' => $this->branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $this->user->assignRole('Company Admin');

        $this->period = AccountingPeriod::query()
            ->where('company_id', $this->company->id)
            ->where('is_current', true)
            ->firstOrFail();

        app()->instance(TenantContext::class, new TenantContext($this->company, $this->branch, false));
    }

    public function test_cash_flow_report_page_loads(): void
    {
        $this->postCashSampleJournal(1500);

        $this->actingAs($this->user)
            ->withHeaders(['Turbo-Frame' => 'module-workspace-content'])
            ->get(route('admin.accounting.reports.cash-flow', [
                'from_date' => $this->period->start_date->toDateString(),
                'to_date' => $this->period->end_date->toDateString(),
                'embedded' => 1,
            ]))
            ->assertOk()
            ->assertSee(__('Cash Flow Statement'))
            ->assertSee(__('Opening cash'));

        $report = app(CashFlowStatementReportService::class)->build([
            'from_date' => $this->period->start_date->toDateString(),
            'to_date' => $this->period->end_date->toDateString(),
        ]);

        $this->assertGreaterThanOrEqual(1500, $report['closing_cash']);
        $this->assertArrayHasKey('operating', $report['sections']);
    }

    public function test_bank_reconciliation_match_flow(): void
    {
        $bankGl = GlAccount::query()->where('company_id', $this->company->id)->where('code', '1220')->firstOrFail();
        $equity = GlAccount::query()->where('company_id', $this->company->id)->where('code', '3200')->firstOrFail();

        $journalService = app(JournalPostingService::class);
        $journal = $journalService->createDraft([
            'accounting_period_id' => $this->period->id,
            'journal_date' => $this->period->start_date->toDateString(),
            'description' => 'Bank deposit',
        ], [
            ['gl_account_id' => $bankGl->id, 'debit' => 1000, 'credit' => 0],
            ['gl_account_id' => $equity->id, 'debit' => 0, 'credit' => 1000],
        ], $this->user->id);
        $journalService->post($journal, $this->user->id);

        $bankLine = JournalLine::query()
            ->where('journal_id', $journal->id)
            ->where('gl_account_id', $bankGl->id)
            ->firstOrFail();

        $bankAccount = BankAccount::query()->create([
            'company_id' => $this->company->id,
            'gl_account_id' => $bankGl->id,
            'name' => 'KCB Operating',
            'currency_code' => 'KES',
            'is_active' => true,
        ]);

        $service = app(BankReconciliationService::class);
        $statement = $service->createStatement($this->company->id, $this->user->id, [
            'bank_account_id' => $bankAccount->id,
            'statement_date' => $this->period->start_date->toDateString(),
            'opening_balance' => 0,
            'closing_balance' => 1000,
            'lines' => [[
                'line_date' => $this->period->start_date->toDateString(),
                'description' => 'Customer deposit',
                'amount' => 1000,
            ]],
        ]);

        $line = $statement->lines->first();
        $this->assertNotNull($line);

        $suggestions = $service->suggestMatches($statement);
        $this->assertNotEmpty($suggestions);
        $this->assertSame($line->id, $suggestions[0]['statement_line_id']);
        $this->assertSame($bankLine->id, $suggestions[0]['journal_line_id']);

        $service->match($line, $bankLine);
        $line->refresh();
        $this->assertTrue($line->is_matched);

        $reconciled = $service->markReconciled($statement->fresh('lines'), $this->user->id);
        $this->assertSame(\App\Enums\BankStatementStatus::Reconciled, $reconciled->status);
        $this->assertNotNull($reconciled->reconciliation);
    }

    public function test_exchange_rate_convert(): void
    {
        $this->assertTrue(Currency::query()->where('code', 'KES')->exists());
        $this->assertTrue(Currency::query()->where('code', 'USD')->exists());

        $service = app(ExchangeRateService::class);
        $service->storeRate($this->company->id, [
            'currency_code' => 'USD',
            'rate_date' => now()->toDateString(),
            'rate_to_base' => 130.5,
            'source' => 'test',
        ]);

        $this->assertEqualsWithDelta(130.5, $service->getRate($this->company->id, 'USD', now()->toDateString()), 0.0001);
        $this->assertEqualsWithDelta(261.0, $service->convert($this->company->id, 2.0, 'USD', now()->toDateString()), 0.01);
        $this->assertEqualsWithDelta(1.0, $service->getRate($this->company->id, 'KES', now()->toDateString()), 0.0001);

        $this->assertDatabaseHas('exchange_rates', [
            'company_id' => $this->company->id,
            'currency_code' => 'USD',
        ]);
    }

    public function test_budget_vs_actual(): void
    {
        $expense = GlAccount::query()->where('company_id', $this->company->id)->where('code', '6100')->first()
            ?? GlAccount::query()->where('company_id', $this->company->id)->whereHas('accountType', fn ($q) => $q->where('code', 'expense'))->where('is_postable', true)->firstOrFail();

        $cash = GlAccount::query()->where('company_id', $this->company->id)->where('code', '1110')->firstOrFail();

        $journalService = app(JournalPostingService::class);
        $journal = $journalService->createDraft([
            'accounting_period_id' => $this->period->id,
            'journal_date' => $this->period->start_date->toDateString(),
            'description' => 'Expense for budget test',
        ], [
            ['gl_account_id' => $expense->id, 'debit' => 400, 'credit' => 0],
            ['gl_account_id' => $cash->id, 'debit' => 0, 'credit' => 400],
        ], $this->user->id);
        $journalService->post($journal, $this->user->id);

        $service = app(BudgetService::class);
        $budget = $service->create($this->company->id, $this->user->id, [
            'name' => 'FY Test Budget',
            'from_date' => $this->period->start_date->toDateString(),
            'to_date' => $this->period->end_date->toDateString(),
            'lines' => [[
                'gl_account_id' => $expense->id,
                'amount' => 1000,
            ]],
        ]);

        $activated = $service->activate($budget);
        $this->assertSame(BudgetStatus::Active, $activated->status);

        $report = $service->vsActual($budget->fresh('lines.glAccount'));
        $this->assertSame(1000.0, $report['totals']['budget']);
        $this->assertEqualsWithDelta(400.0, $report['totals']['actual'], 0.01);
        $this->assertEqualsWithDelta(600.0, $report['totals']['variance'], 0.01);

        $this->actingAs($this->user)
            ->withHeaders(['Turbo-Frame' => 'module-workspace-content'])
            ->get(route('admin.accounting.budgets.vs-actual', ['budget' => $budget, 'embedded' => 1]))
            ->assertOk()
            ->assertSee(__('Budget vs Actual'));
    }

    protected function postCashSampleJournal(float $amount): void
    {
        $service = app(JournalPostingService::class);
        $cash = GlAccount::query()->where('company_id', $this->company->id)->where('code', '1110')->firstOrFail();
        $equity = GlAccount::query()->where('company_id', $this->company->id)->where('code', '3200')->firstOrFail();

        $journal = $service->createDraft([
            'accounting_period_id' => $this->period->id,
            'journal_date' => $this->period->start_date->toDateString(),
            'description' => 'Cash flow sample',
        ], [
            ['gl_account_id' => $cash->id, 'debit' => $amount, 'credit' => 0],
            ['gl_account_id' => $equity->id, 'debit' => 0, 'credit' => $amount],
        ], $this->user->id);
        $service->post($journal, $this->user->id);
    }
}
