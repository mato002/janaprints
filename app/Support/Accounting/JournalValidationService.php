<?php

namespace App\Support\Accounting;

use App\Enums\AccountingPeriodStatus;
use App\Enums\FiscalYearStatus;
use App\Enums\GlAccountStatus;
use App\Enums\JournalEntryType;
use App\Enums\JournalStatus;
use App\Models\Accounting\AccountingPeriod;
use App\Models\Accounting\GlAccount;
use App\Models\Accounting\Journal;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class JournalValidationService
{
    /**
     * @param  array<int, array{gl_account_id: int, description?: string|null, debit: float|string, credit: float|string}>  $lines
     */
    public function validateDraft(array $lines, AccountingPeriod $period, string $journalDate, ?JournalEntryType $entryType = null): array
    {
        $this->assertPeriodAcceptsPosting($period, $journalDate, $entryType);
        $normalized = $this->normalizeLines($lines);
        $this->assertMinimumLines($normalized);
        $this->assertAccountsValid($normalized, $period->company_id);
        $totals = $this->calculateTotals($normalized);
        $this->assertBalanced($totals);

        return [
            'lines' => $normalized,
            'total_debit' => $totals['debit'],
            'total_credit' => $totals['credit'],
        ];
    }

    public function assertCanPost(Journal $journal): void
    {
        if ($journal->status !== JournalStatus::Draft) {
            throw ValidationException::withMessages([
                'journal' => __('Only draft journals can be posted.'),
            ]);
        }

        if (! $journal->isBalanced()) {
            throw ValidationException::withMessages([
                'journal' => __('Journal debits must equal credits before posting.'),
            ]);
        }

        if ($journal->lines()->count() < 2) {
            throw ValidationException::withMessages([
                'journal' => __('A journal must have at least two lines.'),
            ]);
        }

        $period = $journal->accountingPeriod;
        $this->assertPeriodAcceptsPosting($period, $journal->journal_date->toDateString(), $journal->entry_type);

        $journal->loadMissing('lines.glAccount');

        foreach ($journal->lines as $line) {
            $this->assertAccountPostable($line->glAccount, $journal->company_id);
        }
    }

    public function assertCanReverse(Journal $journal): void
    {
        if ($journal->status !== JournalStatus::Posted) {
            throw ValidationException::withMessages([
                'journal' => __('Only posted journals can be reversed.'),
            ]);
        }

        if ($journal->reversed_by_journal_id !== null) {
            throw ValidationException::withMessages([
                'journal' => __('This journal has already been reversed.'),
            ]);
        }

        if ($journal->entry_type === \App\Enums\JournalEntryType::Reversal) {
            throw ValidationException::withMessages([
                'journal' => __('Reversal journals cannot be reversed again.'),
            ]);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $lines
     * @return list<array{gl_account_id: int, description: ?string, debit: float, credit: float}>
     */
    public function normalizeLines(array $lines): array
    {
        $normalized = [];

        foreach ($lines as $line) {
            $debit = round((float) ($line['debit'] ?? 0), 2);
            $credit = round((float) ($line['credit'] ?? 0), 2);

            if ($debit <= 0 && $credit <= 0) {
                continue;
            }

            if ($debit > 0 && $credit > 0) {
                throw ValidationException::withMessages([
                    'lines' => __('Each line must have either a debit or a credit, not both.'),
                ]);
            }

            $normalized[] = [
                'gl_account_id' => (int) $line['gl_account_id'],
                'description' => $line['description'] ?? null,
                'debit' => $debit,
                'credit' => $credit,
            ];
        }

        return $normalized;
    }

    /**
     * @param  list<array{debit: float, credit: float}>  $lines
     * @return array{debit: float, credit: float}
     */
    public function calculateTotals(array $lines): array
    {
        $debit = 0.0;
        $credit = 0.0;

        foreach ($lines as $line) {
            $debit += $line['debit'];
            $credit += $line['credit'];
        }

        return [
            'debit' => round($debit, 2),
            'credit' => round($credit, 2),
        ];
    }

    /**
     * @param  list<array{gl_account_id: int}>  $lines
     */
    protected function assertMinimumLines(array $lines): void
    {
        if (count($lines) < 2) {
            throw ValidationException::withMessages([
                'lines' => __('At least two journal lines with amounts are required.'),
            ]);
        }
    }

    /**
     * @param  list<array{gl_account_id: int}>  $lines
     */
    protected function assertAccountsValid(array $lines, int $companyId): void
    {
        $accountIds = collect($lines)->pluck('gl_account_id')->unique();

        $accounts = GlAccount::query()
            ->where('company_id', $companyId)
            ->whereIn('id', $accountIds)
            ->get()
            ->keyBy('id');

        if ($accounts->count() !== $accountIds->count()) {
            throw ValidationException::withMessages([
                'lines' => __('One or more accounts are invalid for this company.'),
            ]);
        }

        foreach ($accounts as $account) {
            $this->assertAccountPostable($account, $companyId);
        }
    }

    protected function assertAccountPostable(GlAccount $account, int $companyId): void
    {
        if ((int) $account->company_id !== $companyId) {
            throw ValidationException::withMessages([
                'lines' => __('Account :code does not belong to this company.', ['code' => $account->code]),
            ]);
        }

        if (! $account->is_postable) {
            throw ValidationException::withMessages([
                'lines' => __('Account :code is not postable.', ['code' => $account->code]),
            ]);
        }

        if ($account->status !== GlAccountStatus::Active) {
            throw ValidationException::withMessages([
                'lines' => __('Account :code is not active.', ['code' => $account->code]),
            ]);
        }
    }

    /**
     * @param  array{debit: float, credit: float}  $totals
     */
    protected function assertBalanced(array $totals): void
    {
        if ($totals['debit'] !== $totals['credit']) {
            throw ValidationException::withMessages([
                'lines' => __('Total debits (:debit) must equal total credits (:credit).', [
                    'debit' => number_format($totals['debit'], 2),
                    'credit' => number_format($totals['credit'], 2),
                ]),
            ]);
        }

        if ($totals['debit'] <= 0) {
            throw ValidationException::withMessages([
                'lines' => __('Journal amounts must be greater than zero.'),
            ]);
        }
    }

    protected function assertPeriodAcceptsPosting(AccountingPeriod $period, string $journalDate, ?JournalEntryType $entryType = null): void
    {
        $fiscalYear = $period->fiscalYear;

        $fyAllowsPosting = in_array($fiscalYear->status, [
            FiscalYearStatus::Open,
            FiscalYearStatus::YearEndPreparation,
        ], true);

        if (! $fyAllowsPosting) {
            throw ValidationException::withMessages([
                'accounting_period_id' => __('The fiscal year is not open for posting.'),
            ]);
        }

        $yearEndInClosedPeriod = $entryType === JournalEntryType::YearEndClose
            && $period->status === AccountingPeriodStatus::Closed;

        if ($period->status !== AccountingPeriodStatus::Open && ! $yearEndInClosedPeriod) {
            throw ValidationException::withMessages([
                'accounting_period_id' => __('The accounting period is not open for posting.'),
            ]);
        }

        if ($journalDate < $period->start_date->toDateString() || $journalDate > $period->end_date->toDateString()) {
            throw ValidationException::withMessages([
                'journal_date' => __('Journal date must fall within the selected accounting period.'),
            ]);
        }
    }
}
