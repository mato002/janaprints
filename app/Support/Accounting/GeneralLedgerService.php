<?php

namespace App\Support\Accounting;

use App\Enums\JournalStatus;
use App\Models\Accounting\JournalLine;
use Illuminate\Support\Collection;

class GeneralLedgerService
{
    /**
     * @param  array{period_id?: int, account_id?: int, from_date?: string, to_date?: string}  $filters
     * @return Collection<int, object>
     */
    public function entries(array $filters = []): Collection
    {
        $query = JournalLine::query()
            ->select([
                'journal_lines.*',
                'journals.journal_number',
                'journals.journal_date',
                'journals.reference',
                'journals.description as journal_description',
                'journals.status as journal_status',
                'gl_accounts.code as account_code',
                'gl_accounts.name as account_name',
            ])
            ->join('journals', 'journals.id', '=', 'journal_lines.journal_id')
            ->join('gl_accounts', 'gl_accounts.id', '=', 'journal_lines.gl_account_id')
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

        if (! empty($filters['account_id'])) {
            $query->where('journal_lines.gl_account_id', $filters['account_id']);
        }

        if (! empty($filters['from_date'])) {
            $query->whereDate('journals.journal_date', '>=', $filters['from_date']);
        }

        if (! empty($filters['to_date'])) {
            $query->whereDate('journals.journal_date', '<=', $filters['to_date']);
        }

        return $query
            ->orderBy('journals.journal_date')
            ->orderBy('journals.journal_number')
            ->orderBy('journal_lines.line_number')
            ->get();
    }
}
