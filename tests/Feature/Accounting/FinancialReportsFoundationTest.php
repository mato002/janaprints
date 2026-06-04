<?php

namespace Tests\Feature\Accounting;

use App\Enums\GlAccountTypeCode;
use App\Models\Accounting\AccountingPeriod;
use App\Models\Accounting\GlAccount;
use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Support\Accounting\JournalPostingService;
use App\Support\Accounting\Reports\BalanceSheetReportService;
use App\Support\Accounting\Reports\GeneralLedgerReportService;
use App\Support\Accounting\Reports\ProfitAndLossReportService;
use App\Support\Accounting\TrialBalanceService;
use App\Support\TenantContext;
use Database\Seeders\GlAccountTypeSeeder;
use Database\Seeders\JanaPrintsAccountingPeriodsSeeder;
use Database\Seeders\JanaPrintsChartOfAccountsSeeder;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialReportsFoundationTest extends TestCase
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

        $this->postBalanceSheetSampleJournal();
    }

    public function test_trial_balance_from_posted_journals(): void
    {
        $report = app(TrialBalanceService::class)->build([
            'period_id' => $this->period->id,
        ]);

        $this->assertTrue($report['is_balanced']);
        $this->assertGreaterThan(0, $report['total_debit']);
        $this->assertEquals($report['total_debit'], $report['total_credit']);
    }

    public function test_balance_sheet_balances(): void
    {
        $report = app(BalanceSheetReportService::class)->build([
            'as_of_date' => $this->period->end_date->toDateString(),
        ]);

        $this->assertGreaterThan(0, $report['total_assets']);
        $this->assertGreaterThan(0, $report['total_liabilities_and_equity']);
        $this->assertEqualsWithDelta(
            $report['total_assets'],
            $report['total_liabilities_and_equity'],
            0.02,
            'Balance sheet should balance from posted journals',
        );
    }

    public function test_profit_and_loss_includes_revenue_and_expense(): void
    {
        $this->postProfitAndLossSampleJournal();

        $report = app(ProfitAndLossReportService::class)->build([
            'from_date' => $this->period->start_date->toDateString(),
            'to_date' => $this->period->end_date->toDateString(),
        ]);

        $this->assertGreaterThanOrEqual(500, $report['total_revenue']);
        $this->assertNotEmpty($report['sections'][GlAccountTypeCode::Revenue->value]['accounts']);
    }

    public function test_general_ledger_report_running_balance(): void
    {
        $cash = GlAccount::query()->where('company_id', $this->company->id)->where('code', '1110')->firstOrFail();

        $report = app(GeneralLedgerReportService::class)->build([
            'account_id' => $cash->id,
            'from_date' => $this->period->start_date->toDateString(),
            'to_date' => $this->period->end_date->toDateString(),
        ]);

        $this->assertGreaterThan(0, $report['line_count']);
        $lastLine = $report['lines']->last();
        $this->assertNotNull($lastLine);
        $this->assertEqualsWithDelta($report['closing_balance'], $lastLine['running_balance'], 0.01);
    }

    public function test_financial_reports_require_permission(): void
    {
        $viewer = User::factory()->create([
            'company_id' => $this->company->id,
            'default_branch_id' => $this->branch->id,
            'email_verified_at' => now(),
        ]);
        $viewer->assignRole('Viewer');

        $this->actingAs($viewer)
            ->get(route('admin.accounting.reports.balance-sheet'))
            ->assertForbidden();
    }

    protected function postBalanceSheetSampleJournal(): void
    {
        $service = app(JournalPostingService::class);
        $cash = GlAccount::query()->where('company_id', $this->company->id)->where('code', '1110')->firstOrFail();
        $equity = GlAccount::query()->where('company_id', $this->company->id)->where('code', '3200')->firstOrFail();

        $journal = $service->createDraft([
            'accounting_period_id' => $this->period->id,
            'journal_date' => $this->period->start_date->toDateString(),
            'description' => 'Balance sheet test',
        ], [
            ['gl_account_id' => $cash->id, 'debit' => 2000, 'credit' => 0],
            ['gl_account_id' => $equity->id, 'debit' => 0, 'credit' => 2000],
        ], $this->user->id);
        $service->post($journal, $this->user->id);
    }

    protected function postProfitAndLossSampleJournal(): void
    {
        $service = app(JournalPostingService::class);
        $cash = GlAccount::query()->where('company_id', $this->company->id)->where('code', '1110')->firstOrFail();
        $revenue = GlAccount::query()->where('company_id', $this->company->id)->where('code', '4110')->firstOrFail();

        $journal = $service->createDraft([
            'accounting_period_id' => $this->period->id,
            'journal_date' => $this->period->start_date->toDateString(),
            'description' => 'P&L test',
        ], [
            ['gl_account_id' => $cash->id, 'debit' => 500, 'credit' => 0],
            ['gl_account_id' => $revenue->id, 'debit' => 0, 'credit' => 500],
        ], $this->user->id);
        $service->post($journal, $this->user->id);
    }
}
