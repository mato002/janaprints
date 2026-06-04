<?php

namespace App\Support\Accounting\Close;

use App\Enums\AccountingCloseType;
use App\Enums\AccountingPeriodStatus;
use App\Enums\JournalEntryType;
use App\Enums\JournalStatus;
use App\Models\Accounting\AccountingCloseAudit;
use App\Models\Accounting\AccountingPeriod;
use App\Models\Accounting\Journal;
use App\Support\Accounting\JournalPostingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AccountingCloseService
{
    public function __construct(
        protected JournalPostingService $journalPosting,
    ) {}

    /**
     * @param  list<array{gl_account_id: int, description: string, debit: float, credit: float}>  $lines
     */
    public function postCloseJournal(
        AccountingPeriod $period,
        array $lines,
        int $userId,
        JournalEntryType $entryType,
        string $sourceType,
        int $sourceId,
        string $description,
    ): ?Journal {
        if ($lines === []) {
            return null;
        }

        $journal = $this->journalPosting->createDraft([
            'accounting_period_id' => $period->id,
            'journal_date' => $period->end_date->toDateString(),
            'description' => $description,
            'entry_type' => $entryType,
            'source_module' => 'accounting',
            'source_type' => $sourceType,
            'source_id' => $sourceId,
            'reference' => $period->code.'-CLOSE',
        ], $lines, $userId);

        return $this->journalPosting->post($journal, $userId);
    }

    public function activePeriodCloseAudit(AccountingPeriod $period): ?AccountingCloseAudit
    {
        return AccountingCloseAudit::query()
            ->where('accounting_period_id', $period->id)
            ->where('close_type', AccountingCloseType::PeriodClose)
            ->whereNull('reversed_at')
            ->first();
    }

    public function activeYearEndCloseAudit(int $fiscalYearId): ?AccountingCloseAudit
    {
        return AccountingCloseAudit::query()
            ->where('fiscal_year_id', $fiscalYearId)
            ->where('close_type', AccountingCloseType::YearEndClose)
            ->whereNull('reversed_at')
            ->first();
    }

    public function assertNoDraftJournals(AccountingPeriod $period): void
    {
        $exists = Journal::query()
            ->where('company_id', $period->company_id)
            ->where('accounting_period_id', $period->id)
            ->where('status', JournalStatus::Draft)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'period' => __('Post or delete all draft journals before closing this period.'),
            ]);
        }
    }

    public function assertPriorPeriodsClosed(AccountingPeriod $period): void
    {
        $priorOpen = AccountingPeriod::query()
            ->where('fiscal_year_id', $period->fiscal_year_id)
            ->where('period_number', '<', $period->period_number)
            ->where('status', AccountingPeriodStatus::Open)
            ->exists();

        if ($priorOpen) {
            throw ValidationException::withMessages([
                'period' => __('Close earlier periods in this fiscal year before closing :name.', [
                    'name' => $period->name,
                ]),
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $validationSnapshot
     */
    public function recordAudit(
        AccountingPeriod $period,
        AccountingCloseType $type,
        ?Journal $journal,
        float $netAmount,
        int $userId,
        array $validationSnapshot = [],
    ): AccountingCloseAudit {
        return AccountingCloseAudit::query()->create([
            'company_id' => $period->company_id,
            'fiscal_year_id' => $period->fiscal_year_id,
            'accounting_period_id' => $period->id,
            'close_type' => $type,
            'journal_id' => $journal?->id,
            'net_amount' => $netAmount,
            'validation_snapshot' => $validationSnapshot,
            'performed_by' => $userId,
            'performed_at' => now(),
        ]);
    }

    public function markAuditReversed(AccountingCloseAudit $audit, ?Journal $reversalJournal, int $userId): void
    {
        $audit->update([
            'reversed_at' => now(),
            'reversed_by' => $userId,
            'reversal_journal_id' => $reversalJournal?->id,
        ]);
    }
}
