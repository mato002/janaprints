<?php

namespace App\Support\Accounting\Reports;

use App\Enums\GlAccountTypeCode;
use App\Enums\NormalBalance;
use Illuminate\Support\Collection;

class ProfitAndLossReportService
{
    /**
     * @param  array{from_date: string, to_date: string, period_id?: int}  $filters
     */
    public function build(array $filters): array
    {
        $aggregates = PostedJournalQuery::aggregateByAccount([
            'company_id' => $filters['company_id'] ?? null,
            'branch_id' => $filters['branch_id'] ?? null,
            'from_date' => $filters['from_date'],
            'to_date' => $filters['to_date'],
            'period_id' => $filters['period_id'] ?? null,
            'account_types' => [
                GlAccountTypeCode::Revenue->value,
                GlAccountTypeCode::CostOfSales->value,
                GlAccountTypeCode::Expense->value,
            ],
        ])->get();

        $sections = [
            GlAccountTypeCode::Revenue->value => $this->emptySection(GlAccountTypeCode::Revenue),
            GlAccountTypeCode::CostOfSales->value => $this->emptySection(GlAccountTypeCode::CostOfSales),
            GlAccountTypeCode::Expense->value => $this->emptySection(GlAccountTypeCode::Expense),
        ];

        foreach ($aggregates as $row) {
            $typeCode = $row->account_type_code;
            $amount = $this->periodAmount(
                (float) $row->total_debit,
                (float) $row->total_credit,
                NormalBalance::from($row->normal_balance),
            );

            if (abs($amount) < 0.005) {
                continue;
            }

            $sections[$typeCode]['accounts'][] = [
                'account_id' => (int) $row->gl_account_id,
                'account_code' => $row->account_code,
                'account_name' => $row->account_name,
                'debit' => round((float) $row->total_debit, 2),
                'credit' => round((float) $row->total_credit, 2),
                'amount' => round($amount, 2),
            ];
            $sections[$typeCode]['total'] += $amount;
        }

        foreach ($sections as $key => $section) {
            $sections[$key]['total'] = round($section['total'], 2);
            $sections[$key]['accounts'] = collect($section['accounts'])->sortBy('account_code')->values()->all();
        }

        $revenue = $sections[GlAccountTypeCode::Revenue->value]['total'];
        $costOfSales = $sections[GlAccountTypeCode::CostOfSales->value]['total'];
        $expenses = $sections[GlAccountTypeCode::Expense->value]['total'];
        $grossProfit = round($revenue - $costOfSales, 2);
        $netProfit = round($grossProfit - $expenses, 2);

        return [
            'from_date' => $filters['from_date'],
            'to_date' => $filters['to_date'],
            'sections' => $sections,
            'total_revenue' => round($revenue, 2),
            'total_cost_of_sales' => round($costOfSales, 2),
            'gross_profit' => $grossProfit,
            'total_expenses' => round($expenses, 2),
            'net_profit' => $netProfit,
        ];
    }

    protected function emptySection(GlAccountTypeCode $type): array
    {
        return [
            'label' => $type->label(),
            'type' => $type->value,
            'accounts' => [],
            'total' => 0.0,
        ];
    }

    /**
     * P&L display amount: credits positive for revenue, debits positive for expenses.
     */
    protected function periodAmount(float $debit, float $credit, NormalBalance $normal): float
    {
        return match ($normal) {
            NormalBalance::Credit => round($credit - $debit, 2),
            NormalBalance::Debit => round($debit - $credit, 2),
        };
    }
}
