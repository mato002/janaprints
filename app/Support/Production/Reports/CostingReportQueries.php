<?php

namespace App\Support\Production\Reports;

use App\Enums\ProductionType;
use App\Models\Production\JobCostSheet;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CostingReportQueries
{
    public const PER_PAGE = 25;

    public function hasTable(string $table): bool
    {
        return Schema::hasTable($table);
    }

    public function money(float $amount): string
    {
        return 'KES '.number_format($amount, 0);
    }

    public function qty(float $amount): string
    {
        return number_format($amount, 2);
    }

    public function percent(?float $value): string
    {
        return $value === null ? '—' : number_format($value, 1).'%';
    }

    public function productionCost(float $labor, float $machine, float $overhead, float $finishing): float
    {
        return round($labor + $machine + $overhead + $finishing, 2);
    }

    public function marginPercent(float $revenue, float $profit): float
    {
        return $revenue > 0 ? round(($profit / $revenue) * 100, 2) : 0.0;
    }

    public function withPage(CostingReportScope $scope, int $page): CostingReportScope
    {
        return new CostingReportScope(
            companyId: $scope->companyId,
            branchId: $scope->branchId,
            fromDate: $scope->fromDate,
            toDate: $scope->toDate,
            customerId: $scope->customerId,
            productionType: $scope->productionType,
            jobCardId: $scope->jobCardId,
            search: $scope->search,
            tab: $scope->tab,
            page: $page,
        );
    }

    public function baseCostSheetQuery(CostingReportScope $scope): Builder
    {
        $query = JobCostSheet::query()
            ->where('job_cost_sheets.company_id', $scope->companyId);

        if ($scope->branchId !== null) {
            $query->where('job_cost_sheets.branch_id', $scope->branchId);
        }

        $query->where('job_cost_sheets.calculated_at', '>=', $scope->fromDate.' 00:00:00')
            ->where('job_cost_sheets.calculated_at', '<=', $scope->toDate.' 23:59:59');

        if ($scope->customerId !== null) {
            $query->whereHas('jobCard', fn (Builder $q) => $q->where('customer_id', $scope->customerId));
        }

        if ($scope->productionType !== null && $scope->productionType !== '') {
            $query->whereHas('jobCard', fn (Builder $q) => $q->where('production_type', $scope->productionType));
        }

        if ($scope->jobCardId !== null) {
            $query->where('job_cost_sheets.production_job_card_id', $scope->jobCardId);
        }

        if ($scope->search !== '') {
            $term = '%'.$scope->search.'%';
            $query->whereHas('jobCard', fn (Builder $q) => $q->where('job_card_number', 'like', $term));
        }

        return $query;
    }

    protected function baseConsumptionQuery(CostingReportScope $scope): QueryBuilder
    {
        $query = DB::table('production_material_consumptions as pmc')
            ->join('production_job_cards as pjc', 'pjc.id', '=', 'pmc.production_job_card_id')
            ->where('pjc.company_id', $scope->companyId);

        if ($scope->branchId !== null) {
            $query->where('pjc.branch_id', $scope->branchId);
        }

        $query->where('pmc.consumed_at', '>=', $scope->fromDate.' 00:00:00')
            ->where('pmc.consumed_at', '<=', $scope->toDate.' 23:59:59');

        if ($scope->customerId !== null) {
            $query->where('pjc.customer_id', $scope->customerId);
        }

        if ($scope->productionType !== null && $scope->productionType !== '') {
            $query->where('pjc.production_type', $scope->productionType);
        }

        if ($scope->jobCardId !== null) {
            $query->where('pmc.production_job_card_id', $scope->jobCardId);
        }

        if ($scope->search !== '') {
            $term = '%'.$scope->search.'%';
            $query->where('pjc.job_card_number', 'like', $term);
        }

        return $query;
    }

    /**
     * @return array{revenue: float, total_cost: float, gross_profit: float, margin_percent: float, job_count: int}
     */
    public function scopedTotals(CostingReportScope $scope): array
    {
        if (! $this->hasTable('job_cost_sheets')) {
            return ['revenue' => 0, 'total_cost' => 0, 'gross_profit' => 0, 'margin_percent' => 0, 'job_count' => 0];
        }

        $row = (clone $this->baseCostSheetQuery($scope))
            ->selectRaw('COALESCE(SUM(job_cost_sheets.revenue), 0) as revenue')
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
            'margin_percent' => $this->marginPercent($revenue, $profit),
            'job_count' => (int) ($row->job_count ?? 0),
        ];
    }

    public function categoryConsumptionValue(CostingReportScope $scope, string $categoryCode): float
    {
        if (! $this->hasTable('production_material_consumptions') || ! $this->hasTable('inventory_items')) {
            return 0.0;
        }

        $query = $this->baseConsumptionQuery($scope)
            ->join('inventory_items as ii', 'ii.id', '=', 'pmc.inventory_item_id');

        if ($this->hasTable('inventory_categories')) {
            $query->join('inventory_categories as ic', 'ic.id', '=', 'ii.inventory_category_id')
                ->where('ic.code', $categoryCode);
        }

        return round((float) $query->selectRaw('COALESCE(SUM(pmc.quantity * pmc.unit_cost), 0) as total')->value('total'), 2);
    }

    public function paginateJobProfitability(CostingReportScope $scope): LengthAwarePaginator
    {
        $query = (clone $this->baseCostSheetQuery($scope))
            ->with(['jobCard.customer:id,company_name', 'jobCard:id,job_card_number,customer_id'])
            ->orderByDesc('job_cost_sheets.calculated_at');

        return $this->paginateEloquent($query, $scope, fn (JobCostSheet $sheet) => [
            'job_number' => $sheet->jobCard?->job_card_number ?? __('Unknown'),
            'customer' => $sheet->jobCard?->customer?->company_name ?? __('Unknown'),
            'revenue' => $this->money((float) $sheet->revenue),
            'material_cost' => $this->money((float) $sheet->material_cost),
            'production_cost' => $this->money($this->productionCost(
                (float) $sheet->labor_cost,
                (float) $sheet->machine_cost,
                (float) $sheet->overhead_cost,
                (float) $sheet->finishing_cost,
            )),
            'outsourced_cost' => $this->money((float) $sheet->outsourced_cost),
            'total_cost' => $this->money((float) $sheet->total_cost),
            'profit' => $this->money((float) $sheet->gross_profit),
            'margin_percent' => $this->percent((float) $sheet->gross_margin_percent),
        ]);
    }

    public function paginateProductCostAnalysis(CostingReportScope $scope): LengthAwarePaginator
    {
        $rows = (clone $this->baseCostSheetQuery($scope))
            ->join('production_job_cards', 'production_job_cards.id', '=', 'job_cost_sheets.production_job_card_id')
            ->selectRaw('production_job_cards.production_type as production_type')
            ->selectRaw('COALESCE(SUM(job_cost_sheets.material_cost), 0) as material_cost')
            ->selectRaw('COALESCE(SUM(job_cost_sheets.labor_cost), 0) as labor_cost')
            ->selectRaw('COALESCE(SUM(job_cost_sheets.machine_cost), 0) as machine_cost')
            ->selectRaw('COALESCE(SUM(job_cost_sheets.overhead_cost), 0) as overhead_cost')
            ->selectRaw('COALESCE(SUM(job_cost_sheets.finishing_cost), 0) as finishing_cost')
            ->selectRaw('COALESCE(SUM(job_cost_sheets.total_cost), 0) as total_cost')
            ->groupBy('production_job_cards.production_type')
            ->orderByDesc('total_cost')
            ->get()
            ->map(fn ($row) => [
                'product' => str(ProductionType::tryFrom($row->production_type)?->value ?? $row->production_type)->headline(),
                'material_cost' => $this->money((float) $row->material_cost),
                'labor_cost' => $this->money((float) $row->labor_cost),
                'machine_cost' => $this->money((float) $row->machine_cost),
                'overheads' => $this->money((float) $row->overhead_cost + (float) $row->finishing_cost),
                'total_cost' => $this->money((float) $row->total_cost),
            ]);

        return $this->paginateCollection($rows, $scope);
    }

    public function paginatePaperConsumption(CostingReportScope $scope): LengthAwarePaginator
    {
        return $this->paginateMaterialConsumption($scope, 'PAPER', includeWaste: true);
    }

    public function paginateInkConsumption(CostingReportScope $scope): LengthAwarePaginator
    {
        return $this->paginateMaterialConsumption($scope, 'INK', includeWaste: false);
    }

    protected function paginateMaterialConsumption(CostingReportScope $scope, string $categoryCode, bool $includeWaste = true): LengthAwarePaginator
    {
        if (! $this->hasTable('production_material_consumptions')) {
            return $this->emptyPaginator($scope);
        }

        $hasWastage = Schema::hasColumn('production_material_consumptions', 'is_wastage');

        $query = $this->baseConsumptionQuery($scope)
            ->join('inventory_items as ii', 'ii.id', '=', 'pmc.inventory_item_id');

        if ($this->hasTable('inventory_categories')) {
            $query->join('inventory_categories as ic', 'ic.id', '=', 'ii.inventory_category_id')
                ->where('ic.code', $categoryCode);
        }

        $wastageSelect = $hasWastage
            ? 'COALESCE(SUM(CASE WHEN pmc.is_wastage = 1 THEN pmc.quantity ELSE 0 END), 0) as wastage_qty'
            : '0 as wastage_qty';

        $rows = $query
            ->selectRaw('ii.item_name as material_type')
            ->selectRaw('COALESCE(SUM(pmc.quantity), 0) as consumed_qty')
            ->selectRaw('COALESCE(SUM(pmc.quantity * pmc.unit_cost), 0) as cost')
            ->selectRaw($wastageSelect)
            ->groupBy('ii.id', 'ii.item_name')
            ->orderByDesc('cost')
            ->get()
            ->map(function ($row) use ($hasWastage, $includeWaste) {
                $consumed = (float) $row->consumed_qty;
                $mapped = [
                    'material_type' => $row->material_type,
                    'consumed_qty' => $this->qty($consumed),
                    'cost' => $this->money((float) $row->cost),
                ];

                if ($includeWaste) {
                    $wastage = (float) $row->wastage_qty;
                    $wastePercent = $hasWastage && $consumed > 0
                        ? round(($wastage / $consumed) * 100, 1)
                        : null;
                    $mapped['waste_percent'] = $this->percent($wastePercent);
                }

                return $mapped;
            });

        return $this->paginateCollection($rows, $scope);
    }

    public function paginateProductionCostSummary(CostingReportScope $scope): LengthAwarePaginator
    {
        $rows = (clone $this->baseCostSheetQuery($scope))
            ->join('production_job_cards', 'production_job_cards.id', '=', 'job_cost_sheets.production_job_card_id')
            ->selectRaw('production_job_cards.production_type as department')
            ->selectRaw('COUNT(*) as jobs')
            ->selectRaw('COALESCE(SUM(job_cost_sheets.revenue), 0) as revenue')
            ->selectRaw('COALESCE(SUM(job_cost_sheets.total_cost), 0) as cost')
            ->selectRaw('COALESCE(SUM(job_cost_sheets.gross_profit), 0) as profit')
            ->groupBy('production_job_cards.production_type')
            ->orderByDesc('revenue')
            ->get()
            ->map(fn ($row) => [
                'department' => str(ProductionType::tryFrom($row->department)?->value ?? $row->department)->headline(),
                'jobs' => (string) (int) $row->jobs,
                'revenue' => $this->money((float) $row->revenue),
                'cost' => $this->money((float) $row->cost),
                'profit' => $this->money((float) $row->profit),
            ]);

        return $this->paginateCollection($rows, $scope);
    }

    public function paginateCustomerProfitability(CostingReportScope $scope): LengthAwarePaginator
    {
        $rows = (clone $this->baseCostSheetQuery($scope))
            ->join('production_job_cards', 'production_job_cards.id', '=', 'job_cost_sheets.production_job_card_id')
            ->leftJoin('customers', 'customers.id', '=', 'production_job_cards.customer_id')
            ->selectRaw('customers.company_name as customer')
            ->selectRaw('COALESCE(SUM(job_cost_sheets.revenue), 0) as revenue')
            ->selectRaw('COALESCE(SUM(job_cost_sheets.total_cost), 0) as cost')
            ->selectRaw('COALESCE(SUM(job_cost_sheets.gross_profit), 0) as profit')
            ->groupBy('production_job_cards.customer_id', 'customers.company_name')
            ->orderByDesc('profit')
            ->get()
            ->map(function ($row) {
                $revenue = (float) $row->revenue;
                $profit = (float) $row->profit;

                return [
                    'customer' => $row->customer ?? __('Unknown'),
                    'revenue' => $this->money($revenue),
                    'cost' => $this->money((float) $row->cost),
                    'margin' => $this->percent($this->marginPercent($revenue, $profit)),
                ];
            });

        return $this->paginateCollection($rows, $scope);
    }

    public function paginateMonthlyMargin(CostingReportScope $scope): LengthAwarePaginator
    {
        $rows = (clone $this->baseCostSheetQuery($scope))
            ->selectRaw("DATE_FORMAT(job_cost_sheets.calculated_at, '%Y-%m') as month_key")
            ->selectRaw('COALESCE(SUM(job_cost_sheets.revenue), 0) as revenue')
            ->selectRaw('COALESCE(SUM(job_cost_sheets.total_cost), 0) as cost')
            ->selectRaw('COALESCE(SUM(job_cost_sheets.gross_profit), 0) as profit')
            ->groupBy('month_key')
            ->orderBy('month_key')
            ->get()
            ->map(function ($row) {
                $revenue = (float) $row->revenue;
                $profit = (float) $row->profit;

                return [
                    'month' => $row->month_key,
                    'revenue' => $this->money($revenue),
                    'cost' => $this->money((float) $row->cost),
                    'profit' => $this->money($profit),
                    'margin_percent' => $this->percent($this->marginPercent($revenue, $profit)),
                ];
            });

        return $this->paginateCollection($rows, $scope);
    }

    /**
     * @return array<string, LengthAwarePaginator>
     */
    public function paginateForTab(CostingReportScope $scope): LengthAwarePaginator
    {
        return match ($scope->tab) {
            'product_cost' => $this->paginateProductCostAnalysis($scope),
            'paper_consumption' => $this->paginatePaperConsumption($scope),
            'ink_consumption' => $this->paginateInkConsumption($scope),
            'production_cost_summary' => $this->paginateProductionCostSummary($scope),
            'customer_profitability' => $this->paginateCustomerProfitability($scope),
            'monthly_margin' => $this->paginateMonthlyMargin($scope),
            default => $this->paginateJobProfitability($scope),
        };
    }

    /**
     * @param  callable(mixed): array<string, string>  $mapper
     */
    protected function paginateEloquent(Builder $query, CostingReportScope $scope, callable $mapper): LengthAwarePaginator
    {
        $total = (clone $query)->count();
        $items = $query
            ->forPage($scope->page, self::PER_PAGE)
            ->get()
            ->map($mapper);

        return new Paginator($items, $total, self::PER_PAGE, $scope->page, [
            'path' => Paginator::resolveCurrentPath(),
            'pageName' => 'page',
        ]);
    }

    protected function paginateCollection(Collection $rows, CostingReportScope $scope): LengthAwarePaginator
    {
        $total = $rows->count();
        $items = $rows->forPage($scope->page, self::PER_PAGE)->values();

        return new Paginator($items, $total, self::PER_PAGE, $scope->page, [
            'path' => Paginator::resolveCurrentPath(),
            'pageName' => 'page',
        ]);
    }

    protected function emptyPaginator(CostingReportScope $scope): LengthAwarePaginator
    {
        return new Paginator(collect(), 0, self::PER_PAGE, $scope->page, [
            'path' => Paginator::resolveCurrentPath(),
            'pageName' => 'page',
        ]);
    }
}
