<?php

namespace Tests\Feature\Production;

use App\Enums\PostingEventCode;
use App\Models\Accounting\GlAccount;
use App\Models\Accounting\Journal;
use App\Models\Accounting\PostingRule;
use App\Models\Production\ProductionOutput;
use App\Services\Production\ProductionCompletionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\Feature\Production\Concerns\InteractsWithProductionCompletion;
use Tests\TestCase;

class ProductionCompletionAccountingTest extends TestCase
{
    use InteractsWithProductionCompletion;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedProductionCompletionEnvironment();
    }

    public function test_posts_dr_finished_goods_cr_wip(): void
    {
        [$company, , $user, , $finishedItem, , $jobCard] = $this->readyJobForCompletion();

        $output = app(ProductionCompletionService::class)->post($jobCard, [
            'finished_inventory_item_id' => $finishedItem->id,
            'quantity_completed' => 1,
        ], $user->id);

        $journal = Journal::query()->findOrFail($output->posted_journal_id);
        $fgAccount = GlAccount::query()->where('company_id', $company->id)->where('code', '1430')->firstOrFail();
        $wipAccount = GlAccount::query()->where('company_id', $company->id)->where('code', '1420')->firstOrFail();

        $this->assertSame(PostingEventCode::ProductionCompletionPosted->value, $journal->posting_event);
        $this->assertDatabaseHas('journal_lines', [
            'journal_id' => $journal->id,
            'gl_account_id' => $fgAccount->id,
            'debit' => $output->total_cost,
            'credit' => 0,
        ]);
        $this->assertDatabaseHas('journal_lines', [
            'journal_id' => $journal->id,
            'gl_account_id' => $wipAccount->id,
            'debit' => 0,
            'credit' => $output->total_cost,
        ]);
    }

    public function test_stores_posted_journal_id(): void
    {
        [, , $user, , $finishedItem, , $jobCard] = $this->readyJobForCompletion();

        $output = app(ProductionCompletionService::class)->post($jobCard, [
            'finished_inventory_item_id' => $finishedItem->id,
            'quantity_completed' => 1,
        ], $user->id);

        $this->assertNotNull($output->posted_journal_id);
        $this->assertDatabaseHas('production_outputs', [
            'id' => $output->id,
            'posted_journal_id' => $output->posted_journal_id,
        ]);
    }

    public function test_rolls_back_if_accounting_template_missing(): void
    {
        [$company, , $user, , $finishedItem, , $jobCard] = $this->readyJobForCompletion();

        PostingRule::query()
            ->where('company_id', $company->id)
            ->where('event_code', PostingEventCode::ProductionCompletionPosted->value)
            ->update(['is_active' => false]);

        try {
            app(ProductionCompletionService::class)->post($jobCard, [
                'finished_inventory_item_id' => $finishedItem->id,
                'quantity_completed' => 1,
            ], $user->id);
            $this->fail('Expected ValidationException for missing posting rule.');
        } catch (ValidationException) {
            // expected
        }

        $this->assertSame(0, ProductionOutput::query()->where('production_job_card_id', $jobCard->id)->count());
        $this->assertSame(0, Journal::query()->where('source_type', 'production_output')->count());
    }

    public function test_does_not_post_duplicate_journal(): void
    {
        [$company, , $user, , $finishedItem, , $jobCard] = $this->readyJobForCompletion();
        $service = app(ProductionCompletionService::class);

        $output = $service->post($jobCard, [
            'finished_inventory_item_id' => $finishedItem->id,
            'quantity_completed' => 1,
        ], $user->id);

        $journalCount = Journal::query()
            ->where('company_id', $company->id)
            ->where('posting_event', PostingEventCode::ProductionCompletionPosted->value)
            ->where('source_type', 'production_output')
            ->where('source_id', $output->id)
            ->count();

        $this->assertSame(1, $journalCount);
    }
}
