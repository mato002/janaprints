<?php

namespace App\Support\Accounting\Close;

use App\Enums\AccountingCloseType;
use App\Enums\AccountingPeriodStatus;
use App\Enums\JournalEntryType;
use App\Models\Accounting\AccountingPeriod;
use App\Support\Accounting\AccountingPeriodService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PeriodCloseService
{
    public function __construct(
        protected AccountingCloseService $close,
        protected CloseJournalGenerator $journalGenerator,
        protected CurrentYearEarningsCalculator $calculator,
        protected FinancialIntegrityService $integrity,
        protected AccountingPeriodService $periods,
    ) {}

    /**
     * @return array{period: AccountingPeriod, audit: \App\Models\Accounting\AccountingCloseAudit, journal: \App\Models\Accounting\Journal|null}
     */
    public function close(AccountingPeriod $period, int $userId): array
    {
        if ($period->status !== AccountingPeriodStatus::Open) {
            throw ValidationException::withMessages([
                'period' => __('Only open periods can be closed.'),
            ]);
        }

        if ($this->close->activePeriodCloseAudit($period)) {
            throw ValidationException::withMessages([
                'period' => __('This period already has an active close entry.'),
            ]);
        }

        $this->close->assertPriorPeriodsClosed($period);
        $this->close->assertNoDraftJournals($period);

        return DB::transaction(function () use ($period, $userId) {
            $preCloseSnapshot = $this->integrity->validateForPeriodClose($period);
            $netIncome = $this->calculator->periodNetIncome($period);
            $lines = $this->journalGenerator->periodCloseLines($period);

            $journal = $this->close->postCloseJournal(
                $period,
                $lines,
                $userId,
                JournalEntryType::PeriodClose,
                'accounting_period_close',
                $period->id,
                __('Period close — :name', ['name' => $period->name]),
            );

            $closedPeriod = $this->periods->close($period, $userId);

            $postCloseSnapshot = $this->integrity->validateAfterPeriodClose($closedPeriod);

            $audit = $this->close->recordAudit(
                $closedPeriod,
                AccountingCloseType::PeriodClose,
                $journal,
                $netIncome,
                $userId,
                [
                    'pre_close' => $preCloseSnapshot,
                    'post_close' => $postCloseSnapshot,
                ],
            );

            return [
                'period' => $closedPeriod,
                'audit' => $audit,
                'journal' => $journal,
            ];
        });
    }

    /**
     * @return array{period: AccountingPeriod, audit: \App\Models\Accounting\AccountingCloseAudit}
     */
    public function reopen(AccountingPeriod $period, int $userId): array
    {
        if ($period->status !== AccountingPeriodStatus::Closed) {
            throw ValidationException::withMessages([
                'period' => __('Only closed periods can be reopened.'),
            ]);
        }

        $audit = $this->close->activePeriodCloseAudit($period);

        if (! $audit) {
            throw ValidationException::withMessages([
                'period' => __('No active period close record found for this period.'),
            ]);
        }

        $laterClosed = AccountingPeriod::query()
            ->where('fiscal_year_id', $period->fiscal_year_id)
            ->where('period_number', '>', $period->period_number)
            ->whereIn('status', [AccountingPeriodStatus::Closed, AccountingPeriodStatus::Locked])
            ->exists();

        if ($laterClosed) {
            throw ValidationException::withMessages([
                'period' => __('Reopen later periods before reopening :name.', ['name' => $period->name]),
            ]);
        }

        return DB::transaction(function () use ($period, $audit, $userId) {
            $opened = $this->periods->reopen($period);

            $reversalJournal = null;
            if ($audit->journal_id) {
                $original = $audit->journal()->firstOrFail();
                $reversalJournal = app(\App\Support\Accounting\JournalPostingService::class)
                    ->reverse($original, $userId, __('Reopen period :name', ['name' => $period->name]));
            }

            $this->close->markAuditReversed($audit, $reversalJournal, $userId);

            $this->close->recordAudit(
                $opened,
                AccountingCloseType::PeriodCloseReversal,
                $reversalJournal,
                (float) $audit->net_amount * -1,
                $userId,
                ['reversed_audit_id' => $audit->id],
            );

            return ['period' => $opened->fresh(), 'audit' => $audit->fresh()];
        });
    }
}
