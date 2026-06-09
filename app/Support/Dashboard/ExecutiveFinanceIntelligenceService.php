<?php

namespace App\Support\Dashboard;

use App\Support\Accounting\Dashboard\AccountingLedgerMetricsService;
use App\Support\Reports\IntelligenceAggregateQueries;
use App\Support\Reports\IntelligenceScope;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

class ExecutiveFinanceIntelligenceService
{
    public function __construct(
        protected AccountingLedgerMetricsService $ledgerMetrics,
        protected IntelligenceAggregateQueries $queries,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(): array
    {
        if (! $this->canView()) {
            return $this->emptyPayload();
        }

        $companyId = (int) (tenant()->companyId() ?? auth()->user()?->company_id);
        if (! $companyId) {
            return $this->emptyPayload();
        }

        $branchId = tenant()->branchId();
        $monthStart = now()->startOfMonth()->toDateString();
        $today = now()->toDateString();

        $scope = new IntelligenceScope(
            companyId: $companyId,
            branchId: $branchId,
            fromDate: $monthStart,
            toDate: $today,
        );

        $ledger = $this->ledgerAvailable() && $this->canViewLedger()
            ? $this->ledgerMetrics->build([
                'company_id' => $companyId,
                'branch_id' => $branchId,
            ])
            : null;

        $cards = $ledger['cards'] ?? [];
        $charts = $ledger['charts'] ?? [];

        $receivables = $this->resolveSubledgerMetric(
            $cards['accounts_receivable'] ?? null,
            $this->queries->sumReceivables($scope),
            $this->canViewReceivables(),
        );
        $payables = $this->resolveSubledgerMetric(
            $cards['accounts_payable'] ?? null,
            $this->queries->sumPayables($scope),
            $this->canViewPayables(),
        );
        $revenue = $this->resolveMetric(
            $cards['revenue_mtd'] ?? null,
            $this->queries->sumRevenueMtd($scope),
            $this->canViewRevenue(),
        );
        $collections = $this->resolveMetric(
            null,
            $this->queries->sumCollectionsMtd($scope),
            $this->canViewCollections(),
        );
        $cash = $this->resolveMetric(
            $cards['cash_position'] ?? null,
            null,
            $this->canViewLedger(),
        );
        $expenses = $this->resolveMetric(
            $cards['expenses_mtd'] ?? null,
            null,
            $this->canViewLedger(),
        );
        $profit = $this->resolveMetric(
            $cards['net_profit_mtd'] ?? null,
            null,
            $this->canViewLedger(),
        );
        $grossMargin = $this->resolveGrossMargin(
            isset($cards['gross_margin_mtd']) ? (float) $cards['gross_margin_mtd'] : null,
            $this->canViewLedger(),
        );

        $source = $ledger !== null ? 'ledger' : 'operational';
        $available = $this->hasVisibleMetrics(
            $receivables,
            $payables,
            $revenue,
            $collections,
            $cash,
            $expenses,
            $profit,
            $grossMargin,
        );

        if (! $available) {
            $source = 'none';
        }

        return [
            'available' => $available,
            'source' => $source,
            'receivables' => $receivables['display'],
            'receivables_raw' => $receivables['raw'],
            'payables' => $payables['display'],
            'payables_raw' => $payables['raw'],
            'cash_position' => $cash['display'],
            'cash_position_raw' => $cash['raw'],
            'collections_mtd' => $collections['display'],
            'collections_mtd_raw' => $collections['raw'],
            'gross_margin' => $grossMargin['display'],
            'gross_margin_raw' => $grossMargin['raw'],
            'revenue_mtd' => $revenue['display'],
            'revenue_raw' => $revenue['raw'],
            'expenses_mtd' => $expenses['display'],
            'expenses_raw' => $expenses['raw'],
            'profit_mtd' => $profit['display'],
            'profit_raw' => $profit['raw'],
            'trends' => [
                'revenue' => $charts['revenue_trend'] ?? [],
                'expenses' => $charts['expense_trend'] ?? [],
                'cash_flow' => $charts['cash_flow_trend'] ?? [],
            ],
            'links' => $this->financeLinks(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function emptyPayload(): array
    {
        return [
            'available' => false,
            'source' => 'none',
            'receivables' => '—',
            'receivables_raw' => null,
            'payables' => '—',
            'payables_raw' => null,
            'cash_position' => '—',
            'cash_position_raw' => null,
            'collections_mtd' => '—',
            'collections_mtd_raw' => null,
            'gross_margin' => '—',
            'gross_margin_raw' => null,
            'revenue_mtd' => '—',
            'revenue_raw' => null,
            'expenses_mtd' => '—',
            'expenses_raw' => null,
            'profit_mtd' => '—',
            'profit_raw' => null,
            'trends' => [
                'revenue' => [],
                'expenses' => [],
                'cash_flow' => [],
            ],
            'links' => [],
        ];
    }

    /**
     * @return array{raw: ?float, display: string}
     */
    protected function resolveMetric(?float $ledgerValue, ?float $operationalValue, bool $visible): array
    {
        if (! $visible) {
            return ['raw' => null, 'display' => '—'];
        }

        $raw = $ledgerValue ?? $operationalValue;

        if ($raw === null) {
            return ['raw' => null, 'display' => '—'];
        }

        return ['raw' => $raw, 'display' => $this->queries->money($raw)];
    }

    /**
     * Prefer posted ledger balances; fall back to invoice/bill subledgers when GL is zero.
     *
     * @return array{raw: ?float, display: string}
     */
    protected function resolveSubledgerMetric(?float $ledgerValue, ?float $operationalValue, bool $visible): array
    {
        if (! $visible) {
            return ['raw' => null, 'display' => '—'];
        }

        $raw = ($ledgerValue !== null && $ledgerValue > 0)
            ? $ledgerValue
            : $operationalValue;

        if ($raw === null) {
            return ['raw' => null, 'display' => '—'];
        }

        return ['raw' => $raw, 'display' => $this->queries->money($raw)];
    }

    /**
     * @return array{raw: ?float, display: string}
     */
    protected function resolveGrossMargin(?float $ledgerPercent, bool $visible): array
    {
        if (! $visible || $ledgerPercent === null) {
            return ['raw' => null, 'display' => '—'];
        }

        return [
            'raw' => $ledgerPercent,
            'display' => round($ledgerPercent, 1).'%',
        ];
    }

    /**
     * @return list<array{label: string, route: string, url: ?string}>
     */
    protected function financeLinks(): array
    {
        $definitions = [
            [
                'label' => __('Financial 360'),
                'route' => 'admin.reports.financial360',
                'permission' => ['intelligence.financial.view', 'reports.view'],
            ],
            [
                'label' => __('Accounting Dashboard'),
                'route' => 'admin.accounting.dashboard',
                'permission' => ['accounting.dashboard.view'],
            ],
            [
                'label' => __('Receivables Intelligence'),
                'route' => 'admin.receivables.aging',
                'permission' => ['receivables.aging.view'],
            ],
            [
                'label' => __('Payables Intelligence'),
                'route' => 'admin.payables.aging',
                'permission' => ['payables.aging.view'],
            ],
        ];

        $user = auth()->user();
        $links = [];

        foreach ($definitions as $def) {
            if (! $user || ! $this->userCanAny($user, $def['permission'])) {
                continue;
            }

            if (! Route::has($def['route'])) {
                continue;
            }

            $links[] = [
                'label' => $def['label'],
                'route' => $def['route'],
                'url' => route($def['route']),
            ];
        }

        return $links;
    }

    protected function canView(): bool
    {
        $user = auth()->user();

        return $user && (
            $user->can('accounting.dashboard.view')
            || $user->can('invoices.view')
            || $user->can('payables.bills.view')
            || $user->can('reports.view')
            || $user->can('intelligence.financial.view')
        );
    }

    protected function canViewLedger(): bool
    {
        $user = auth()->user();

        return $user && (
            $user->can('accounting.dashboard.view')
            || $user->can('accounting.reports.view')
        );
    }

    protected function canViewReceivables(): bool
    {
        $user = auth()->user();

        return $user && (
            $user->can('invoices.view')
            || $user->can('receivables.aging.view')
            || $user->can('accounting.dashboard.view')
        );
    }

    protected function canViewPayables(): bool
    {
        $user = auth()->user();

        return $user && (
            $user->can('payables.bills.view')
            || $user->can('payables.aging.view')
            || $user->can('accounting.dashboard.view')
        );
    }

    protected function canViewRevenue(): bool
    {
        $user = auth()->user();

        return $user && (
            $user->can('invoices.view')
            || $user->can('sales_orders.view')
            || $user->can('accounting.dashboard.view')
            || $user->can('reports.view')
        );
    }

    protected function canViewCollections(): bool
    {
        $user = auth()->user();

        return $user && (
            $user->can('payments.view')
            || $user->can('receivables.aging.view')
            || $user->can('accounting.dashboard.view')
        );
    }

    protected function ledgerAvailable(): bool
    {
        return Schema::hasTable('journals')
            && Schema::hasTable('gl_accounts')
            && Schema::hasTable('accounting_periods');
    }

    /**
     * @param  array{raw: ?float, display: string}  ...$metrics
     */
    protected function hasVisibleMetrics(array ...$metrics): bool
    {
        foreach ($metrics as $metric) {
            if (($metric['raw'] ?? null) !== null) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  list<string>  $permissions
     */
    protected function userCanAny(\App\Models\User $user, array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($user->can($permission)) {
                return true;
            }
        }

        return false;
    }
}
