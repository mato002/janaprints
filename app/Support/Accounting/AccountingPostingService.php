<?php

namespace App\Support\Accounting;

use App\Enums\AccountingPeriodStatus;
use App\Enums\JournalEntryType;
use App\Enums\JournalStatus;
use App\Enums\PostingEventCode;
use App\Models\Accounting\AccountingPeriod;
use App\Models\Accounting\Journal;
use App\Models\Accounting\PostingRule;
use App\Support\Accounting\Dto\PostingContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AccountingPostingService
{
    public function __construct(
        protected PostingTemplateBuilderService $templateBuilder,
        protected JournalPostingService $journalPosting,
    ) {}

    public function post(PostingContext $context): Journal
    {
        $existing = $this->findPostedJournal($context);

        if ($existing) {
            return $existing->load(['lines.glAccount', 'accountingPeriod', 'poster', 'creator']);
        }

        $rule = $this->resolveRule($context);
        $template = $rule->template()->with('lines')->firstOrFail();
        $period = $this->resolvePeriod($context);
        $lines = $this->templateBuilder->buildJournalLines($template, $context);

        return DB::transaction(function () use ($context, $rule, $template, $period, $lines) {
            $journal = $this->journalPosting->createDraft([
                'accounting_period_id' => $period->id,
                'branch_id' => $context->branchId,
                'journal_date' => $context->journalDate,
                'reference' => $context->reference,
                'description' => $context->description ?? $rule->name,
                'entry_type' => JournalEntryType::System,
                'posting_event' => $context->event->value,
                'source_module' => $context->module()->value,
                'source_type' => $context->sourceType,
                'source_id' => $context->sourceId,
                'posting_template_id' => $template->id,
                'posting_rule_id' => $rule->id,
            ], $lines, $context->userId);

            if ($rule->auto_post) {
                return $this->journalPosting->post($journal, $context->userId);
            }

            return $journal;
        });
    }

    public function postEvent(
        PostingEventCode $event,
        int $companyId,
        int $userId,
        string $sourceType,
        int $sourceId,
        string $journalDate,
        array $amounts,
        ?int $branchId = null,
        ?int $accountingPeriodId = null,
        ?string $reference = null,
        ?string $description = null,
        array $accounts = [],
        array $metadata = [],
    ): Journal {
        return $this->post(new PostingContext(
            companyId: $companyId,
            userId: $userId,
            event: $event,
            sourceType: $sourceType,
            sourceId: $sourceId,
            journalDate: $journalDate,
            branchId: $branchId,
            accountingPeriodId: $accountingPeriodId,
            reference: $reference,
            description: $description,
            accounts: $accounts,
            amounts: $amounts,
            metadata: $metadata,
        ));
    }

    public function findPostedJournal(PostingContext $context): ?Journal
    {
        return Journal::query()
            ->where('company_id', $context->companyId)
            ->where('posting_event', $context->event->value)
            ->where('source_type', $context->sourceType)
            ->where('source_id', $context->sourceId)
            ->where('status', JournalStatus::Posted)
            ->first();
    }

    protected function resolveRule(PostingContext $context): PostingRule
    {
        $rule = PostingRule::query()
            ->where('company_id', $context->companyId)
            ->where('event_code', $context->event->value)
            ->where('is_active', true)
            ->orderBy('priority')
            ->first();

        if (! $rule) {
            throw ValidationException::withMessages([
                'event' => __('No active posting rule for :event.', ['event' => $context->event->value]),
            ]);
        }

        return $rule;
    }

    protected function resolvePeriod(PostingContext $context): AccountingPeriod
    {
        if ($context->accountingPeriodId) {
            return AccountingPeriod::query()
                ->where('company_id', $context->companyId)
                ->findOrFail($context->accountingPeriodId);
        }

        $period = AccountingPeriod::query()
            ->where('company_id', $context->companyId)
            ->where('is_current', true)
            ->first();

        if (! $period) {
            throw ValidationException::withMessages([
                'period' => __('No current accounting period for posting.'),
            ]);
        }

        $date = $context->journalDate;
        $start = $period->start_date->toDateString();
        $end = $period->end_date->toDateString();

        if ($date < $start || $date > $end) {
            $period = AccountingPeriod::query()
                ->where('company_id', $context->companyId)
                ->whereDate('start_date', '<=', $date)
                ->whereDate('end_date', '>=', $date)
                ->first();
        }

        if (! $period) {
            throw ValidationException::withMessages([
                'period' => __('No open accounting period for date :date.', ['date' => $date]),
            ]);
        }

        if ($period->status !== AccountingPeriodStatus::Open) {
            throw ValidationException::withMessages([
                'period' => __('Accounting period :code is not open for posting.', ['code' => $period->code]),
            ]);
        }

        return $period;
    }
}
