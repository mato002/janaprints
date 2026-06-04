<?php

namespace App\Support\Accounting\Close;

use App\Models\Accounting\AccountingPeriod;
use App\Support\Accounting\Reports\BalanceSheetReportService;
use App\Support\Accounting\Reports\ProfitAndLossReportService;
use App\Support\Accounting\TrialBalanceService;
use Illuminate\Validation\ValidationException;

class FinancialIntegrityService
{
    public function __construct(
        protected TrialBalanceService $trialBalance,
        protected BalanceSheetReportService $balanceSheet,
        protected ProfitAndLossReportService $profitAndLoss,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function validateForPeriodClose(AccountingPeriod $period): array
    {
        $tbFilters = [
            'company_id' => $period->company_id,
            'period_id' => $period->id,
            'from_date' => $period->start_date->toDateString(),
            'to_date' => $period->end_date->toDateString(),
        ];

        $trialBalance = $this->trialBalance->build($tbFilters);

        if (! $trialBalance['is_balanced']) {
            throw ValidationException::withMessages([
                'integrity' => __('Trial balance is not balanced for this period (debits :debit, credits :credit).', [
                    'debit' => $trialBalance['total_debit'],
                    'credit' => $trialBalance['total_credit'],
                ]),
            ]);
        }

        $balanceSheet = $this->balanceSheet->build([
            'company_id' => $period->company_id,
            'as_of_date' => $period->end_date->toDateString(),
        ]);

        $snapshot = [
            'trial_balance' => [
                'total_debit' => $trialBalance['total_debit'],
                'total_credit' => $trialBalance['total_credit'],
                'is_balanced' => $trialBalance['is_balanced'],
            ],
            'balance_sheet' => [
                'total_assets' => $balanceSheet['total_assets'],
                'total_liabilities' => $balanceSheet['total_liabilities'],
                'total_equity' => $balanceSheet['total_equity'],
                'total_liabilities_and_equity' => $balanceSheet['total_liabilities_and_equity'],
                'is_balanced' => $balanceSheet['is_balanced'],
            ],
        ];

        return $snapshot;
    }

    /**
     * @return array<string, mixed>
     */
    public function validateAfterPeriodClose(AccountingPeriod $period): array
    {
        $balanceSheet = $this->balanceSheet->build([
            'company_id' => $period->company_id,
            'as_of_date' => $period->end_date->toDateString(),
        ]);

        if (! $balanceSheet['is_balanced']) {
            throw ValidationException::withMessages([
                'integrity' => __('Balance sheet does not balance after period close (Assets :assets ≠ Liabilities + Equity :le).', [
                    'assets' => $balanceSheet['total_assets'],
                    'le' => $balanceSheet['total_liabilities_and_equity'],
                ]),
            ]);
        }

        $pl = $this->profitAndLoss->build([
            'company_id' => $period->company_id,
            'from_date' => $period->start_date->toDateString(),
            'to_date' => $period->end_date->toDateString(),
            'period_id' => $period->id,
        ]);

        return [
            'balance_sheet' => $balanceSheet,
            'profit_and_loss' => [
                'total_revenue' => $pl['total_revenue'],
                'total_cost_of_sales' => $pl['total_cost_of_sales'],
                'total_expenses' => $pl['total_expenses'],
                'net_profit' => $pl['net_profit'],
            ],
            'pl_reset' => abs($pl['net_profit']) < 0.02
                && abs($pl['total_revenue']) < 0.02
                && abs($pl['total_cost_of_sales']) < 0.02
                && abs($pl['total_expenses']) < 0.02,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function buildIntegrityReport(int $companyId, string $asOfDate, ?int $periodId = null): array
    {
        $tb = $this->trialBalance->build(array_filter([
            'company_id' => $companyId,
            'period_id' => $periodId,
            'to_date' => $asOfDate,
        ]));

        $bs = $this->balanceSheet->build([
            'company_id' => $companyId,
            'as_of_date' => $asOfDate,
        ]);

        return [
            'as_of_date' => $asOfDate,
            'trial_balance_balanced' => $tb['is_balanced'],
            'trial_balance_debit' => $tb['total_debit'],
            'trial_balance_credit' => $tb['total_credit'],
            'balance_sheet_balanced' => $bs['is_balanced'],
            'total_assets' => $bs['total_assets'],
            'total_liabilities_and_equity' => $bs['total_liabilities_and_equity'],
            'variance' => round($bs['total_assets'] - $bs['total_liabilities_and_equity'], 2),
        ];
    }
}
