<?php

namespace App\Support\Accounting\Close;

use App\Enums\NormalBalance;
use App\Models\Accounting\AccountingPeriod;
use App\Models\Accounting\FiscalYear;
use App\Models\Accounting\GlAccount;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class CloseJournalGenerator
{
    public function __construct(
        protected CurrentYearEarningsCalculator $calculator,
    ) {}

    /**
     * Close revenue, COS, and expense accounts into Current Year Earnings (3300).
     *
     * @return list<array{gl_account_id: int, description: string, debit: float, credit: float}>
     */
    public function periodCloseLines(AccountingPeriod $period): array
    {
        $balances = $this->calculator->periodProfitAndLossBalances($period);
        $earnings = $this->calculator->resolveEquityAccount($period->company_id, 'current_year_earnings');

        if ($balances->isEmpty()) {
            return [];
        }

        $lines = [];

        foreach ($balances as $row) {
            $lines = array_merge($lines, $this->closeProfitAndLossAccount(
                $row['account'],
                $row['amount'],
                $row['normal_balance'],
                $earnings,
            ));
        }

        $this->assertBalanced($lines);

        return $lines;
    }

    /**
     * Transfer Current Year Earnings balance to Retained Earnings (3200).
     *
     * @return list<array{gl_account_id: int, description: string, debit: float, credit: float}>
     */
    public function yearEndCloseLines(FiscalYear $fiscalYear): array
    {
        $asOf = $fiscalYear->end_date->toDateString();
        $balance = $this->calculator->currentYearEarningsBalance($fiscalYear, $asOf);

        if (abs($balance) < 0.005) {
            return [];
        }

        $currentYear = $this->calculator->resolveEquityAccount($fiscalYear->company_id, 'current_year_earnings');
        $retained = $this->calculator->resolveEquityAccount($fiscalYear->company_id, 'retained_earnings');
        $amount = round(abs($balance), 2);

        if ($balance > 0) {
            return [
                $this->line($currentYear->id, __('Transfer to retained earnings'), $amount, 0),
                $this->line($retained->id, __('Transfer from current year earnings'), 0, $amount),
            ];
        }

        return [
            $this->line($retained->id, __('Transfer deficit from current year earnings'), $amount, 0),
            $this->line($currentYear->id, __('Clear current year earnings deficit'), 0, $amount),
        ];
    }

    /**
     * @return list<array{gl_account_id: int, description: string, debit: float, credit: float}>
     */
    protected function closeProfitAndLossAccount(
        GlAccount $account,
        float $amount,
        NormalBalance $normal,
        GlAccount $earnings,
    ): array {
        $value = round(abs($amount), 2);

        return match ($normal) {
            NormalBalance::Credit => [
                $this->line($account->id, __('Period close — :account', ['account' => $account->name]), $value, 0),
                $this->line($earnings->id, __('Period close — :account', ['account' => $account->name]), 0, $value),
            ],
            NormalBalance::Debit => [
                $this->line($earnings->id, __('Period close — :account', ['account' => $account->name]), $value, 0),
                $this->line($account->id, __('Period close — :account', ['account' => $account->name]), 0, $value),
            ],
        };
    }

    /**
     * @param  list<array{debit: float, credit: float}>  $lines
     */
    protected function assertBalanced(array $lines): void
    {
        $debit = round(array_sum(array_column($lines, 'debit')), 2);
        $credit = round(array_sum(array_column($lines, 'credit')), 2);

        if ($debit !== $credit || $debit <= 0) {
            throw ValidationException::withMessages([
                'close' => __('Generated close journal is not balanced.'),
            ]);
        }
    }

    /**
     * @return array{gl_account_id: int, description: string, debit: float, credit: float}
     */
    protected function line(int $accountId, string $description, float $debit, float $credit): array
    {
        return [
            'gl_account_id' => $accountId,
            'description' => $description,
            'debit' => round($debit, 2),
            'credit' => round($credit, 2),
        ];
    }
}
