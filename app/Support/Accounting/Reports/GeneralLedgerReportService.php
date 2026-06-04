<?php

namespace App\Support\Accounting\Reports;

use App\Enums\JournalStatus;
use App\Enums\NormalBalance;
use App\Models\Accounting\GlAccount;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GeneralLedgerReportService
{
    /**
     * @param  array{account_id: int, from_date?: string, to_date?: string, period_id?: int}  $filters
     */
    public function build(array $filters): array
    {
        $account = GlAccount::query()
            ->forTenant()
            ->with('accountType')
            ->findOrFail($filters['account_id']);

        $fromDate = $filters['from_date'] ?? null;
        $toDate = $filters['to_date'] ?? null;

        $openingBalance = $this->openingBalance(
            $account,
            $fromDate,
            $filters['period_id'] ?? null,
        );

        $lines = $this->periodLines($account->id, $filters);
        $running = $openingBalance;

        $detailLines = $lines->map(function ($line) use (&$running, $account) {
            $debit = round((float) $line->debit, 2);
            $credit = round((float) $line->credit, 2);
            $running = round($running + $this->balanceMovement($debit, $credit, $account->normal_balance), 2);

            return [
                'journal_line_id' => (int) $line->id,
                'journal_id' => (int) $line->journal_id,
                'journal_number' => $line->journal_number,
                'journal_date' => $line->journal_date,
                'reference' => $line->reference,
                'description' => $line->line_description ?: $line->journal_description,
                'debit' => $debit,
                'credit' => $credit,
                'running_balance' => $running,
            ];
        });

        $periodDebit = round((float) $lines->sum('debit'), 2);
        $periodCredit = round((float) $lines->sum('credit'), 2);

        return [
            'account' => $account,
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'opening_balance' => $openingBalance,
            'closing_balance' => $running,
            'period_debit' => $periodDebit,
            'period_credit' => $periodCredit,
            'lines' => $detailLines,
            'line_count' => $detailLines->count(),
        ];
    }

    /**
     * Summary of all postable accounts for a period (no running detail).
     *
     * @param  array{from_date?: string, to_date?: string, period_id?: int}  $filters
     */
    public function buildSummary(array $filters): array
    {
        $aggregates = PostedJournalQuery::aggregateByAccount($filters)->get();

        $rows = $aggregates->map(function ($row) {
            $normal = NormalBalance::from($row->normal_balance);
            $debit = round((float) $row->total_debit, 2);
            $credit = round((float) $row->total_credit, 2);

            return [
                'account_id' => (int) $row->gl_account_id,
                'account_code' => $row->account_code,
                'account_name' => $row->account_name,
                'account_type' => $row->account_type_name,
                'period_debit' => $debit,
                'period_credit' => $credit,
                'net_balance' => round($debit - $credit, 2),
                'signed_balance' => match ($normal) {
                    NormalBalance::Credit => round($credit - $debit, 2),
                    NormalBalance::Debit => round($debit - $credit, 2),
                },
            ];
        });

        return [
            'from_date' => $filters['from_date'] ?? null,
            'to_date' => $filters['to_date'] ?? null,
            'rows' => $rows,
        ];
    }

    protected function openingBalance(GlAccount $account, ?string $fromDate, ?int $periodId): float
    {
        if (! $fromDate) {
            return 0.0;
        }

        $row = PostedJournalQuery::applyFilters(
            PostedJournalQuery::base($account->company_id),
            [
                'account_id' => $account->id,
                'period_id' => $periodId,
            ],
        )
            ->whereDate('journals.journal_date', '<', $fromDate)
            ->selectRaw('COALESCE(SUM(journal_lines.debit), 0) as total_debit')
            ->selectRaw('COALESCE(SUM(journal_lines.credit), 0) as total_credit')
            ->first();

        if (! $row) {
            return 0.0;
        }

        return round(
            $this->balanceMovement(
                (float) $row->total_debit,
                (float) $row->total_credit,
                $account->normal_balance,
            ),
            2,
        );
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    protected function periodLines(int $accountId, array $filters): Collection
    {
        $query = DB::table('journal_lines')
            ->join('journals', 'journals.id', '=', 'journal_lines.journal_id')
            ->where('journal_lines.gl_account_id', $accountId)
            ->whereIn('journals.status', [
                JournalStatus::Posted->value,
                JournalStatus::Reversed->value,
            ]);

        if (tenant()->companyId()) {
            $query->where('journals.company_id', tenant()->companyId());
        }

        if (! empty($filters['period_id'])) {
            $query->where('journals.accounting_period_id', $filters['period_id']);
        }

        if (! empty($filters['from_date'])) {
            $query->whereDate('journals.journal_date', '>=', $filters['from_date']);
        }

        if (! empty($filters['to_date'])) {
            $query->whereDate('journals.journal_date', '<=', $filters['to_date']);
        }

        return $query
            ->select([
                'journal_lines.id',
                'journal_lines.journal_id',
                'journal_lines.debit',
                'journal_lines.credit',
                'journal_lines.description as line_description',
                'journals.journal_number',
                'journals.journal_date',
                'journals.reference',
                'journals.description as journal_description',
            ])
            ->orderBy('journals.journal_date')
            ->orderBy('journals.journal_number')
            ->orderBy('journal_lines.line_number')
            ->get();
    }

    protected function balanceMovement(float $debit, float $credit, NormalBalance $normal): float
    {
        $raw = $debit - $credit;

        return match ($normal) {
            NormalBalance::Debit => $raw,
            NormalBalance::Credit => -$raw,
        };
    }
}
