<?php

namespace Tests\Feature\Accounting;

use App\Enums\JournalStatus;
use App\Models\Accounting\AccountingPeriod;
use App\Models\Accounting\FiscalYear;
use App\Models\Accounting\GlAccount;
use App\Models\Accounting\Journal;
use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Support\Accounting\GeneralLedgerService;
use App\Support\Accounting\JournalPostingService;
use App\Support\Accounting\TrialBalanceService;
use App\Support\TenantContext;
use Database\Seeders\GlAccountTypeSeeder;
use Database\Seeders\JanaPrintsAccountingPeriodsSeeder;
use Database\Seeders\JanaPrintsChartOfAccountsSeeder;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class GeneralLedgerFoundationTest extends TestCase
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

        session(['active_company_id' => $this->company->id, 'active_branch_id' => $this->branch->id]);

        app()->instance(TenantContext::class, new TenantContext($this->company, $this->branch, false));
    }

    public function test_rejects_unbalanced_journal(): void
    {
        $cash = GlAccount::query()->where('company_id', $this->company->id)->where('code', '1110')->firstOrFail();
        $revenue = GlAccount::query()->where('company_id', $this->company->id)->where('code', '4110')->firstOrFail();

        $this->expectException(ValidationException::class);

        app(JournalPostingService::class)->createDraft([
            'accounting_period_id' => $this->period->id,
            'journal_date' => $this->period->start_date->toDateString(),
            'description' => 'Unbalanced test',
        ], [
            ['gl_account_id' => $cash->id, 'debit' => 1000, 'credit' => 0],
            ['gl_account_id' => $revenue->id, 'debit' => 0, 'credit' => 500],
        ], $this->user->id);
    }

    public function test_create_post_and_trial_balance(): void
    {
        $cash = GlAccount::query()->where('company_id', $this->company->id)->where('code', '1110')->firstOrFail();
        $revenue = GlAccount::query()->where('company_id', $this->company->id)->where('code', '4110')->firstOrFail();

        $journal = app(JournalPostingService::class)->createDraft([
            'accounting_period_id' => $this->period->id,
            'journal_date' => $this->period->start_date->toDateString(),
            'description' => 'Test receipt',
        ], [
            ['gl_account_id' => $cash->id, 'debit' => 1000, 'credit' => 0],
            ['gl_account_id' => $revenue->id, 'debit' => 0, 'credit' => 1000],
        ], $this->user->id);

        $this->assertSame(JournalStatus::Draft, $journal->status);
        $this->assertEquals(1000.0, (float) $journal->total_debit);
        $this->assertEquals(1000.0, (float) $journal->total_credit);

        app(JournalPostingService::class)->post($journal, $this->user->id);
        $this->assertSame(JournalStatus::Posted, $journal->fresh()->status);

        $entries = app(GeneralLedgerService::class)->entries(['period_id' => $this->period->id]);
        $this->assertCount(2, $entries);

        $report = app(TrialBalanceService::class)->build(['period_id' => $this->period->id]);
        $this->assertTrue($report['is_balanced']);
        $this->assertEquals(1000.0, $report['total_debit']);
        $this->assertEquals(1000.0, $report['total_credit']);
    }

    public function test_reverse_journal_marks_original_reversed(): void
    {
        $cash = GlAccount::query()->where('company_id', $this->company->id)->where('code', '1110')->firstOrFail();
        $revenue = GlAccount::query()->where('company_id', $this->company->id)->where('code', '4110')->firstOrFail();

        $service = app(JournalPostingService::class);
        $journal = $service->createDraft([
            'accounting_period_id' => $this->period->id,
            'journal_date' => $this->period->start_date->toDateString(),
        ], [
            ['gl_account_id' => $cash->id, 'debit' => 500, 'credit' => 0],
            ['gl_account_id' => $revenue->id, 'debit' => 0, 'credit' => 500],
        ], $this->user->id);

        $service->post($journal, $this->user->id);
        $reversal = $service->reverse($journal->fresh(), $this->user->id);

        $this->assertSame(JournalStatus::Reversed, $journal->fresh()->status);
        $this->assertSame(JournalStatus::Posted, $reversal->status);
        $this->assertSame($journal->id, $reversal->reversal_of_journal_id);

        $report = app(TrialBalanceService::class)->build(['period_id' => $this->period->id]);
        $this->assertTrue($report['is_balanced']);

        $cashRow = $report['rows']->firstWhere('account_code', '1110');
        $revenueRow = $report['rows']->firstWhere('account_code', '4110');
        $this->assertEquals(0.0, $cashRow['balance'] ?? 0.0);
        $this->assertEquals(0.0, $revenueRow['balance'] ?? 0.0);
    }

    public function test_journals_index_requires_permission(): void
    {
        $viewer = User::factory()->create([
            'company_id' => $this->company->id,
            'default_branch_id' => $this->branch->id,
            'email_verified_at' => now(),
        ]);
        $viewer->assignRole('Viewer');

        $this->actingAs($viewer)
            ->get(route('admin.accounting.journals.index'))
            ->assertForbidden();
    }

    public function test_accountant_can_create_journal_via_http(): void
    {
        $accountant = User::factory()->create([
            'company_id' => $this->company->id,
            'default_branch_id' => $this->branch->id,
            'email_verified_at' => now(),
        ]);
        $accountant->assignRole('Accountant');

        $cash = GlAccount::query()->where('code', '1110')->firstOrFail();
        $revenue = GlAccount::query()->where('code', '4110')->firstOrFail();

        $this->actingAs($accountant)
            ->post(route('admin.accounting.journals.store'), [
                'accounting_period_id' => $this->period->id,
                'journal_date' => $this->period->start_date->toDateString(),
                'description' => 'HTTP journal',
                'lines' => [
                    ['gl_account_id' => $cash->id, 'debit' => 200, 'credit' => 0],
                    ['gl_account_id' => $revenue->id, 'debit' => 0, 'credit' => 200],
                ],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('journals', [
            'company_id' => $this->company->id,
            'description' => 'HTTP journal',
            'status' => JournalStatus::Draft->value,
        ]);
    }
}
