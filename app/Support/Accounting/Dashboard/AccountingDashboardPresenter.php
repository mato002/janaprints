<?php

namespace App\Support\Accounting\Dashboard;

use App\Models\Accounting\AccountingPeriod;
use App\Models\Branch;
use App\Models\Company;
use App\Models\User;

class AccountingDashboardPresenter
{
    public function __construct(
        protected AccountingLedgerMetricsService $ledgerMetrics,
        protected AccountingDashboardOperationalService $operational,
    ) {}

    /**
     * @param  array{company_id?: int|null, branch_id?: int|null, period_id?: int|null}  $input
     * @return array<string, mixed>
     */
    public function build(User $user, array $input = []): array
    {
        $companyId = (int) ($input['company_id'] ?? $user->company_id ?? tenant()->companyId());
        $branchId = array_key_exists('branch_id', $input)
            ? ($input['branch_id'] !== '' && $input['branch_id'] !== null ? (int) $input['branch_id'] : null)
            : tenant()->branchId();
        $periodId = ! empty($input['period_id']) ? (int) $input['period_id'] : null;

        $filters = [
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'period_id' => $periodId,
        ];

        $ledger = $this->ledgerMetrics->build($filters);
        $operational = $this->operational->build([
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'as_of_date' => $ledger['period']['as_of_date'],
        ]);

        return [
            'generated_at' => now()->toIso8601String(),
            'context' => $this->context($companyId, $branchId, $ledger['period']),
            'filters' => [
                'company_id' => $companyId,
                'branch_id' => $branchId,
                'period_id' => $periodId ?? $ledger['period']['period_id'],
            ],
            'filter_options' => $this->filterOptions($user, $companyId),
            'cards' => $this->formatCards($ledger['cards']),
            'widgets' => $operational['widgets'],
        ];
    }

    /**
     * @param  array<string, float>  $cards
     * @return list<array<string, mixed>>
     */
    protected function formatCards(array $cards): array
    {
        $definitions = [
            ['key' => 'cash_position', 'label' => __('Cash Position'), 'route' => 'admin.accounting.reports.general-ledger', 'icon' => 'currency-dollar'],
            ['key' => 'accounts_receivable', 'label' => __('Accounts Receivable'), 'route' => 'admin.receivables.aging', 'icon' => 'users'],
            ['key' => 'accounts_payable', 'label' => __('Accounts Payable'), 'route' => 'admin.payables.aging', 'icon' => 'credit-card'],
            ['key' => 'revenue_mtd', 'label' => __('Revenue MTD'), 'route' => 'admin.accounting.reports.profit-and-loss', 'icon' => 'chart-bar'],
            ['key' => 'expenses_mtd', 'label' => __('Expenses MTD'), 'route' => 'admin.accounting.reports.profit-and-loss', 'icon' => 'document-text'],
            ['key' => 'net_profit_mtd', 'label' => __('Net Profit'), 'route' => 'admin.accounting.reports.profit-and-loss', 'icon' => 'scale'],
        ];

        return collect($definitions)->map(fn (array $def) => [
            ...$def,
            'value' => number_format($cards[$def['key']] ?? 0, 2),
            'raw' => $cards[$def['key']] ?? 0,
        ])->all();
    }

    /**
     * @return array<string, mixed>
     */
    protected function context(int $companyId, ?int $branchId, array $period): array
    {
        $company = Company::query()->find($companyId);
        $branch = $branchId ? Branch::query()->find($branchId) : null;

        return [
            'company' => $company?->name ?? __('Company'),
            'branch' => $branch?->name ?? __('All branches'),
            'period' => $period['period_code'] ?? $period['period_name'] ?? __('Current period'),
            'as_of_date' => $period['as_of_date'],
            'mtd_range' => ($period['mtd_from'] ?? '').' — '.($period['mtd_to'] ?? ''),
            'ytd_range' => ($period['ytd_from'] ?? '').' — '.($period['ytd_to'] ?? ''),
        ];
    }

    /**
     * @return array{companies: \Illuminate\Support\Collection, branches: \Illuminate\Support\Collection, periods: \Illuminate\Support\Collection}
     */
    protected function filterOptions(User $user, int $companyId): array
    {
        $companies = $user->hasRole('Super Admin')
            ? Company::query()->orderBy('name')->get(['id', 'name', 'code'])
            : Company::query()->where('id', $companyId)->get(['id', 'name', 'code']);

        $branches = Branch::query()
            ->where('company_id', $companyId)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        $periods = AccountingPeriod::query()
            ->where('company_id', $companyId)
            ->orderByDesc('start_date')
            ->get(['id', 'code', 'name', 'start_date', 'end_date', 'is_current']);

        return [
            'companies' => $companies,
            'branches' => $branches,
            'periods' => $periods,
        ];
    }
}
