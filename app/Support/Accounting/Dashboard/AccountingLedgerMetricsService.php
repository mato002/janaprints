<?php

namespace App\Support\Accounting\Dashboard;

use App\Enums\GlAccountTypeCode;
use App\Enums\NormalBalance;
use App\Models\Accounting\AccountingPeriod;
use App\Models\Accounting\FiscalYear;
use App\Support\Accounting\LedgerSignedBalance;
use App\Support\Accounting\Reports\PostedJournalQuery;
use App\Support\Accounting\Reports\ProfitAndLossReportService;
use Illuminate\Support\Collection;

class AccountingLedgerMetricsService
{
    public function __construct(
        protected ProfitAndLossReportService $profitAndLoss,
    ) {}

    /**
     * @param  array{company_id: int, branch_id?: int|null, period_id?: int|null}  $filters
     * @return array<string, mixed>
     */
    public function build(array $filters): array
    {
        $context = $this->resolvePeriodContext($filters);
        $ledgerFilters = [
            'company_id' => $filters['company_id'],
            'branch_id' => $filters['branch_id'] ?? null,
            'as_of_date' => $context['as_of_date'],
            'period_id' => $context['period_id'],
        ];

        $balances = $this->balancesByAccountCode($ledgerFilters);
        $accountCodes = config('accounting_dashboard.accounts', []);

        $mtdPl = $this->profitAndLoss->build([
            'company_id' => $filters['company_id'],
            'branch_id' => $filters['branch_id'] ?? null,
            'from_date' => $context['mtd_from'],
            'to_date' => $context['mtd_to'],
            'period_id' => $context['period_id'],
        ]);

        $ytdPl = $this->profitAndLoss->build([
            'company_id' => $filters['company_id'],
            'branch_id' => $filters['branch_id'] ?? null,
            'from_date' => $context['ytd_from'],
            'to_date' => $context['ytd_to'],
        ]);

        return [
            'period' => $context,
            'cards' => [
                'cash_position' => $this->sumAccountCodes($balances, $accountCodes['cash'] ?? []),
                'accounts_receivable' => $this->sumAccountCodes($balances, $accountCodes['receivables'] ?? []),
                'accounts_payable' => $this->sumAccountCodes($balances, $accountCodes['payables'] ?? []),
                'revenue_mtd' => $mtdPl['total_revenue'],
                'revenue_ytd' => $ytdPl['total_revenue'],
                'expenses_mtd' => round($mtdPl['total_cost_of_sales'] + $mtdPl['total_expenses'], 2),
                'net_profit_mtd' => $mtdPl['net_profit'],
            ],
            'charts' => $this->buildTrendCharts($filters, $context),
        ];
    }

    /**
     * @param  array{company_id: int, branch_id?: int|null, period_id?: int|null}  $filters
     * @return array<string, mixed>
     */
    protected function resolvePeriodContext(array $filters): array
    {
        $period = null;

        if (! empty($filters['period_id'])) {
            $period = AccountingPeriod::query()
                ->with('fiscalYear')
                ->where('company_id', $filters['company_id'])
                ->find($filters['period_id']);
        }

        if (! $period) {
            $period = AccountingPeriod::query()
                ->with('fiscalYear')
                ->where('company_id', $filters['company_id'])
                ->where('is_current', true)
                ->first();
        }

        $asOf = $period?->end_date?->toDateString() ?? now()->toDateString();
        $mtdFrom = $period?->start_date?->toDateString() ?? now()->startOfMonth()->toDateString();
        $mtdTo = $period?->end_date?->toDateString() ?? now()->toDateString();

        $fiscalYear = $period?->fiscalYear
            ?? FiscalYear::query()
                ->where('company_id', $filters['company_id'])
                ->where('is_current', true)
                ->first();

        $ytdFrom = $fiscalYear?->start_date?->toDateString() ?? now()->startOfYear()->toDateString();

        return [
            'period_id' => $period?->id,
            'period_code' => $period?->code,
            'period_name' => $period?->name,
            'as_of_date' => $asOf,
            'mtd_from' => $mtdFrom,
            'mtd_to' => $mtdTo,
            'ytd_from' => $ytdFrom,
            'ytd_to' => $mtdTo,
        ];
    }

    /**
     * @param  array<string, mixed>  $ledgerFilters
     * @return array<string, array{balance: float, normal_balance: NormalBalance}>
     */
    protected function balancesByAccountCode(array $ledgerFilters): array
    {
        $rows = PostedJournalQuery::aggregateByAccount($ledgerFilters)->get();
        $map = [];

        foreach ($rows as $row) {
            $normal = NormalBalance::from($row->normal_balance);
            $map[$row->account_code] = [
                'balance' => LedgerSignedBalance::balanceSheetAmount(
                    (float) $row->total_debit,
                    (float) $row->total_credit,
                    $normal,
                ),
                'normal_balance' => $normal,
            ];
        }

        return $map;
    }

