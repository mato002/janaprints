<?php

namespace App\Support\Accounting\Reports;

use App\Enums\GlAccountTypeCode;
use App\Enums\NormalBalance;
use App\Support\Accounting\LedgerSignedBalance;
use Illuminate\Support\Facades\DB;

class CashFlowStatementReportService
{
    /**
     * @param  array{from_date: string, to_date: string, period_id?: int, company_id?: int, branch_id?: int}  $filters
     */
    public function build(array $filters): array
    {
        $companyId = $filters['company_id'] ?? tenant()->companyId();
        $cashCodes = config('accounting_dashboard.accounts.cash', ['1110', '1120', '1210', '1220', '1230']);
        $fromDate = $filters['from_date'];
        $toDate = $filters['to_date'];
        $dayBefore = \Carbon\Carbon::parse($fromDate)->subDay()->toDateString();

        $openingCash = $this->cashBalanceAsOf($companyId, $cashCodes, $dayBefore, $filters);
        $closingCash = $this->cashBalanceAsOf($companyId, $cashCodes, $toDate, $filters);
        $periodNetChange = round($closingCash - $openingCash, 2);

        $sections = $this->buildActivitySections($companyId, $cashCodes, $filters);

        $totalInflows = round(collect($sections)->sum(fn ($s) => $s['inflows_total']), 2);
        $totalOutflows = round(collect($sections)->sum(fn ($s) => $s['outflows_total']), 2);
        $netFromActivities = round($totalInflows - $totalOutflows, 2);

        return [
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'cash_account_codes' => $cashCodes,
            'opening_cash' => $openingCash,
            'closing_cash' => $closingCash,
            'period_net_change' => $periodNetChange,
            'sections' => $sections,
            'total_inflows' => $totalInflows,
            'total_outflows' => $totalOutflows,
            'net_from_activities' => $netFromActivities,
        ];
    }

    /**
     * @param  list<string>  $cashCodes
     * @param  array<string, mixed>  $filters
     */
    protected function cashBalanceAsOf(?int $companyId, array $cashCodes, string $asOfDate, array $filters): float
    {
        $rows = PostedJournalQuery::aggregateByAccount([
            'company_id' => $companyId,
            'branch_id' => $filters['branch_id'] ?? null,
            'as_of_date' => $asOfDate,
        ])
            ->whereIn('gl_accounts.code', $cashCodes)
            ->get();

        $total = 0.0;
        foreach ($rows as $row) {
            $total += LedgerSignedBalance::balanceSheetAmount(
                (float) $row->total_debit,
                (float) $row->total_credit,
                NormalBalance::from($row->normal_balance),
            );
        }

        return round($total, 2);
    }

