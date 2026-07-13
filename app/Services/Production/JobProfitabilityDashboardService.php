<?php

namespace App\Services\Production;

use App\Enums\ProductionType;
use App\Models\Branch;
use App\Models\Crm\Customer;
use App\Models\Production\JobCostSheet;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;

class JobProfitabilityDashboardService
{
    public const HEALTHY_MARGIN_PERCENT = 25.0;

    /**
     * @return array<string, mixed>
     */
    public function build(Request $request, ?User $user = null): array
    {
        $user ??= auth()->user();
        $companyId = (int) tenant()->companyId();
        $tenantBranchId = tenant()->branchId();

        $filters = $this->filtersFromRequest($request);
        $baseQuery = $this->baseQuery($companyId, $tenantBranchId, $filters);

        $monthQuery = $this->monthScopeQuery($companyId, $tenantBranchId);
        $aggregates = $this->scopedAggregates(clone $baseQuery);
        $health = $this->healthSummary(clone $baseQuery);
        $costDrivers = $this->costDriverBreakdown(clone $baseQuery);

        $topProfitable = $this->topProfitableJobs(clone $baseQuery, 10);
        $lossMaking = $this->lossMakingJobs(clone $baseQuery, 10);
        $productProfitability = $this->productProfitability(clone $baseQuery);
        $branchProfitability = $this->branchProfitability(clone $baseQuery, $companyId);
        $customerRows = $this->customerProfitability(clone $baseQuery);

        return [
            'as_of' => now()->format('Y-m-d H:i'),
            'filters' => $filters,
            'filter_options' => $this->filterOptions($companyId, $tenantBranchId),
            'active_filter_chips' => $this->activeFilterChips($filters),
            'has_active_filters' => $this->hasActiveFilters($filters),
            'kpis' => $this->executiveKpis($monthQuery, clone $baseQuery, $productProfitability, $customerRows),
            'health' => $health,
            'top_profitable' => $topProfitable,
            'loss_making' => $lossMaking,
            'product_profitability' => $productProfitability,
            'branch_profitability' => $branchProfitability,
            'top_customers' => collect($customerRows)->sortByDesc('profit')->take(10)->values()->all(),
            'low_margin_customers' => collect($customerRows)
                ->filter(fn (array $row) => $row['revenue'] > 0 && ($row['margin_percent'] < self::HEALTHY_MARGIN_PERCENT || $row['profit'] < 0))
                ->sortBy('profit')
                ->take(10)
                ->values()
                ->all(),
            'cost_drivers' => $costDrivers,
            'alerts' => $this->alerts($health, $productProfitability, $customerRows, $filters, $user),
            'totals' => $aggregates,
            'can_view_job_360' => $user?->can('production.view') && Route::has('admin.production.job-cards.show'),
            'can_view_customer_360' => $user?->can('crm.customers.view') && Route::has('admin.crm.customers.show'),
            'target_margin_percent' => self::HEALTHY_MARGIN_PERCENT,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function filtersFromRequest(Request $request): array
    {
        $now = now();

        return [
            'date_from' => $request->query('date_from', $now->copy()->startOfMonth()->toDateString()),
            'date_to' => $request->query('date_to', $now->toDateString()),
            'branch_id' => $request->query('branch_id'),
            'customer_id' => $request->query('customer_id'),
            'production_type' => $request->query('production_type'),
            'margin_category' => $request->query('margin_category'),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    protected function baseQuery(int $companyId, ?int $tenantBranchId, array $filters): Builder
    {
        $query = JobCostSheet::query()
            ->where('job_cost_sheets.company_id', $companyId);

        $branchId = filled($filters['branch_id'] ?? null)
            ? (int) $filters['branch_id']
            : $tenantBranchId;

        if ($branchId) {
            $query->where('job_cost_sheets.branch_id', $branchId);
        }

        if (filled($filters['date_from'] ?? null)) {
            $query->where('job_cost_sheets.calculated_at', '>=', $filters['date_from'].' 00:00:00');
        }

        if (filled($filters['date_to'] ?? null)) {
            $query->where('job_cost_sheets.calculated_at', '<=', $filters['date_to'].' 23:59:59');
        }

        if (filled($filters['customer_id'] ?? null)) {
            $query->whereHas('jobCard', fn (Builder $q) => $q->where('customer_id', (int) $filters['customer_id']));
        }

        if (filled($filters['production_type'] ?? null)) {
            $query->whereHas('jobCard', fn (Builder $q) => $q->where('production_type', $filters['production_type']));
        }

        $this->applyMarginCategoryFilter($query, $filters['margin_category'] ?? null);

        return $query;
    }

    protected function monthScopeQuery(int $companyId, ?int $tenantBranchId): Builder
    {
        $query = JobCostSheet::query()
            ->where('job_cost_sheets.company_id', $companyId)
            ->where('job_cost_sheets.calculated_at', '>=', now()->startOfMonth()->startOfDay())
            ->where('job_cost_sheets.calculated_at', '<=', now()->endOfMonth()->endOfDay());

        if ($tenantBranchId) {
            $query->where('job_cost_sheets.branch_id', $tenantBranchId);
        }

        return $query;
    }

    protected function applyMarginCategoryFilter(Builder $query, ?string $category): void
    {
        if (! filled($category)) {
            return;
        }

        match ($category) {
            'healthy' => $query
                ->where('job_cost_sheets.revenue', '>', 0)
                ->where('job_cost_sheets.total_cost', '>', 0)
                ->where('job_cost_sheets.gross_margin_percent', '>=', self::HEALTHY_MARGIN_PERCENT),
            'low_margin' => $query
                ->where('job_cost_sheets.revenue', '>', 0)
                ->where('job_cost_sheets.total_cost', '>', 0)
                ->where('job_cost_sheets.gross_margin_percent', '>', 0)
                ->where('job_cost_sheets.gross_margin_percent', '<', self::HEALTHY_MARGIN_PERCENT),
            'loss' => $query->where('job_cost_sheets.gross_profit', '<', 0),
            'missing_costing' => $query
                ->where('job_cost_sheets.revenue', '>', 0)
                ->where(function (Builder $q) {
                    $q->whereNull('job_cost_sheets.total_cost')->orWhere('job_cost_sheets.total_cost', '<=', 0);
                }),
            default => null,
        };
    }

    /**
     * @return array<string, float|int>
     */
    protected function scopedAggregates(Builder $query): array
    {
        $row = (clone $query)->selectRaw('COALESCE(SUM(job_cost_sheets.revenue), 0) as revenue')
            ->selectRaw('COALESCE(SUM(job_cost_sheets.total_cost), 0) as total_cost')
            ->selectRaw('COALESCE(SUM(job_cost_sheets.gross_profit), 0) as gross_profit')
            ->selectRaw('COUNT(*) as job_count')
            ->first();

        $revenue = (float) ($row->revenue ?? 0);
        $cost = (float) ($row->total_cost ?? 0);
        $profit = (float) ($row->gross_profit ?? 0);

        return [
            'revenue' => round($revenue, 2),
            'total_cost' => round($cost, 2),
            'gross_profit' => round($profit, 2),
            'margin_percent' => $revenue > 0 ? round(($profit / $revenue) * 100, 2) : 0.0,
            'job_count' => (int) ($row->job_count ?? 0),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function executiveKpis(
        Builder $monthQuery,
        Builder $scopedQuery,
        array $productProfitability,
        array $customerRows,
    ): array {
        $month = (clone $monthQuery)->selectRaw('COALESCE(SUM(job_cost_sheets.revenue), 0) as revenue')
            ->selectRaw('COALESCE(SUM(job_cost_sheets.total_cost), 0) as total_cost')
            ->selectRaw('COALESCE(SUM(job_cost_sheets.gross_profit), 0) as gross_profit')
            ->first();

        $monthRevenue = (float) ($month->revenue ?? 0);
        $monthCost = (float) ($month->total_cost ?? 0);
        $monthProfit = (float) ($month->gross_profit ?? 0);
        $monthMargin = $monthRevenue > 0 ? round(($monthProfit / $monthRevenue) * 100, 1) : 0.0;

        $lossCount = (clone $scopedQuery)->where('job_cost_sheets.gross_profit', '<', 0)->count();
        $missingCount = (clone $scopedQuery)
            ->where('job_cost_sheets.revenue', '>', 0)
            ->where(function (Builder $q) {
                $q->whereNull('job_cost_sheets.total_cost')->orWhere('job_cost_sheets.total_cost', '<=', 0);
            })
            ->count();

        $topProduct = collect($productProfitability)->sortByDesc('profit')->first();
        $topCustomer = collect($customerRows)->sortByDesc('profit')->first();

        return [
            [
                'label' => __('Revenue This Month'),
                'value' => $this->money($monthRevenue),
                'icon' => 'currency-dollar',
                'tone' => 'indigo',
            ],
            [
                'label' => __('Production Cost This Month'),
                'value' => $this->money($monthCost),
                'icon' => 'cog',
                'tone' => 'slate',
            ],
            [
                'label' => __('Gross Profit This Month'),
                'value' => $this->money($monthProfit),
                'icon' => 'chart-pie',
                'tone' => $monthProfit >= 0 ? 'emerald' : 'red',
            ],
            [
                'label' => __('Gross Margin %'),
                'value' => $monthRevenue > 0 ? $monthMargin.'%' : 'N/A',
                'icon' => 'chart-bar',
                'tone' => $monthMargin >= self::HEALTHY_MARGIN_PERCENT ? 'emerald' : ($monthMargin > 0 ? 'amber' : 'red'),
            ],
            [
                'label' => __('Loss-Making Jobs'),
                'value' => (string) $lossCount,
                'icon' => 'exclamation-triangle',
                'tone' => $lossCount > 0 ? 'red' : 'slate',
                'clickable' => $lossCount > 0,
                'url' => $lossCount > 0 ? $this->filterUrl(['margin_category' => 'loss']) : null,
            ],
            [
                'label' => __('Jobs Missing Costing'),
                'value' => (string) $missingCount,
                'icon' => 'question-mark-circle',
                'tone' => $missingCount > 0 ? 'amber' : 'slate',
                'clickable' => $missingCount > 0,
                'url' => $missingCount > 0 ? $this->filterUrl(['margin_category' => 'missing_costing']) : null,
            ],
            [
                'label' => __('Most Profitable Product/Service'),
                'value' => $topProduct['label'] ?? 'N/A',
                'hint' => isset($topProduct['profit']) ? $this->money((float) $topProduct['profit']) : null,
                'icon' => 'cube',
                'tone' => 'emerald',
            ],
            [
                'label' => __('Most Profitable Customer'),
                'value' => $topCustomer['customer_name'] ?? 'N/A',
                'hint' => isset($topCustomer['profit']) ? $this->money((float) $topCustomer['profit']) : null,
                'icon' => 'users',
                'tone' => 'emerald',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function healthSummary(Builder $query): array
    {
        $row = (clone $query)->selectRaw('SUM(CASE WHEN job_cost_sheets.revenue > 0 AND job_cost_sheets.total_cost > 0 AND job_cost_sheets.gross_margin_percent >= ? THEN 1 ELSE 0 END) as healthy', [self::HEALTHY_MARGIN_PERCENT])
            ->selectRaw('SUM(CASE WHEN job_cost_sheets.revenue > 0 AND job_cost_sheets.total_cost > 0 AND job_cost_sheets.gross_margin_percent > 0 AND job_cost_sheets.gross_margin_percent < ? THEN 1 ELSE 0 END) as low_margin', [self::HEALTHY_MARGIN_PERCENT])
            ->selectRaw('SUM(CASE WHEN job_cost_sheets.gross_profit < 0 THEN 1 ELSE 0 END) as loss_making')
            ->selectRaw('SUM(CASE WHEN job_cost_sheets.revenue > 0 AND (job_cost_sheets.total_cost IS NULL OR job_cost_sheets.total_cost <= 0) THEN 1 ELSE 0 END) as missing_costing')
            ->first();

        return [
            'healthy' => [
                'count' => (int) ($row->healthy ?? 0),
                'label' => __('Healthy Jobs'),
                'badge' => __('Healthy'),
                'variant' => 'success',
                'description' => __('Margin ≥ :percent%', ['percent' => (int) self::HEALTHY_MARGIN_PERCENT]),
                'filter_url' => $this->filterUrl(['margin_category' => 'healthy']),
            ],
            'low_margin' => [
                'count' => (int) ($row->low_margin ?? 0),
                'label' => __('Low Margin Jobs'),
                'badge' => __('Low Margin'),
                'variant' => 'warning',
                'description' => __('Margin > 0% and < :percent%', ['percent' => (int) self::HEALTHY_MARGIN_PERCENT]),
                'filter_url' => $this->filterUrl(['margin_category' => 'low_margin']),
            ],
            'loss_making' => [
                'count' => (int) ($row->loss_making ?? 0),
                'label' => __('Loss-Making Jobs'),
                'badge' => __('Loss'),
                'variant' => 'danger',
                'description' => __('Negative gross profit'),
                'filter_url' => $this->filterUrl(['margin_category' => 'loss']),
            ],
            'missing_costing' => [
                'count' => (int) ($row->missing_costing ?? 0),
                'label' => __('Jobs Missing Costing'),
                'badge' => __('Missing Cost'),
                'variant' => 'draft',
                'description' => __('Revenue recorded without cost inputs'),
                'filter_url' => $this->filterUrl(['margin_category' => 'missing_costing']),
            ],
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    protected function topProfitableJobs(Builder $query, int $limit): Collection
    {
        return (clone $query)
            ->where('job_cost_sheets.gross_profit', '>', 0)
            ->orderByDesc('job_cost_sheets.gross_profit')
            ->limit($limit)
            ->with(['jobCard.customer:id,company_name', 'jobCard:id,public_id,job_card_number,customer_id'])
            ->get()
            ->values()
            ->map(fn (JobCostSheet $sheet, int $index) => $this->presentJobRow($sheet, $index + 1));
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    protected function lossMakingJobs(Builder $query, int $limit): Collection
    {
        return (clone $query)
            ->where('job_cost_sheets.gross_profit', '<', 0)
            ->orderBy('job_cost_sheets.gross_profit')
            ->limit($limit)
            ->with(['jobCard.customer:id,company_name', 'jobCard:id,public_id,job_card_number,customer_id'])
            ->get()
            ->values()
            ->map(fn (JobCostSheet $sheet) => $this->presentJobRow($sheet, null, true));
    }

    /**
     * @return array<string, mixed>
     */
    protected function presentJobRow(JobCostSheet $sheet, ?int $rank = null, bool $isLoss = false): array
    {
        $jobCard = $sheet->jobCard;
        $user = auth()->user();

        $job360Url = null;
        if ($user?->can('production.view') && Route::has('admin.production.job-cards.show') && $jobCard) {
            $job360Url = route('admin.production.job-cards.show', $jobCard);
        }

        $costingUrl = ($jobCard && Route::has('admin.production.job-cards.costing'))
            ? route('admin.production.job-cards.costing', $jobCard)
            : null;

        return [
            'rank' => $rank,
            'job_card_number' => $jobCard?->job_card_number ?? __('Unknown'),
            'customer_name' => $jobCard?->customer?->company_name ?? __('Unknown'),
            'revenue' => (float) $sheet->revenue,
            'cost' => (float) $sheet->total_cost,
            'profit' => (float) $sheet->gross_profit,
            'loss' => $isLoss ? abs((float) $sheet->gross_profit) : null,
            'margin_percent' => (float) $sheet->gross_margin_percent,
            'margin_variant' => $this->marginVariant((float) $sheet->gross_margin_percent, (float) $sheet->revenue, (float) $sheet->total_cost),
            'likely_reason' => __('Review costing inputs'),
            'job_360_url' => $job360Url,
            'costing_url' => $costingUrl,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function productProfitability(Builder $query): array
    {
        $sheets = (clone $query)
            ->join('production_job_cards', 'production_job_cards.id', '=', 'job_cost_sheets.production_job_card_id')
            ->selectRaw('production_job_cards.production_type as production_type')
            ->selectRaw('COALESCE(SUM(job_cost_sheets.revenue), 0) as revenue')
            ->selectRaw('COALESCE(SUM(job_cost_sheets.total_cost), 0) as total_cost')
            ->selectRaw('COALESCE(SUM(job_cost_sheets.gross_profit), 0) as gross_profit')
            ->selectRaw('COUNT(*) as job_count')
            ->groupBy('production_job_cards.production_type')
            ->get();

        return $sheets->map(function ($row) {
            $type = $row->production_type ?? 'unknown';
            $revenue = (float) $row->revenue;
            $cost = (float) $row->total_cost;
            $profit = (float) $row->gross_profit;
            $margin = $revenue > 0 ? round(($profit / $revenue) * 100, 2) : 0.0;

            return [
                'production_type' => $type,
                'label' => str(ProductionType::tryFrom($type)?->value ?? $type)->headline(),
                'revenue' => round($revenue, 2),
                'cost' => round($cost, 2),
                'profit' => round($profit, 2),
                'margin_percent' => $margin,
                'margin_variant' => $this->marginVariant($margin, $revenue, $cost),
                'job_count' => (int) $row->job_count,
            ];
        })->sortByDesc('profit')->values()->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function branchProfitability(Builder $query, int $companyId): array
    {
        $rows = (clone $query)
            ->selectRaw('job_cost_sheets.branch_id as branch_id')
            ->selectRaw('COALESCE(SUM(job_cost_sheets.revenue), 0) as revenue')
            ->selectRaw('COALESCE(SUM(job_cost_sheets.total_cost), 0) as total_cost')
            ->selectRaw('COALESCE(SUM(job_cost_sheets.gross_profit), 0) as gross_profit')
            ->selectRaw('COUNT(*) as job_count')
            ->groupBy('job_cost_sheets.branch_id')
            ->get();

        $branches = Branch::query()
            ->where('company_id', $companyId)
            ->whereIn('id', $rows->pluck('branch_id')->filter())
            ->get(['id', 'name'])
            ->keyBy('id');

        return $rows->map(function ($row) use ($branches) {
            $revenue = (float) $row->revenue;
            $cost = (float) $row->total_cost;
            $profit = (float) $row->gross_profit;
            $margin = $revenue > 0 ? round(($profit / $revenue) * 100, 2) : 0.0;

            return [
                'branch_id' => $row->branch_id,
                'branch_name' => $branches->get($row->branch_id)?->name ?? __('Unknown'),
                'revenue' => round($revenue, 2),
                'cost' => round($cost, 2),
                'profit' => round($profit, 2),
                'margin_percent' => $margin,
                'margin_variant' => $this->marginVariant($margin, $revenue, $cost),
                'job_count' => (int) $row->job_count,
            ];
        })->sortByDesc('profit')->values()->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function customerProfitability(Builder $query): array
    {
        $rows = (clone $query)
            ->join('production_job_cards', 'production_job_cards.id', '=', 'job_cost_sheets.production_job_card_id')
            ->selectRaw('production_job_cards.customer_id as customer_id')
            ->selectRaw('COALESCE(SUM(job_cost_sheets.revenue), 0) as revenue')
            ->selectRaw('COALESCE(SUM(job_cost_sheets.total_cost), 0) as total_cost')
            ->selectRaw('COALESCE(SUM(job_cost_sheets.gross_profit), 0) as gross_profit')
            ->selectRaw('COUNT(*) as job_count')
            ->groupBy('production_job_cards.customer_id')
            ->get();

        $customers = Customer::query()
            ->whereIn('id', $rows->pluck('customer_id')->filter())
            ->get(['id', 'company_name'])
            ->keyBy('id');

        $user = auth()->user();
        $canViewCustomer = $user?->can('crm.customers.view') && Route::has('admin.crm.customers.show');

        return $rows->map(function ($row) use ($customers, $canViewCustomer) {
            $revenue = (float) $row->revenue;
            $cost = (float) $row->total_cost;
            $profit = (float) $row->gross_profit;
            $margin = $revenue > 0 ? round(($profit / $revenue) * 100, 2) : 0.0;
            $customerId = $row->customer_id;

            return [
                'customer_id' => $customerId,
                'customer_name' => $customers->get($customerId)?->company_name ?? __('Unknown'),
                'customer_url' => ($canViewCustomer && $customerId)
                    ? route('admin.crm.customers.show', $customerId)
                    : null,
                'revenue' => round($revenue, 2),
                'cost' => round($cost, 2),
                'profit' => round($profit, 2),
                'margin_percent' => $margin,
                'margin_variant' => $this->marginVariant($margin, $revenue, $cost),
                'job_count' => (int) $row->job_count,
            ];
        })->values()->all();
    }

    /**
     * @return array<string, mixed>
     */
    protected function costDriverBreakdown(Builder $query): array
    {
        $row = (clone $query)->selectRaw('COALESCE(SUM(job_cost_sheets.material_cost), 0) as material_cost')
            ->selectRaw('COALESCE(SUM(job_cost_sheets.labor_cost), 0) as labor_cost')
            ->selectRaw('COALESCE(SUM(job_cost_sheets.machine_cost), 0) as machine_cost')
            ->selectRaw('COALESCE(SUM(job_cost_sheets.overhead_cost), 0) as overhead_cost')
            ->selectRaw('COALESCE(SUM(job_cost_sheets.finishing_cost), 0) as finishing_cost')
            ->selectRaw('COALESCE(SUM(job_cost_sheets.outsourced_cost), 0) as outsourced_cost')
            ->first();

        $categories = [
            'materials' => ['label' => __('Materials'), 'amount' => (float) ($row->material_cost ?? 0)],
            'labor' => ['label' => __('Labor'), 'amount' => (float) ($row->labor_cost ?? 0)],
            'machine' => ['label' => __('Machine'), 'amount' => (float) ($row->machine_cost ?? 0)],
            'overhead' => ['label' => __('Overhead'), 'amount' => (float) ($row->overhead_cost ?? 0)],
            'finishing' => ['label' => __('Finishing'), 'amount' => (float) ($row->finishing_cost ?? 0)],
            'other' => ['label' => __('Other'), 'amount' => (float) ($row->outsourced_cost ?? 0)],
        ];

        $available = collect($categories)
            ->filter(fn (array $item) => $item['amount'] > 0)
            ->map(fn (array $item, string $key) => [
                'key' => $key,
                'label' => $item['label'],
                'amount' => round($item['amount'], 2),
            ])
            ->values()
            ->all();

        $total = array_sum(array_column($available, 'amount'));

        return [
            'available' => $available,
            'total' => round($total, 2),
            'has_data' => count($available) > 0,
        ];
    }

    /**
     * @param  array<string, mixed>  $health
     * @param  array<int, array<string, mixed>>  $productProfitability
     * @param  array<int, array<string, mixed>>  $customerRows
     * @return list<array<string, mixed>>
     */
    protected function alerts(
        array $health,
        array $productProfitability,
        array $customerRows,
        array $filters,
        ?User $user,
    ): array {
        $belowTargetProducts = collect($productProfitability)
            ->filter(fn (array $row) => $row['revenue'] > 0 && $row['margin_percent'] < self::HEALTHY_MARGIN_PERCENT)
            ->count();

        $belowTargetCustomers = collect($customerRows)
            ->filter(fn (array $row) => $row['revenue'] > 0 && $row['margin_percent'] < self::HEALTHY_MARGIN_PERCENT)
            ->count();

        return [
            [
                'key' => 'loss_making_jobs',
                'title' => __('Jobs losing money'),
                'count' => $health['loss_making']['count'],
                'severity' => 'danger',
                'severity_label' => __('Critical'),
                'url' => $this->filterUrl(['margin_category' => 'loss'], $filters),
            ],
            [
                'key' => 'below_target_margin',
                'title' => __('Jobs below target margin'),
                'count' => $health['low_margin']['count'],
                'severity' => 'warning',
                'severity_label' => __('Attention'),
                'url' => $this->filterUrl(['margin_category' => 'low_margin'], $filters),
            ],
            [
                'key' => 'missing_costing',
                'title' => __('Jobs missing costing'),
                'count' => $health['missing_costing']['count'],
                'severity' => 'warning',
                'severity_label' => __('Attention'),
                'url' => $this->filterUrl(['margin_category' => 'missing_costing'], $filters),
            ],
            [
                'key' => 'customers_below_target',
                'title' => __('Customers below target margin'),
                'count' => $belowTargetCustomers,
                'severity' => $belowTargetCustomers > 0 ? 'warning' : 'neutral',
                'severity_label' => $belowTargetCustomers > 0 ? __('Attention') : __('Clear'),
                'url' => null,
            ],
            [
                'key' => 'products_below_target',
                'title' => __('Product/service below target margin'),
                'count' => $belowTargetProducts,
                'severity' => $belowTargetProducts > 0 ? 'warning' : 'neutral',
                'severity_label' => $belowTargetProducts > 0 ? __('Attention') : __('Clear'),
                'url' => null,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function filterOptions(int $companyId, ?int $tenantBranchId): array
    {
        $sheetQuery = JobCostSheet::query()->where('job_cost_sheets.company_id', $companyId);
        if ($tenantBranchId) {
            $sheetQuery->where('job_cost_sheets.branch_id', $tenantBranchId);
        }

        $customerIds = (clone $sheetQuery)
            ->join('production_job_cards', 'production_job_cards.id', '=', 'job_cost_sheets.production_job_card_id')
            ->distinct()
            ->pluck('production_job_cards.customer_id')
            ->filter();

        return [
            'branches' => Branch::query()
                ->where('company_id', $companyId)
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name']),
            'customers' => Customer::query()
                ->where('company_id', $companyId)
                ->whereIn('id', $customerIds)
                ->orderBy('company_name')
                ->limit(100)
                ->get(['id', 'company_name']),
            'production_types' => ProductionType::cases(),
            'margin_categories' => [
                'healthy' => __('Healthy (≥ :percent%)', ['percent' => (int) self::HEALTHY_MARGIN_PERCENT]),
                'low_margin' => __('Low margin'),
                'loss' => __('Loss-making'),
                'missing_costing' => __('Missing costing'),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return list<array<string, string>>
     */
    protected function activeFilterChips(array $filters): array
    {
        $chips = [];
        $defaults = $this->filtersFromRequest(request());

        foreach (['date_from', 'date_to', 'branch_id', 'customer_id', 'production_type', 'margin_category'] as $key) {
            $value = $filters[$key] ?? null;
            if (! filled($value)) {
                continue;
            }

            if (in_array($key, ['date_from', 'date_to'], true) && ($filters[$key] ?? null) === ($defaults[$key] ?? null)) {
                continue;
            }

            $label = match ($key) {
                'date_from' => __('From :date', ['date' => $value]),
                'date_to' => __('To :date', ['date' => $value]),
                'branch_id' => __('Branch #:id', ['id' => $value]),
                'customer_id' => __('Customer #:id', ['id' => $value]),
                'production_type' => str($value)->headline()->toString(),
                'margin_category' => str($value)->replace('_', ' ')->headline()->toString(),
                default => (string) $value,
            };

            $without = $filters;
            unset($without[$key]);

            $chips[] = [
                'label' => $label,
                'url' => $this->filterUrl($without),
            ];
        }

        return $chips;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    protected function hasActiveFilters(array $filters): bool
    {
        return count($this->activeFilterChips($filters)) > 0;
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @param  array<string, mixed>|null  $base
     */
    protected function filterUrl(array $overrides, ?array $base = null): string
    {
        $base ??= $this->filtersFromRequest(request());
        $query = array_filter(
            array_merge($base, $overrides),
            fn ($value) => filled($value),
        );

        return route('admin.production.costing.dashboard').'?'.http_build_query($query);
    }

    protected function marginVariant(float $marginPercent, float $revenue, float $cost): string
    {
        if ($revenue > 0 && $cost <= 0) {
            return 'draft';
        }

        if ($marginPercent < 0) {
            return 'danger';
        }

        if ($marginPercent >= self::HEALTHY_MARGIN_PERCENT) {
            return 'success';
        }

        if ($marginPercent > 0) {
            return 'warning';
        }

        return 'neutral';
    }

    protected function money(float $amount): string
    {
        return 'KES '.number_format($amount, 0);
    }
}
