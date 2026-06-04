<?php

namespace App\Support\Accounting;

use App\Enums\NormalBalance;
use App\Models\Accounting\GlAccount;
use App\Support\Accounting\Reports\PostedJournalQuery;
use Illuminate\Support\Collection;

class TrialBalanceService
{
    /**
     * @param  array{period_id?: int, from_date?: string, to_date?: string}  $filters
     * @return array{
     *     rows: Collection<int, array<string, mixed>>,
     *     total_debit: float,
     *     total_credit: float,
     *     is_balanced: bool
     * }
     */
    public function build(array $filters = []): array
    {
        $companyId = $filters['company_id'] ?? tenant()->companyId();

        if (! $companyId) {
            return ['rows' => collect(), 'total_debit' => 0.0, 'total_credit' => 0.0, 'is_balanced' => true];
        }

        $aggregates = PostedJournalQuery::aggregateByAccount([
            ...$filters,
            'company_id' => $companyId,
        ])->get();

        $aggregates = $aggregates->map(function ($row) {
            $row->period_debit = $row->total_debit;
            $row->period_credit = $row->total_credit;

            return $row;
        });

        $totalDebit = 0.0;
        $totalCredit = 0.0;

        $rows = $aggregates->map(function ($row) use (&$totalDebit, &$totalCredit) {
            $debit = round((float) $row->period_debit, 2);
            $credit = round((float) $row->period_credit, 2);
            $balance = round($debit - $credit, 2);

            $totalDebit += $debit;
            $totalCredit += $credit;

            $normal = NormalBalance::from($row->normal_balance);

            return [
                'account_id' => (int) $row->gl_account_id,
                'account_code' => $row->account_code,
                'account_name' => $row->account_name,
                'normal_balance' => $normal,
                'period_debit' => $debit,
                'period_credit' => $credit,
                'balance' => $balance,
                'debit_balance' => $balance > 0 ? $balance : 0.0,
                'credit_balance' => $balance < 0 ? abs($balance) : 0.0,
            ];
        });

        $totalDebit = round($totalDebit, 2);
        $totalCredit = round($totalCredit, 2);

        return [
            'rows' => $rows,
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'is_balanced' => $totalDebit === $totalCredit,
        ];
    }

    /**
     * Include accounts with zero activity for a complete trial balance.
     *
     * @param  array{period_id?: int, from_date?: string, to_date?: string}  $filters
     */
    public function buildFull(array $filters = []): array
    {
        $result = $this->build($filters);
        $companyId = $filters['company_id'] ?? tenant()->companyId();

        if (! $companyId) {
            return $result;
        }

        $activeIds = $result['rows']->pluck('account_id')->all();

        $accounts = GlAccount::query()
            ->forTenant()
            ->where('is_postable', true)
            ->where('company_id', $companyId)
            ->when($activeIds !== [], fn ($q) => $q->whereNotIn('id', $activeIds))
            ->orderBy('code')
            ->get(['id', 'code', 'name', 'normal_balance']);

        $zeroRows = $accounts->map(fn (GlAccount $account) => [
            'account_id' => $account->id,
            'account_code' => $account->code,
            'account_name' => $account->name,
            'normal_balance' => $account->normal_balance,
            'period_debit' => 0.0,
            'period_credit' => 0.0,
            'balance' => 0.0,
            'debit_balance' => 0.0,
            'credit_balance' => 0.0,
        ]);

        $result['rows'] = $result['rows']->concat($zeroRows)->sortBy('account_code')->values();

        return $result;
    }
}
