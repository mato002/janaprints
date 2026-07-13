<?php

namespace Tests\Feature\Commercial;

use App\Enums\JournalStatus;
use App\Enums\PosPaymentMethod;
use App\Enums\PosRefundMethod;
use App\Enums\PosReturnType;
use App\Enums\PosSaleStatus;
use App\Enums\PosSessionStatus;
use App\Enums\PostingEventCode;
use App\Models\Accounting\AccountingPeriod;
use App\Models\Accounting\GlAccount;
use App\Models\Accounting\Journal;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Pos\PosPayment;
use App\Models\Pos\PosReturn;
use App\Models\Pos\PosSale;
use App\Models\Pos\PosSession;
use App\Models\User;
use App\Support\Accounting\PosAccountingPostingService;
use App\Support\Commercial\PosSaleService;
use App\Support\TenantContext;
use Database\Seeders\GlAccountTypeSeeder;
use Database\Seeders\JanaPrintsAccountingPeriodsSeeder;
use Database\Seeders\JanaPrintsChartOfAccountsSeeder;
use Database\Seeders\JanaPrintsPosPostingSeeder;
use Database\Seeders\JanaPrintsPostingEngineSeeder;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PosAccountingTruthTest extends TestCase
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
        $this->seed(JanaPrintsPosPostingSeeder::class);

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

    public function test_cash_sale_journal(): void
    {
        $sale = $this->createPaidSale(PosPaymentMethod::Cash, 500.00);

        $payment = $sale->payments->first();
        $this->assertNotNull($payment->posted_journal_id);

        $journal = Journal::query()->findOrFail($payment->posted_journal_id);
        $this->assertSame(JournalStatus::Posted, $journal->status);
        $this->assertSame(PostingEventCode::PosSaleCash->value, $journal->posting_event);
        $this->assertTrue($journal->isBalanced());

        $cashTill = GlAccount::query()->where('company_id', $this->company->id)->where('code', '1120')->firstOrFail();
        $revenue = GlAccount::query()->where('company_id', $this->company->id)->where('code', '4110')->firstOrFail();

        $this->assertJournalHasLine($journal, $cashTill->id, debit: 500.00);
        $this->assertJournalHasLine($journal, $revenue->id, credit: 500.00);
    }

    public function test_mpesa_sale_journal(): void
    {
        $sale = $this->createPaidSale(PosPaymentMethod::Mpesa, 750.00);

        $journal = Journal::query()->findOrFail($sale->payments->first()->posted_journal_id);
        $this->assertSame(PostingEventCode::PosSaleMpesa->value, $journal->posting_event);

        $mpesa = GlAccount::query()->where('company_id', $this->company->id)->where('code', '1210')->firstOrFail();
        $this->assertJournalHasLine($journal, $mpesa->id, debit: 750.00);
    }

    public function test_card_sale_journal(): void
    {
        $sale = $this->createPaidSale(PosPaymentMethod::Card, 1200.00);

        $journal = Journal::query()->findOrFail($sale->payments->first()->posted_journal_id);
        $this->assertSame(PostingEventCode::PosSaleCard->value, $journal->posting_event);

        $card = GlAccount::query()->where('company_id', $this->company->id)->where('code', '1240')->firstOrFail();
        $this->assertJournalHasLine($journal, $card->id, debit: 1200.00);
    }

    public function test_bank_sale_journal(): void
    {
        $sale = $this->createPaidSale(PosPaymentMethod::Bank, 900.00);

        $journal = Journal::query()->findOrFail($sale->payments->first()->posted_journal_id);
        $this->assertSame(PostingEventCode::PosSaleBank->value, $journal->posting_event);
        $this->assertTrue($journal->isBalanced());

        $bank = GlAccount::query()->where('company_id', $this->company->id)->where('code', '1210')->firstOrFail();
        $revenue = GlAccount::query()->where('company_id', $this->company->id)->where('code', '4110')->firstOrFail();

        $this->assertJournalHasLine($journal, $bank->id, debit: 900.00);
        $this->assertJournalHasLine($journal, $revenue->id, credit: 900.00);
    }

    public function test_return_reversal_journal(): void
    {
        $sale = $this->createPaidSale(PosPaymentMethod::Cash, 400.00)->fresh('items');

        $return = app(\App\Support\Commercial\PosReturnService::class)->createReturn(
            $sale,
            PosReturnType::FullReturn,
            PosRefundMethod::Cash,
            'Customer return',
            [],
            $this->user->id,
        );

        app(\App\Support\Commercial\PosReturnService::class)->approveReturn($return, $this->user->id);

        $return->refresh();
        $this->assertNotNull($return->posted_journal_id);

        $journal = Journal::query()->findOrFail($return->posted_journal_id);
        $this->assertSame(PostingEventCode::PosReturn->value, $journal->posting_event);
        $this->assertTrue($journal->isBalanced());

        $salesReturns = GlAccount::query()->where('company_id', $this->company->id)->where('code', '4160')->firstOrFail();
        $cashTill = GlAccount::query()->where('company_id', $this->company->id)->where('code', '1120')->firstOrFail();

        $this->assertJournalHasLine($journal, $salesReturns->id, debit: 400.00);
        $this->assertJournalHasLine($journal, $cashTill->id, credit: 400.00);
    }

    public function test_no_duplicate_posting(): void
    {
        $sale = $this->createPaidSale(PosPaymentMethod::Cash, 250.00);
        $payment = $sale->payments->first();
        $journalId = $payment->posted_journal_id;

        app(PosAccountingPostingService::class)->postPaidSale($sale->fresh('payments'), $this->user->id);

        $payment->refresh();
        $this->assertSame($journalId, $payment->posted_journal_id);
        $this->assertSame(1, Journal::query()
            ->where('posting_event', PostingEventCode::PosSaleCash->value)
            ->where('source_type', 'pos_payment')
            ->where('source_id', $payment->id)
            ->count());
    }

    public function test_held_sale_does_not_post_journal(): void
    {
        $session = $this->openSession();

        $sale = app(PosSaleService::class)->createSale([
            'status' => PosSaleStatus::Held,
            'is_walk_in' => true,
            'lines' => [[
                'description' => 'Held item',
                'quantity' => 1,
                'unit_price' => 100,
                'discount_amount' => 0,
                'tax_amount' => 0,
            ]],
        ], $this->company->id, $this->branch->id, $this->user->id, $session->id);

        $this->assertSame(0, Journal::query()->where('source_type', 'pos_payment')->count());
        $this->assertNull(PosPayment::query()->where('pos_sale_id', $sale->id)->value('posted_journal_id'));
    }

    protected function createPaidSale(PosPaymentMethod $method, float $amount): PosSale
    {
        $session = $this->openSession();

        return app(PosSaleService::class)->createSale([
            'status' => PosSaleStatus::Paid,
            'is_walk_in' => true,
            'amount_paid' => $amount,
            'lines' => [[
                'description' => 'Retail item',
                'quantity' => 1,
                'unit_price' => $amount,
                'discount_amount' => 0,
                'tax_amount' => 0,
            ]],
            'payments' => [[
                'payment_method' => $method->value,
                'amount' => $amount,
            ]],
        ], $this->company->id, $this->branch->id, $this->user->id, $session->id);
    }

    protected function openSession(): PosSession
    {
        return PosSession::query()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'cashier_id' => $this->user->id,
            'session_number' => 'SES-GL-'.uniqid(),
            'opening_float' => 0,
            'opening_cash' => 0,
            'status' => PosSessionStatus::Open,
            'opened_at' => now(),
            'opened_by' => $this->user->id,
        ]);
    }

    protected function assertJournalHasLine(Journal $journal, int $accountId, float $debit = 0, float $credit = 0): void
    {
        $journal->load('lines');
        $line = $journal->lines->firstWhere('gl_account_id', $accountId);

        $this->assertNotNull($line, "Journal missing line for account {$accountId}");
        $this->assertEqualsWithDelta($debit, (float) $line->debit, 0.01);
        $this->assertEqualsWithDelta($credit, (float) $line->credit, 0.01);
    }
}
