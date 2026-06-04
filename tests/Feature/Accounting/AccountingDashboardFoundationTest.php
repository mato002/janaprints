<?php

namespace Tests\Feature\Accounting;

use App\Models\Accounting\AccountingPeriod;
use App\Models\Accounting\GlAccount;
use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Support\Accounting\Dashboard\AccountingDashboardPresenter;
use App\Support\Accounting\Dashboard\AccountingLedgerMetricsService;
use App\Support\Accounting\JournalPostingService;
use App\Support\TenantContext;
use Database\Seeders\GlAccountTypeSeeder;
use Database\Seeders\JanaPrintsAccountingPeriodsSeeder;
use Database\Seeders\JanaPrintsChartOfAccountsSeeder;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountingDashboardFoundationTest extends TestCase
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

        $this->postSampleLedgerActivity();
    }

    public function test_ledger_metrics_from_posted_journals(): void
    {
        $metrics = app(AccountingLedgerMetricsService::class)->build([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'period_id' => $this->period->id,
        ]);

        $this->assertGreaterThan(0, $metrics['cards']['cash_position']);
        $this->assertGreaterThan(0, $metrics['cards']['revenue_mtd']);
        $this->assertEquals(
            $metrics['cards']['revenue_mtd'],
            $metrics['cards']['revenue_ytd'],
        );
        $this->assertNotEmpty($metrics['charts']['revenue_trend']);
    }

    public function test_dashboard_presenter_returns_six_kpi_cards(): void
    {
        $dashboard = app(AccountingDashboardPresenter::class)->build($this->user, [
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'period_id' => $this->period->id,
        ]);

        $this->assertCount(6, $dashboard['cards']);
        $this->assertArrayHasKey('recent_journals', $dashboard['widgets']);
        $this->assertArrayHasKey('period_closing_alerts', $dashboard['widgets']);
        $this->assertArrayNotHasKey('charts', $dashboard);
    }

    public function test_accounting_dashboard_requires_permission(): void
    {
        $viewer = User::factory()->create([
            'company_id' => $this->company->id,
            'default_branch_id' => $this->branch->id,
            'email_verified_at' => now(),
        ]);
        $viewer->assignRole('Viewer');

        $this->actingAs($viewer)
            ->get(route('admin.accounting.dashboard'))
            ->assertForbidden();
    }

    public function test_accountant_can_view_dashboard(): void
    {
        $accountant = User::factory()->create([
            'company_id' => $this->company->id,
            'default_branch_id' => $this->branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $accountant->assignRole('Accountant');

        $this->actingAs($accountant)
            ->get(route('admin.accounting.dashboard'))
            ->assertOk()
            ->assertSee(__('Accounting Dashboard'));
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
            'description' => 'Dashboard test',
        ], [
            ['gl_account_id' => $cash->id, 'debit' => 1500, 'credit' => 0],
            ['gl_account_id' => $revenue->id, 'debit' => 0, 'credit' => 1500],
        ], $this->user->id);

        $service->post($journal, $this->user->id);
    }
}