    /**
     * @param  array<string, array{balance: float, normal_balance: NormalBalance}>  $balances
     * @param  list<string>  $codes
     */
    protected function sumAccountCodes(array $balances, array $codes): float
    {
        $total = 0.0;

        foreach ($codes as $code) {
            $total += $balances[$code]['balance'] ?? 0.0;
        }

        return round($total, 2);
    }

    /**
     * @param  array{company_id: int, branch_id?: int|null}  $filters
     * @param  array<string, mixed>  $context
     * @return array<string, list<array{label: string, value: float, percent: int}>>
     */
    protected function buildTrendCharts(array $filters, array $context): array
    {
        $months = $this->trendMonthKeys($context['ytd_from'], $context['mtd_to']);
        $trendFilters = [
            'company_id' => $filters['company_id'],
            'branch_id' => $filters['branch_id'] ?? null,
            'from_date' => $months[0].'-01',
            'to_date' => $context['mtd_to'],
        ];

        $revenueByMonth = [];
        $expenseByMonth = [];

        foreach (PostedJournalQuery::monthlyTotalsByAccountType($trendFilters)->get() as $row) {
            $key = $row->month_key;
            $amount = $this->typePeriodAmount($row->account_type_code, (float) $row->total_debit, (float) $row->total_credit);

            if ($row->account_type_code === GlAccountTypeCode::Revenue->value) {
                $revenueByMonth[$key] = ($revenueByMonth[$key] ?? 0) + $amount;
            } elseif (in_array($row->account_type_code, [
                GlAccountTypeCode::Expense->value,
                GlAccountTypeCode::CostOfSales->value,
            ], true)) {
                $expenseByMonth[$key] = ($expenseByMonth[$key] ?? 0) + $amount;
            }
        }

        $cashCodes = config('accounting_dashboard.accounts.cash', []);
        $cashByMonth = PostedJournalQuery::monthlyTotalsForAccountCodes($trendFilters, $cashCodes)
            ->get()
            ->keyBy('month_key')
            ->map(fn ($row) => round((float) $row->total_debit - (float) $row->total_credit, 2));

        return [
            'revenue_trend' => $this->normalizeChart($months, $revenueByMonth),
            'expense_trend' => $this->normalizeChart($months, $expenseByMonth),
            'cash_flow_trend' => $this->normalizeChart($months, $cashByMonth->all()),
        ];
    }

    protected function typePeriodAmount(string $typeCode, float $debit, float $credit): float
    {
        return match ($typeCode) {
            GlAccountTypeCode::Revenue->value => LedgerSignedBalance::profitAndLossAmount($debit, $credit, NormalBalance::Credit),
            GlAccountTypeCode::CostOfSales->value,
            GlAccountTypeCode::Expense->value => LedgerSignedBalance::profitAndLossAmount($debit, $credit, NormalBalance::Debit),
            default => 0.0,
        };
    }

    /**
     * @return list<string>
     */
    protected function trendMonthKeys(string $fromDate, string $toDate): array
    {
        $count = (int) config('accounting_dashboard.trend_months', 6);
        $end = \Illuminate\Support\Carbon::parse($toDate)->startOfMonth();
        $start = \Illuminate\Support\Carbon::parse($fromDate)->startOfMonth();
        $earliest = $end->copy()->subMonths($count - 1);

        if ($start->gt($earliest)) {
            $earliest = $start;
        }

        $months = [];
        $cursor = $earliest->copy();

        while ($cursor->lte($end)) {
            $months[] = $cursor->format('Y-m');
            $cursor->addMonth();
        }

        return $months;
    }

    /**
     * @param  list<string>  $months
     * @param  array<string, float>  $values
     * @return list<array{label: string, value: float, percent: int}>
     */
    protected function normalizeChart(array $months, array $values): array
    {
        $points = collect($months)->map(fn (string $month) => [
            'label' => \Illuminate\Support\Carbon::createFromFormat('Y-m', $month)->format('M Y'),
            'value' => round((float) ($values[$month] ?? 0), 2),
        ])->all();

        $max = max(1.0, ...array_column($points, 'value'));

        foreach ($points as &$point) {
            $point['percent'] = (int) round(($point['value'] / $max) * 100);
        }
        unset($point);

        return $points;
    }
}
