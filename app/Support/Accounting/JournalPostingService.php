<?php

namespace App\Support\Accounting;

use App\Enums\DocumentType;
use App\Enums\JournalEntryType;
use App\Enums\JournalStatus;
use App\Models\Accounting\AccountingPeriod;
use App\Models\Accounting\Journal;
use App\Models\Accounting\JournalLine;
use App\Support\Platform\NumberingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class JournalPostingService
{
    public function __construct(
        protected JournalValidationService $validator,
        protected NumberingService $numbering,
    ) {}

    /**
     * @param  array<string, mixed>  $header
     * @param  array<int, array<string, mixed>>  $lines
     */
    public function createDraft(array $header, array $lines, int $userId): Journal
    {
        $period = AccountingPeriod::query()->findOrFail($header['accounting_period_id']);
        $entryType = isset($header['entry_type'])
            ? ($header['entry_type'] instanceof JournalEntryType ? $header['entry_type'] : JournalEntryType::from($header['entry_type']))
            : null;

        $validated = $this->validator->validateDraft(
            $lines,
            $period,
            $header['journal_date'],
            $entryType,
        );

        return DB::transaction(function () use ($header, $validated, $period, $userId) {
            $journal = Journal::query()->create([
                'company_id' => $period->company_id,
                'branch_id' => $header['branch_id'] ?? null,
                'fiscal_year_id' => $period->fiscal_year_id,
                'accounting_period_id' => $period->id,
                'journal_number' => $this->numbering->next(
                    DocumentType::Journal,
                    $period->company_id,
                    $header['branch_id'] ?? null,
                ),
                'journal_date' => $header['journal_date'],
                'entry_type' => $header['entry_type'] ?? JournalEntryType::Manual,
                'status' => JournalStatus::Draft,
                'reference' => $header['reference'] ?? null,
                'description' => $header['description'] ?? null,
                'posting_event' => $header['posting_event'] ?? null,
                'source_module' => $header['source_module'] ?? null,
                'source_type' => $header['source_type'] ?? null,
                'source_id' => $header['source_id'] ?? null,
                'posting_template_id' => $header['posting_template_id'] ?? null,
                'posting_rule_id' => $header['posting_rule_id'] ?? null,
                'total_debit' => $validated['total_debit'],
                'total_credit' => $validated['total_credit'],
                'created_by' => $userId,
            ]);

            $this->syncLines($journal, $validated['lines']);

            return $journal->load(['lines.glAccount', 'accountingPeriod', 'fiscalYear', 'creator']);
        });
    }

    /**
     * @param  array<string, mixed>  $header
     * @param  array<int, array<string, mixed>>  $lines
     */
    public function updateDraft(Journal $journal, array $header, array $lines): Journal
    {
        if (! $journal->status->isEditable()) {
            throw ValidationException::withMessages([
                'journal' => __('Only draft journals can be edited.'),
            ]);
        }

        $period = AccountingPeriod::query()->findOrFail($header['accounting_period_id'] ?? $journal->accounting_period_id);
        $entryType = isset($header['entry_type'])
            ? ($header['entry_type'] instanceof JournalEntryType ? $header['entry_type'] : JournalEntryType::from($header['entry_type']))
            : $journal->entry_type;

        $validated = $this->validator->validateDraft(
            $lines,
            $period,
            $header['journal_date'] ?? $journal->journal_date->toDateString(),
            $entryType,
        );

        return DB::transaction(function () use ($journal, $header, $validated, $period) {
            $journal->update([
                'branch_id' => $header['branch_id'] ?? $journal->branch_id,
                'fiscal_year_id' => $period->fiscal_year_id,
                'accounting_period_id' => $period->id,
                'journal_date' => $header['journal_date'] ?? $journal->journal_date,
                'reference' => $header['reference'] ?? $journal->reference,
                'description' => $header['description'] ?? $journal->description,
                'total_debit' => $validated['total_debit'],
                'total_credit' => $validated['total_credit'],
            ]);

            $journal->lines()->delete();
            $this->syncLines($journal, $validated['lines']);

            return $journal->fresh(['lines.glAccount', 'accountingPeriod', 'fiscalYear', 'creator']);
        });
    }

    public function post(Journal $journal, int $userId): Journal
    {
        $this->validator->assertCanPost($journal);

        $journal->update([
            'status' => JournalStatus::Posted,
            'posted_at' => now(),
            'posted_by' => $userId,
        ]);

        return $journal->fresh(['lines.glAccount', 'accountingPeriod', 'poster', 'creator']);
    }

    public function reverse(Journal $journal, int $userId, ?string $description = null): Journal
    {
        $this->validator->assertCanReverse($journal);

        $journal->load('lines.glAccount', 'accountingPeriod');

        return DB::transaction(function () use ($journal, $userId, $description) {
            $period = $journal->accountingPeriod;
            $journalDate = $this->reversalJournalDate($period);

            $reversalLines = $journal->lines->map(fn (JournalLine $line) => [
                'gl_account_id' => $line->gl_account_id,
                'description' => __('Reversal of :number', ['number' => $journal->journal_number]),
                'debit' => (float) $line->credit,
                'credit' => (float) $line->debit,
            ])->all();

            $reversal = $this->createDraft([
                'accounting_period_id' => $journal->accounting_period_id,
                'branch_id' => $journal->branch_id,
                'journal_date' => $journalDate,
                'reference' => 'REV-'.$journal->journal_number,
                'description' => $description ?? __('Reversal of journal :number', ['number' => $journal->journal_number]),
            ], $reversalLines, $userId);

            $reversal->update([
                'entry_type' => JournalEntryType::Reversal,
                'reversal_of_journal_id' => $journal->id,
            ]);

            $this->post($reversal, $userId);

            $journal->update([
                'status' => JournalStatus::Reversed,
                'reversed_at' => now(),
                'reversed_by_journal_id' => $reversal->id,
            ]);

            return $reversal->fresh(['lines.glAccount', 'reversalOf', 'poster']);
        });
    }

    public function deleteDraft(Journal $journal): void
    {
        if (! $journal->status->isEditable()) {
            throw ValidationException::withMessages([
                'journal' => __('Only draft journals can be deleted.'),
            ]);
        }

        $journal->delete();
    }

    /**
     * @param  list<array{gl_account_id: int, description: ?string, debit: float, credit: float}>  $lines
     */
    protected function syncLines(Journal $journal, array $lines): void
    {
        foreach ($lines as $index => $line) {
            $journal->lines()->create([
                'gl_account_id' => $line['gl_account_id'],
                'line_number' => $index + 1,
                'description' => $line['description'],
                'debit' => $line['debit'],
                'credit' => $line['credit'],
            ]);
        }
    }

    protected function reversalJournalDate(AccountingPeriod $period): string
    {
        $today = now()->toDateString();
        $start = $period->start_date->toDateString();
        $end = $period->end_date->toDateString();

        if ($today < $start) {
            return $start;
        }

        if ($today > $end) {
            return $end;
        }

        return $today;
    }
}
