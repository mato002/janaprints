<?php

namespace App\Support\Accounting\Reports;

use App\Enums\JournalStatus;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class PostedJournalQuery
{
    /**
     * Base query: journal_lines joined to posted/reversed journals and GL accounts.
     * All financial reports must build on this — no summary tables.
     */
    public static function base(?int $companyId = null): Builder
    {
        $companyId ??= tenant()->companyId();

        $query = DB::table('journal_lines')
            ->join('journals', 'journals.id', '=', 'journal_lines.journal_id')
            ->join('gl_accounts', 'gl_accounts.id', '=', 'journal_lines.gl_account_id')
            ->join('gl_account_types', 'gl_account_types.id', '=', 'gl_accounts.gl_account_type_id')
            ->whereIn('journals.status', [
                JournalStatus::Posted->value,
                JournalStatus::Reversed->value,
            ]);

        if ($companyId) {
            $query->where('journals.company_id', $companyId);
        } else {
            $query->whereRaw('1 = 0');
        }

        return $query;
    }

    /**
     * @param  array{period_id?: int, from_date?: string, to_date?: string, as_of_date?: string, account_id?: int, account_type?: string, branch_id?: int|null, company_id?: int}  $filters
     */
    public static function applyFilters(Builder $query, array $filters): Builder
    {
        if (! empty($filters['branch_id'])) {
            $branchId = (int) $filters['branch_id'];
            $query->where(function (Builder $inner) use ($branchId) {
                $inner->whereNull('journals.branch_id')
                    ->orWhere('journals.branch_id', $branchId);
            });
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

        if (! empty($filters['as_of_date'])) {
            $query->whereDate('journals.journal_date', '<=', $filters['as_of_date']);
        }

        if (! empty($filters['account_id'])) {
            $query->where('journal_lines.gl_account_id', $filters['account_id']);
        }

        if (! empty($filters['account_type'])) {
            $query->where('gl_account_types.code', $filters['account_type']);
        }

        if (! empty($filters['account_types']) && is_array($filters['account_types'])) {
            $query->whereIn('gl_account_types.code', $filters['account_types']);
        }

        return $query;
    }

    /**
     * Monthly debit/credit totals by GL account type (for dashboard trends).
     *
     * @param  array<string, mixed>  $filters
     */
    public static function monthlyTotalsByAccountType(array $filters): Builder
    {
        $companyId = $filters['company_id'] ?? tenant()->companyId();
        $driver = DB::connection()->getDriverName();
        $monthExpr = $driver === 'sqlite'
            ? "strftime('%Y-%m', journals.journal_date)"
            : "DATE_FORMAT(journals.journal_date, '%Y-%m')";

        return static::applyFilters(static::base($companyId), $filters)
            ->selectRaw("{$monthExpr} as month_key")
            ->selectRaw('gl_account_types.code as account_type_code')
            ->selectRaw('COALESCE(SUM(journal_lines.debit), 0) as total_debit')
            ->selectRaw('COALESCE(SUM(journal_lines.credit), 0) as total_credit')
            ->groupBy('month_key', 'account_type_code')
            ->orderBy('month_key');
    }

    /**
     * Monthly debit/credit totals for specific GL account codes (e.g. cash accounts).
     *
     * @param  array<string, mixed>  $filters
     * @param  list<string>  $accountCodes
     */
    public static function monthlyTotalsForAccountCodes(array $filters, array $accountCodes): Builder
    {
        $companyId = $filters['company_id'] ?? tenant()->companyId();
        $driver = DB::connection()->getDriverName();
        $monthExpr = $driver === 'sqlite'
            ? "strftime('%Y-%m', journals.journal_date)"
            : "DATE_FORMAT(journals.journal_date, '%Y-%m')";

        return static::applyFilters(static::base($companyId), $filters)
            ->whereIn('gl_accounts.code', $accountCodes)
            ->selectRaw("{$monthExpr} as month_key")
            ->selectRaw('COALESCE(SUM(journal_lines.debit), 0) as total_debit')
            ->selectRaw('COALESCE(SUM(journal_lines.credit), 0) as total_credit')
            ->groupBy('month_key')
            ->orderBy('month_key');
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public static function aggregateByAccount(array $filters = []): Builder
    {
        $companyId = $filters['company_id'] ?? tenant()->companyId();

        return static::applyFilters(static::base($companyId), $filters)
            ->groupBy(
                'journal_lines.gl_account_id',
                'gl_accounts.code',
                'gl_accounts.name',
                'gl_accounts.normal_balance',
                'gl_account_types.code',
                'gl_account_types.name',
            )
            ->selectRaw('journal_lines.gl_account_id')
            ->selectRaw('gl_accounts.code as account_code')
            ->selectRaw('gl_accounts.name as account_name')
            ->selectRaw('gl_accounts.normal_balance')
            ->selectRaw('gl_account_types.code as account_type_code')
            ->selectRaw('gl_account_types.name as account_type_name')
            ->selectRaw('COALESCE(SUM(journal_lines.debit), 0) as total_debit')
            ->selectRaw('COALESCE(SUM(journal_lines.credit), 0) as total_credit')
            ->orderBy('gl_accounts.code');
    }
}
