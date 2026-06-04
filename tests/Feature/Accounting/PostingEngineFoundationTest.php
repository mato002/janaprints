<?php

namespace Tests\Feature\Accounting;

use App\Enums\JournalEntryType;
use App\Enums\JournalStatus;
use App\Enums\PostingEventCode;
use App\Models\Accounting\AccountingPeriod;
use App\Models\Accounting\GlAccount;
use App\Models\Accounting\Journal;
use App\Models\Accounting\PostingRule;
use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Support\Accounting\AccountingPostingService;
use App\Support\Accounting\AccountingReversalService;
use App\Support\Accounting\Dto\PostingContext;
use App\Support\Accounting\TrialBalanceService;
use App\Support\TenantContext;
use Database\Seeders\GlAccountTypeSeeder;
use Database\Seeders\JanaPrintsAccountingPeriodsSeeder;
use Database\Seeders\JanaPrintsChartOfAccountsSeeder;
use Database\Seeders\JanaPrintsPostingEngineSeeder;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PostingEngineFoundationTest extends TestCase
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
        $this->seed(JanaPrintsPostingEngineSeeder::class);

        $this->company = Company::query()->where('code', 'JANA')->firstOrFail();
        $this->branch = Branch::query()->where('company_id', $this->company->id)->firstOrFail();
        $this->user = User::factory()->create([
            'company_id' => $this->company->id,
            'default_branch_id' => $this->branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $this->period = AccountingPeriod::query()
            ->where('company_id', $this->company->id)
            ->where('is_current', true)
            ->firstOrFail();

        app()->instance(TenantContext::class, new TenantContext($this->company, $this->branch, false));
    }

    public function test_posts_customer_payment_via_posting_engine(): void
    {
        $bank = GlAccount::query()->where('company_id', $this->company->id)->where('code', '1210')->firstOrFail();

        $journal = app(AccountingPostingService::class)->postEvent(
            PostingEventCode::PaymentReceived,
            $this->company->id,
            $this->user->id,
            'customer_payment',
            9001,
            $this->period->start_date->toDateString(),
            [
                'total_amount' => 1500.00,
                'allocated_amount' => 1500.00,
                'unallocated_amount' => 0,
            ],
            $this->branch->id,
            $this->period->id,
            'RCP-9001',
            'Test customer payment',
            accounts: ['receipt_account' => $bank->id],
        );

        $this->assertSame(JournalStatus::Posted, $journal->status);
        $this->assertSame(JournalEntryType::System, $journal->entry_type);
        $this->assertSame(PostingEventCode::PaymentReceived->value, $journal->posting_event);
        $this->assertSame('customer_payment', $journal->source_type);
        $this->assertSame(9001, $journal->source_id);
        $this->assertTrue($journal->isBalanced());
        $this->assertCount(2, $journal->lines);
    }

    public function test_posting_is_idempotent_for_same_source(): void
    {
        $service = app(AccountingPostingService::class);
        $bank = GlAccount::query()->where('company_id', $this->company->id)->where('code', '1210')->firstOrFail();
        $amounts = [
            'total_amount' => 500.00,
            'allocated_amount' => 500.00,
            'unallocated_amount' => 0,
        ];
        $accounts = ['receipt_account' => $bank->id];

        $first = $service->postEvent(
            PostingEventCode::PaymentReceived,
            $this->company->id,
            $this->user->id,
            'customer_payment',
            9002,
            $this->period->start_date->toDateString(),
            $amounts,
            accountingPeriodId: $this->period->id,
            accounts: $accounts,
        );

        $second = $service->postEvent(
            PostingEventCode::PaymentReceived,
            $this->company->id,
            $this->user->id,
            'customer_payment',
            9002,
            $this->period->start_date->toDateString(),
            $amounts,
            accountingPeriodId: $this->period->id,
            accounts: $accounts,
        );

        $this->assertSame($first->id, $second->id);
        $this->assertSame(1, Journal::query()->where('source_id', 9002)->where('posting_event', PostingEventCode::PaymentReceived->value)->count());
    }

    public function test_reverses_posted_journal_by_source(): void
    {
        $posting = app(AccountingPostingService::class);
        $posting->postEvent(
            PostingEventCode::InvoicePosted,
            $this->company->id,
            $this->user->id,
            'sales_invoice',
            8001,
            $this->period->start_date->toDateString(),
            [
                'subtotal' => 1000.00,
                'tax_amount' => 150.00,
                'total_amount' => 1150.00,
            ],
            accountingPeriodId: $this->period->id,
        );

        $reversal = app(AccountingReversalService::class)->reverseBySource(
            $this->company->id,
            'sales_invoice',
            8001,
            $this->user->id,
            event: PostingEventCode::InvoicePosted,
        );

        $this->assertSame(JournalEntryType::Reversal, $reversal->entry_type);
        $this->assertSame(JournalStatus::Posted, $reversal->status);

        $report = app(TrialBalanceService::class)->build(['period_id' => $this->period->id]);
        $receivables = $report['rows']->firstWhere('account_code', '1300');
        $revenue = $report['rows']->firstWhere('account_code', '4110');
        $vat = $report['rows']->firstWhere('account_code', '2120');

        $this->assertEqualsWithDelta(0, $receivables['balance'] ?? 0.0, 0.01);
        $this->assertEqualsWithDelta(0, $revenue['balance'] ?? 0.0, 0.01);
        $this->assertEqualsWithDelta(0, $vat['balance'] ?? 0.0, 0.01);
    }

    public function test_rejects_posting_without_rule(): void
    {
        PostingRule::query()
            ->where('company_id', $this->company->id)
            ->where('event_code', PostingEventCode::PaymentReceived->value)
            ->delete();

        $this->expectException(ValidationException::class);

        app(AccountingPostingService::class)->post(new PostingContext(
            companyId: $this->company->id,
            userId: $this->user->id,
            event: PostingEventCode::PaymentReceived,
            sourceType: 'orphan',
            sourceId: 1,
            journalDate: $this->period->start_date->toDateString(),
            accountingPeriodId: $this->period->id,
            amounts: ['total_amount' => 100],
        ));
    }

    public function test_seeded_rules_cover_all_posting_events(): void
    {
        foreach (PostingEventCode::cases() as $event) {
            $this->assertDatabaseHas('posting_rules', [
                'company_id' => $this->company->id,
                'event_code' => $event->value,
                'is_active' => true,
            ]);
        }
    }
}
