<?php

namespace App\Support\Accounting\Close;

use App\Enums\AccountingCloseType;
use App\Enums\AccountingPeriodStatus;
use App\Enums\FiscalYearStatus;
use App\Enums\JournalEntryType;
use App\Models\Accounting\AccountingCloseAudit;
use App\Models\Accounting\AccountingPeriod;
use App\Models\Accounting\FiscalYear;
use App\Support\Accounting\FiscalYearService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class YearEndCloseService
{
    public function __construct(
        protected AccountingCloseService $close,
        protected CloseJournalGenerator $journalGenerator,
        protected CurrentYearEarningsCalculator $calculator,
        protected FinancialIntegrityService $integrity,
        protected FiscalYearService $fiscalYears,
    ) {}

    /**
     * @return array{fiscal_year: FiscalYear, audit: \App\Models\Accounting\AccountingCloseAudit, journal: \App\Models\Accounting\Journal|null}
     */
    public function closeFiscalYear(FiscalYear $fiscalYear, int $userId): array
    {
        if (! in_array($fiscalYear->status, [FiscalYearStatus::Open, FiscalYearStatus::YearEndPreparation], true)) {
            throw ValidationException::withMessages([
                'fiscal_year' => __('This fiscal year cannot be closed in its current status.'),
            ]);
        }

        if ($this->close->activeYearEndCloseAudit($fiscalYear->id)) {
            throw ValidationException::withMessages([
                'fiscal_year' => __('Year-end close has already been performed for this fiscal year.'),
            ]);
        }

        $openPeriods = $fiscalYear->periods()
            ->where('status', AccountingPeriodStatus::Open)
            ->exists();

        if ($openPeriods) {
            throw ValidationException::withMessages([
                'fiscal_year' => __('All accounting periods must be closed before year-end close.'),
            ]);
        }

        $lastPeriod = $fiscalYear->periods()->orderByDesc('period_number')->first();

        if (! $lastPeriod) {
            throw ValidationException::withMessages([
                'fiscal_year' => __('Fiscal year has no accounting periods.'),
            ]);
        }

        return DB::transaction(function () use ($fiscalYear, $lastPeriod, $userId) {
            $lines = $this->journalGenerator->yearEndCloseLines($fiscalYear);
            $transferAmount = $this->calculator->currentYearEarningsBalance(
                $fiscalYear,
                $fiscalYear->end_date->toDateString(),
            );

            $journal = $this->close->postCloseJournal(
                $lastPeriod,
                $lines,
                $userId,
                JournalEntryType::YearEndClose,
                'fiscal_year_close',
                $fiscalYear->id,
                __('Year-end close — :name', ['name' => $fiscalYear->name]),
            );

            $closedFy = $this->fiscalYears->closeFiscalYear($fiscalYear, $userId);

            $integrity = $this->integrity->buildIntegrityReport(
                $fiscalYear->company_id,
                $fiscalYear->end_date->toDateString(),
            );

            $audit = AccountingCloseAudit::query()->create([
                'company_id' => $fiscalYear->company_id,
                'fiscal_year_id' => $fiscalYear->id,
                'accounting_period_id' => null,
                'close_type' => AccountingCloseType::YearEndClose,
                'journal_id' => $journal?->id,
                'net_amount' => round(abs($transferAmount), 2),
                'validation_snapshot' => ['integrity' => $integrity],
                'performed_by' => $userId,
                'performed_at' => now(),
            ]);

            return [
                'fiscal_year' => $closedFy,
                'audit' => $audit,
                'journal' => $journal,
            ];
        });
    }

    public function reopenFiscalYear(FiscalYear $fiscalYear, int $userId): FiscalYear
    {
        $audit = $this->close->activeYearEndCloseAudit($fiscalYear->id);

        if (! $audit) {
            throw ValidationException::withMessages([
                'fiscal_year' => __('No active year-end close record found.'),
            ]);
        }

        return DB::transaction(function () use ($fiscalYear, $audit, $userId) {
            $reopened = $this->fiscalYears->reopenFiscalYear($fiscalYear);

            $reversalJournal = null;
            if ($audit->journal_id) {
                $original = $audit->journal()->firstOrFail();
                $reversalJournal = app(\App\Support\Accounting\JournalPostingService::class)
                    ->reverse($original, $userId, __('Reopen fiscal year :name', ['name' => $fiscalYear->name]));
            }

            $this->close->markAuditReversed($audit, $reversalJournal, $userId);

            $lastPeriod = $fiscalYear->periods()->orderByDesc('period_number')->first();
            if ($lastPeriod) {
                $this->close->recordAudit(
                    $lastPeriod,
                    AccountingCloseType::YearEndCloseReversal,
                    $reversalJournal,
                    (float) $audit->net_amount * -1,
                    $userId,
                    ['reversed_audit_id' => $audit->id],
                );
            }

            return $reopened->fresh(['periods']);
        });
    }
}
