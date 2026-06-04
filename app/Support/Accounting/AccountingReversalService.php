<?php

namespace App\Support\Accounting;

use App\Enums\JournalEntryType;
use App\Enums\JournalStatus;
use App\Enums\PostingEventCode;
use App\Models\Accounting\Journal;
use App\Support\Accounting\Dto\PostingContext;
use Illuminate\Validation\ValidationException;

class AccountingReversalService
{
    public function __construct(
        protected JournalPostingService $journalPosting,
    ) {}

    public function reverseBySource(
        int $companyId,
        string $sourceType,
        int $sourceId,
        int $userId,
        ?string $description = null,
        ?PostingEventCode $event = null,
    ): Journal {
        $journal = $this->findReversibleJournal($companyId, $sourceType, $sourceId, $event);

        if (! $journal) {
            throw ValidationException::withMessages([
                'source' => __('No posted journal found for :type #:id.', [
                    'type' => $sourceType,
                    'id' => $sourceId,
                ]),
            ]);
        }

        return $this->journalPosting->reverse($journal, $userId, $description);
    }

    public function reverseContext(PostingContext $context, ?string $description = null): Journal
    {
        $journal = Journal::query()
            ->where('company_id', $context->companyId)
            ->where('posting_event', $context->event->value)
            ->where('source_type', $context->sourceType)
            ->where('source_id', $context->sourceId)
            ->where('status', JournalStatus::Posted)
            ->first();

        if (! $journal) {
            throw ValidationException::withMessages([
                'source' => __('No posted journal for event :event and source :type #:id.', [
                    'event' => $context->event->value,
                    'type' => $context->sourceType,
                    'id' => $context->sourceId,
                ]),
            ]);
        }

        return $this->journalPosting->reverse($journal, $context->userId, $description);
    }

    protected function findReversibleJournal(
        int $companyId,
        string $sourceType,
        int $sourceId,
        ?PostingEventCode $event,
    ): ?Journal {
        $query = Journal::query()
            ->where('company_id', $companyId)
            ->where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->where('status', JournalStatus::Posted)
            ->where('entry_type', '!=', JournalEntryType::Reversal);

        if ($event) {
            $query->where('posting_event', $event->value);
        }

        return $query->latest('posted_at')->first();
    }
}