    /**
     * Indirect-style sections: classify period cash movements by counter-account type / code range.
     *
     * @param  list<string>  $cashCodes
     * @param  array<string, mixed>  $filters
     * @return array<string, array<string, mixed>>
     */
    protected function buildActivitySections(?int $companyId, array $cashCodes, array $filters): array
    {
        $sections = [
            'operating' => $this->emptySection('Operating activities'),
            'investing' => $this->emptySection('Investing activities'),
            'financing' => $this->emptySection('Financing activities'),
        ];

        $cashLines = PostedJournalQuery::applyFilters(
            PostedJournalQuery::base($companyId),
            [
                'from_date' => $filters['from_date'],
                'to_date' => $filters['to_date'],
                'period_id' => $filters['period_id'] ?? null,
                'branch_id' => $filters['branch_id'] ?? null,
            ],
        )
            ->whereIn('gl_accounts.code', $cashCodes)
            ->select([
                'journal_lines.journal_id',
                'journal_lines.debit',
                'journal_lines.credit',
            ])
            ->get();

        if ($cashLines->isEmpty()) {
            return $sections;
        }

        $journalIds = $cashLines->pluck('journal_id')->unique()->values()->all();

        $counterRows = DB::table('journal_lines')
            ->join('gl_accounts', 'gl_accounts.id', '=', 'journal_lines.gl_account_id')
            ->join('gl_account_types', 'gl_account_types.id', '=', 'gl_accounts.gl_account_type_id')
            ->whereIn('journal_lines.journal_id', $journalIds)
            ->whereNotIn('gl_accounts.code', $cashCodes)
            ->select([
                'journal_lines.journal_id',
                'gl_accounts.id as gl_account_id',
                'gl_accounts.code as account_code',
                'gl_accounts.name as account_name',
                'gl_account_types.code as account_type_code',
                DB::raw('COALESCE(SUM(journal_lines.debit), 0) as total_debit'),
                DB::raw('COALESCE(SUM(journal_lines.credit), 0) as total_credit'),
            ])
            ->groupBy(
                'journal_lines.journal_id',
                'gl_accounts.id',
                'gl_accounts.code',
                'gl_accounts.name',
                'gl_account_types.code',
            )
            ->get()
            ->groupBy('journal_id');

        $cashByJournal = $cashLines->groupBy('journal_id');

        foreach ($cashByJournal as $journalId => $lines) {
            $cashIn = round($lines->sum(fn ($l) => (float) $l->debit), 2);
            $cashOut = round($lines->sum(fn ($l) => (float) $l->credit), 2);
            $netCash = round($cashIn - $cashOut, 2);

            if (abs($netCash) < 0.005) {
                continue;
            }

            $counters = $counterRows->get($journalId, collect());
            $primary = $counters->sortByDesc(fn ($r) => abs((float) $r->total_debit - (float) $r->total_credit))->first();

            $sectionKey = $primary
                ? $this->classifySection($primary->account_type_code, (string) $primary->account_code)
                : 'operating';

            $label = $primary
                ? trim($primary->account_code.' — '.$primary->account_name)
                : __('Unclassified cash movement');

            $bucketKey = $primary ? (string) $primary->gl_account_id : 'unclassified';

            if (! isset($sections[$sectionKey]['lines'][$bucketKey])) {
                $sections[$sectionKey]['lines'][$bucketKey] = [
                    'label' => $label,
                    'account_code' => $primary->account_code ?? null,
                    'inflow' => 0.0,
                    'outflow' => 0.0,
                    'net' => 0.0,
                ];
            }

            if ($netCash > 0) {
                $sections[$sectionKey]['lines'][$bucketKey]['inflow'] += $netCash;
            } else {
                $sections[$sectionKey]['lines'][$bucketKey]['outflow'] += abs($netCash);
            }
            $sections[$sectionKey]['lines'][$bucketKey]['net'] += $netCash;
        }

        foreach ($sections as $key => $section) {
            $lines = collect($section['lines'])
                ->map(function (array $line) {
                    $line['inflow'] = round($line['inflow'], 2);
                    $line['outflow'] = round($line['outflow'], 2);
                    $line['net'] = round($line['net'], 2);

                    return $line;
                })
                ->sortBy('account_code')
                ->values()
                ->all();

            $sections[$key]['lines'] = $lines;
            $sections[$key]['inflows_total'] = round(collect($lines)->sum('inflow'), 2);
            $sections[$key]['outflows_total'] = round(collect($lines)->sum('outflow'), 2);
            $sections[$key]['net'] = round($sections[$key]['inflows_total'] - $sections[$key]['outflows_total'], 2);
        }

        return $sections;
    }

    protected function classifySection(string $accountTypeCode, string $accountCode): string
    {
        $codeNum = (int) preg_replace('/\D/', '', $accountCode);

        return match ($accountTypeCode) {
            GlAccountTypeCode::Revenue->value,
            GlAccountTypeCode::CostOfSales->value,
            GlAccountTypeCode::Expense->value => 'operating',
            GlAccountTypeCode::Equity->value => 'financing',
            GlAccountTypeCode::Asset->value => $codeNum >= 1500 ? 'investing' : 'operating',
            GlAccountTypeCode::Liability->value => $codeNum >= 2300 ? 'financing' : 'operating',
            default => 'operating',
        };
    }

    protected function emptySection(string $label): array
    {
        return [
            'label' => __($label),
            'lines' => [],
            'inflows_total' => 0.0,
            'outflows_total' => 0.0,
            'net' => 0.0,
        ];
    }
}
