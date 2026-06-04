<?php

namespace App\Support\Accounting\Reports;

use App\Enums\GlAccountTypeCode;
use App\Enums\NormalBalance;
use Illuminate\Support\Collection;

class BalanceSheetReportService
{
    /**
     * @param  array{as_of_date: string, period_id?: int}  $filters
     */
    public function build(array $filters): array
    {
        $asOf = $filters['as_of_date'] ?? now()->toDateString();

        $aggregates = PostedJournalQuery::aggregateByAccount([
            'company_id' => $filters['company_id'] ?? null,
            'branch_id' => $filters['branch_id'] ?? null,
            'as_of_date' => $asOf,
            'period_id' => $filters['period_id'] ?? null,
            'account_types' => [
                GlAccountTypeCode::Asset->value,
                GlAccountTypeCode::Liability->value,
                GlAccountTypeCode::Equity->value,
            ],
        ])->get();

        $sections = [
            GlAccountTypeCode::Asset->value => $this->emptySection(GlAccountTypeCode::Asset),
            GlAccountTypeCode::Liability->value => $this->emptySection(GlAccountTypeCode::Liability),
            GlAccountTypeCode::Equity->value => $this->emptySection(GlAccountTypeCode::Equity),
        ];

        foreach ($aggregates as $row) {
            $typeCode = $row->account_type_code;
            $balance = $this->signedBalance(
                (float) $row->total_debit,
                (float) $row->total_credit,
                NormalBalance::from($row->normal_balance),
            );

            if (abs($balance) < 0.005) {
                continue;
            }

            $sections[$typeCode]['accounts'][] = [
                'account_id' => (int) $row->gl_account_id,
                'account_code' => $row->account_code,
                'account_name' => $row->account_name,
                'debit' => round((float) $row->total_debit, 2),
                'credit' => round((float) $row->total_credit, 2),
                'balance' => round($balance, 2),
            ];
            $sections[$typeCode]['total'] += $balance;
        }

        foreach ($sections as $key => $section) {
            $sections[$key]['total'] = round($section['total'], 2);
            $sections[$key]['accounts'] = collect($section['accounts'])->sortBy('account_code')->values()->all();
        }

        $totalAssets = $sections[GlAccountTypeCode::Asset->value]['total'];
        $totalLiabilities = $sections[GlAccountTypeCode::Liability->value]['total'];
        $totalEquity = $sections[GlAccountTypeCode::Equity->value]['total'];
        $totalLiabilitiesEquity = round($totalLiabilities + $totalEquity, 2);

        return [
            'as_of_date' => $asOf,
            'sections' => $sections,
            'total_assets' => round($totalAssets, 2),
            'total_liabilities' => round($totalLiabilities, 2),
            'total_equity' => round($totalEquity, 2),
            'total_liabilities_and_equity' => $totalLiabilitiesEquity,
            'is_balanced' => abs($totalAssets - $totalLiabilitiesEquity) < 0.02,
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

    protected function signedBalance(float $debit, float $credit, NormalBalance $normal): float
    {
        $raw = round($debit - $credit, 2);

        return match ($normal) {
            NormalBalance::Debit => $raw,
            NormalBalance::Credit => -$raw,
        };
    }
}
