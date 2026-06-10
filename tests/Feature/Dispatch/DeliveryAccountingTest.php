<?php

namespace Tests\Feature\Dispatch;

use App\Enums\PostingEventCode;
use App\Models\Accounting\GlAccount;
use App\Models\Accounting\Journal;
use App\Models\Accounting\PostingRule;
use App\Services\Dispatch\DeliveryNoteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\Feature\Dispatch\Concerns\InteractsWithDispatchInventory;
use Tests\TestCase;

class DeliveryAccountingTest extends TestCase
{
    use InteractsWithDispatchInventory;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedDispatchInventoryEnvironment();
    }

    public function test_posts_dr_cogs_cr_finished_goods(): void
    {
        [$note, , $user, $jobCard] = $this->readyDispatchedDeliveryNote();

        app(DeliveryNoteService::class)->deliver($note, $user->id);
        $note->refresh();

        $journal = Journal::query()->findOrFail($note->posted_journal_id);
        $cogs = GlAccount::query()->where('company_id', $jobCard->company_id)->where('code', '5450')->firstOrFail();
        $fg = GlAccount::query()->where('company_id', $jobCard->company_id)->where('code', '1430')->firstOrFail();

        $this->assertSame(PostingEventCode::DeliveryCogsPosted->value, $journal->posting_event);
        $this->assertDatabaseHas('journal_lines', [
            'journal_id' => $journal->id,
            'gl_account_id' => $cogs->id,
            'credit' => 0,
        ]);
        $this->assertDatabaseHas('journal_lines', [
            'journal_id' => $journal->id,
            'gl_account_id' => $fg->id,
            'debit' => 0,
        ]);
    }

    public function test_stores_posted_journal_id_on_delivery_note(): void
    {
        [$note, , $user] = $this->readyDispatchedDeliveryNote();

        app(DeliveryNoteService::class)->deliver($note, $user->id);

        $this->assertNotNull($note->fresh()->posted_journal_id);
    }

    public function test_rolls_back_if_accounting_template_missing(): void
    {
        [$note, , $user, $jobCard] = $this->readyDispatchedDeliveryNote();

        PostingRule::query()
            ->where('company_id', $jobCard->company_id)
            ->where('event_code', PostingEventCode::DeliveryCogsPosted->value)
            ->update(['is_active' => false]);

        try {
            app(DeliveryNoteService::class)->deliver($note, $user->id);
            $this->fail('Expected ValidationException.');
        } catch (ValidationException) {
        }

        $this->assertNull($note->fresh()->posted_journal_id);
        $this->assertSame('dispatched', $note->fresh()->status->value);
    }
}
