<?php

namespace App\Support\Accounting\Close;

use App\Enums\GlAccountTypeCode;
use App\Enums\NormalBalance;
use App\Models\Accounting\AccountingPeriod;
use App\Models\Accounting\FiscalYear;
use App\Models\Accounting\GlAccount;
use App\Support\Accounting\LedgerSignedBalance;
use App\Support\Accounting\Reports\PostedJournalQuery;
use Illuminate\Support\Collection;

class CurrentYearEarningsCalculator
{
    /**
     * P&L account balances for a single accounting period (activity in that period only).
     *
     * @return Collection<int, array{account: GlAccount, amount: float, normal_balance: NormalBalance}>
     */
    public function periodProfitAndLossBalances(AccountingPeriod $period): Collection
    {
        return $this->aggregateBalances([
            'company_id' => $period->company_id,
            'period_id' => $period->id,
            'from_date' => $period->start_date->toDateString(),
            'to_date' => $period->end_date->toDateString(),
        ]);
    }

    /**
     * Signed balance on Current Year Earnings (3300) as of a date within the fiscal year.
     */
    public function currentYearEarningsBalance(FiscalYear $fiscalYear, string $asOfDate): float
    {
        $account = $this->resolveEquityAccount($fiscalYear->company_id, 'current_year_earnings');

        $row = PostedJournalQuery::applyFilters(
            PostedJournalQuery::base($fiscalYear->company_id),
            [
                'account_id' => $account->id,
                'from_date' => $fiscalYear->start_date->toDateString(),
                'to_date' => $asOfDate,
            ],
        )
            ->selectRaw('COALESCE(SUM(journal_lines.debit), 0) as total_debit')
            ->selectRaw('COALESCE(SUM(journal_lines.credit), 0) as total_credit')
            ->first();

        if (! $row) {
            return 0.0;
        }

        return LedgerSignedBalance::balanceSheetAmount(
            (float) $row->total_debit,
            (float) $row->total_credit,
            $account->normal_balance,
        );
    }

    /**
     * Net profit for period activity (revenue − COS − expenses).
     */
    public function periodNetIncome(AccountingPeriod $period): float
    {
        $total = 0.0;

        foreach ($this->periodProfitAndLossBalances($period) as $row) {
            $total += $row['amount'];
        }

        return round($total, 2);
    }

    /**
     * @param  array{company_id: int, period_id?: int, from_date: string, to_date: string}  $filters
     * @return Collection<int, array{account: GlAccount, amount: float, normal_balance: NormalBalance}>
     */
    protected function aggregateBalances(array $filters): Collection
    {
        $typeCodes = config('accounting_close.pl_account_types', []);
        $rows = PostedJournalQuery::aggregateByAccount([
            ...$filters,
            'account_types' => $typeCodes,
        ])->get();

        $accounts = GlAccount::query()
            ->where('company_id', $filters['company_id'])
            ->whereIn('id', $rows->pluck('gl_account_id'))
            ->get()
            ->keyBy('id');

        return $rows->map(function ($row) use ($accounts) {
            $account = $accounts->get($row->gl_account_id);
            if (! $account) {
                return null;
            }

            $normal = NormalBalance::from($row->normal_balance);
            $amount = $this->profitAndLossAmount(
                (float) $row->total_debit,
                (float) $row->total_credit,
                $normal,
            );

            if (abs($amount) < 0.005) {
                return null;
            }

            return [
                'account' => $account,
                'amount' => round($amount, 2),
                'normal_balance' => $normal,
                'type_code' => $row->account_type_code,
            ];
        })->filter()->values();
    }

    protected function profitAndLossAmount(float $debit, float $credit, NormalBalance $normal): float
    {
        return match ($normal) {
            NormalBalance::Credit => round($credit - $debit, 2),
            NormalBalance::Debit => round($debit - $credit, 2),
        };
    }

    public function resolveEquityAccount(int $companyId, string $key): GlAccount
    {
        $code = config("accounting_close.accounts.{$key}");

        return GlAccount::query()
            ->where('company_id', $companyId)
            ->where('code', $code)
            ->where('is_postable', true)
            ->firstOrFail();
    }

    /**
     * @return list<GlAccountTypeCode>
     */
    public static function profitAndLossTypeCodes(): array
    {
        return [
            GlAccountTypeCode::Revenue,
            GlAccountTypeCode::CostOfSales,
            GlAccountTypeCode::Expense,
        ];
    }
}
