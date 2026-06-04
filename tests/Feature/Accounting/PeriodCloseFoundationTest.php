<?php

namespace Tests\Feature\Accounting;

use App\Enums\AccountingCloseType;
use App\Enums\AccountingPeriodStatus;
use App\Enums\FiscalYearStatus;
use App\Models\Accounting\AccountingCloseAudit;
use App\Models\Accounting\AccountingPeriod;
use App\Models\Accounting\FiscalYear;
use App\Models\Accounting\GlAccount;
use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Support\Accounting\Close\FinancialIntegrityService;
use App\Support\Accounting\Close\PeriodCloseService;
use App\Support\Accounting\Close\YearEndCloseService;
use App\Support\Accounting\JournalPostingService;
use App\Support\Accounting\Reports\BalanceSheetReportService;
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

class PeriodCloseFoundationTest extends TestCase
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

    public function test_period_close_rolls_pl_into_current_year_earnings_and_balances_sheet(): void
    {
        $this->postOperatingRevenueJournal(1500);

        $bsBefore = app(BalanceSheetReportService::class)->build([
            'company_id' => $this->company->id,
            'as_of_date' => $this->period->end_date->toDateString(),
        ]);
        $this->assertFalse($bsBefore['is_balanced']);

        $result = app(PeriodCloseService::class)->close($this->period, $this->user->id);

        $this->assertSame(AccountingPeriodStatus::Closed, $result['period']->status);
        $this->assertNotNull($result['journal']);
        $this->assertDatabaseHas('accounting_close_audits', [
            'accounting_period_id' => $this->period->id,
            'close_type' => AccountingCloseType::PeriodClose->value,
        ]);

        $bsAfter = app(BalanceSheetReportService::class)->build([
            'company_id' => $this->company->id,
            'as_of_date' => $this->period->end_date->toDateString(),
        ]);
        $this->assertTrue($bsAfter['is_balanced']);
        $this->assertEqualsWithDelta(1500, $bsAfter['total_assets'], 0.02);
        $this->assertEqualsWithDelta(1500, $bsAfter['total_equity'], 0.02);

        $pl = app(ProfitAndLossReportService::class)->build([
            'company_id' => $this->company->id,
            'from_date' => $this->period->start_date->toDateString(),
            'to_date' => $this->period->end_date->toDateString(),
            'period_id' => $this->period->id,
        ]);
        $this->assertEqualsWithDelta(0, $pl['net_profit'], 0.02);

        $tb = app(TrialBalanceService::class)->build([
            'company_id' => $this->company->id,
            'period_id' => $this->period->id,
        ]);
        $this->assertTrue($tb['is_balanced']);
    }

    public function test_reopen_period_reverses_close_and_restores_pl_balances(): void
    {
        $this->postOperatingRevenueJournal(800);
        app(PeriodCloseService::class)->close($this->period, $this->user->id);

        app(PeriodCloseService::class)->reopen($this->period->fresh(), $this->user->id);

        $this->assertSame(AccountingPeriodStatus::Open, $this->period->fresh()->status);
        $this->assertNotNull(
            AccountingCloseAudit::query()
                ->where('accounting_period_id', $this->period->id)
                ->where('close_type', AccountingCloseType::PeriodClose)
                ->whereNotNull('reversed_at')
                ->first(),
        );

        $pl = app(ProfitAndLossReportService::class)->build([
            'company_id' => $this->company->id,
            'from_date' => $this->period->start_date->toDateString(),
            'to_date' => $this->period->end_date->toDateString(),
            'period_id' => $this->period->id,
        ]);
        $this->assertGreaterThanOrEqual(800, $pl['total_revenue']);
    }

    public function test_year_end_transfers_current_year_earnings_to_retained_earnings(): void
    {
        $fy = FiscalYear::query()
            ->where('company_id', $this->company->id)
            ->where('is_current', true)
            ->firstOrFail();

        $this->postOperatingRevenueJournal(2000);

        foreach ($fy->periods()->orderBy('period_number')->get() as $period) {
            if ($period->status === AccountingPeriodStatus::Open) {
                app(PeriodCloseService::class)->close($period, $this->user->id);
            }
        }

        $cyBefore = $this->accountBalance('3300');
        $this->assertGreaterThan(0, $cyBefore);

        app(YearEndCloseService::class)->closeFiscalYear($fy, $this->user->id);

        $this->assertSame(FiscalYearStatus::Closed, $fy->fresh()->status);
        $this->assertDatabaseHas('accounting_close_audits', [
            'fiscal_year_id' => $fy->id,
            'close_type' => AccountingCloseType::YearEndClose->value,
        ]);

        $this->assertEqualsWithDelta(0, $this->accountBalance('3300'), 0.02);
        $this->assertGreaterThan(0, $this->accountBalance('3200'));

        $integrity = app(FinancialIntegrityService::class)->buildIntegrityReport(
            $this->company->id,
            $fy->end_date->toDateString(),
        );
        $this->assertTrue($integrity['balance_sheet_balanced']);
    }

    public function test_accountant_can_close_period_via_http(): void
    {
        $accountant = User::factory()->create([
            'company_id' => $this->company->id,
            'default_branch_id' => $this->branch->id,
            'email_verified_at' => now(),
        ]);
        $accountant->assignRole('Accountant');

        $this->postOperatingRevenueJournal(300);

        session(['active_company_id' => $this->company->id, 'active_branch_id' => $this->branch->id]);

        $this->actingAs($accountant)
            ->post(route('admin.accounting.periods.close', $this->period))
            ->assertRedirect();

        $this->assertSame(AccountingPeriodStatus::Closed, $this->period->fresh()->status);
    }

    public function test_financial_integrity_report_accessible(): void
    {
        $this->postOperatingRevenueJournal(100);
        app(PeriodCloseService::class)->close($this->period, $this->user->id);

        session(['active_company_id' => $this->company->id, 'active_branch_id' => $this->branch->id]);

        $this->actingAs($this->user)
            ->get(route('admin.accounting.reports.financial-integrity', [
                'as_of_date' => $this->period->end_date->toDateString(),
            ]))
            ->assertOk()
            ->assertSee(__('Financial Integrity'));
    }

    protected function postOperatingRevenueJournal(float $amount): void
    {
        $cash = GlAccount::query()->where('company_id', $this->company->id)->where('code', '1110')->firstOrFail();
        $revenue = GlAccount::query()->where('company_id', $this->company->id)->where('code', '4110')->firstOrFail();

        $journal = app(JournalPostingService::class)->createDraft([
            'accounting_period_id' => $this->period->id,
            'journal_date' => $this->period->start_date->toDateString(),
            'description' => 'Operating revenue test',
        ], [
            ['gl_account_id' => $cash->id, 'debit' => $amount, 'credit' => 0],
            ['gl_account_id' => $revenue->id, 'debit' => 0, 'credit' => $amount],
        ], $this->user->id);

        app(JournalPostingService::class)->post($journal, $this->user->id);
    }

    protected function accountBalance(string $code): float
    {
        $account = GlAccount::query()
            ->where('company_id', $this->company->id)
            ->where('code', $code)
            ->firstOrFail();

        $row = \App\Support\Accounting\Reports\PostedJournalQuery::applyFilters(
            \App\Support\Accounting\Reports\PostedJournalQuery::base($this->company->id),
            ['account_id' => $account->id],
        )
            ->selectRaw('COALESCE(SUM(journal_lines.debit), 0) as total_debit')
            ->selectRaw('COALESCE(SUM(journal_lines.credit), 0) as total_credit')
            ->first();

        return \App\Support\Accounting\LedgerSignedBalance::balanceSheetAmount(
            (float) $row->total_debit,
            (float) $row->total_credit,
            $account->normal_balance,
        );
    }
}
