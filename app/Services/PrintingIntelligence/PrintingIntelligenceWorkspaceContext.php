<?php

namespace App\Services\PrintingIntelligence;

use App\Models\Assets\MachineProfile;
use App\Models\Inventory\InventoryItem;
use App\Models\PrintingIntelligence\PrintArtworkAnalysis;
use App\Models\PrintingIntelligence\PrintArtworkInkEstimate;
use App\Models\PrintingIntelligence\PrintArtworkProductionEstimate;
use App\Models\PrintingIntelligence\PrintEstimateActualComparison;
use App\Models\PrintingIntelligence\PrintForecastSnapshot;
use App\Models\PrintingIntelligence\PrintInkProfile;
use App\Models\PrintingIntelligence\PrintQuotationEstimate;
use App\Services\Inventory\DeadStockDetectionService;
use App\Services\Inventory\InventoryVelocityService;

/**
 * Workspace context assembly for PI9.5 UI (delegates to existing PI services only).
 */
class PrintingIntelligenceWorkspaceContext
{
    public function __construct(
        protected PrintingIntelligenceGateway $gateway,
        protected MachineCostProfileService $machineCostProfile,
        protected InkCostProfileService $inkCostProfile,
        protected MaterialCostContextService $materialCostContext,
        protected InventoryVelocityService $velocityService,
        protected DeadStockDetectionService $deadStockService,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function platformOverview(int $companyId, ?int $branchId = null): array
    {
        $filters = ['company_id' => $companyId, 'branch_id' => $branchId];
        $baseMetrics = $this->gateway->overviewMetrics($companyId, $branchId);

        $accuracy = auth()->user()?->can('printing.estimate-actual.view')
            ? $this->gateway->accuracyAnalyticsContext($filters)
            : ['summary' => []];
        $profitability = auth()->user()?->can('printing.profitability.view')
            ? $this->gateway->profitabilityOverview($companyId, $branchId, ['days' => 90])
            : null;
        $forecast = auth()->user()?->can('printing.executive.view')
            ? $this->gateway->forecastOverview($companyId, $filters)
            : null;

        return array_merge($baseMetrics, [
            'artwork_analyses' => PrintArtworkAnalysis::query()->where('company_id', $companyId)->count(),
            'ink_estimates' => PrintArtworkInkEstimate::query()->where('company_id', $companyId)->count(),
            'machine_estimates' => PrintArtworkProductionEstimate::query()->where('company_id', $companyId)->count(),
            'quotation_estimates' => PrintQuotationEstimate::query()->where('company_id', $companyId)->count(),
            'estimate_accuracy' => $accuracy['summary']['average_accuracy_score'] ?? null,
            'total_profit' => $profitability['summary']['total_profit'] ?? null,
            'average_margin' => $profitability['summary']['average_margin'] ?? null,
            'forecast_confidence' => $forecast['forecast_revenue']['confidence_score'] ?? null,
            'forecast_snapshots' => PrintForecastSnapshot::query()->where('company_id', $companyId)->count(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function machineIntelligence(int $companyId, ?int $branchId = null): array
    {
        $filters = ['company_id' => $companyId, 'branch_id' => $branchId, 'days' => 90];
        $profitability = app(MachineProfitabilityService::class)->analyze($filters);
        $capacity = app(CapacityForecastService::class)->forecast($filters);

        $profitByMachine = collect($profitability['rankings'] ?? [])->keyBy('machine_profile_id');
        $capacityByMachine = collect($capacity['machines'] ?? [])->keyBy('machine_profile_id');

        $machines = MachineProfile::query()
            ->where('company_id', $companyId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->orderBy('machine_code')
            ->get()
            ->map(function (MachineProfile $machine) use ($profitByMachine, $capacityByMachine) {
                $profit = $profitByMachine->get($machine->id, []);
                $cap = $capacityByMachine->get($machine->id, []);

                return [
                    'machine_profile_id' => $machine->id,
                    'machine_name' => $machine->machine_code,
                    'machine_type' => $machine->machine_type,
                    'cost_per_hour' => $this->machineCostProfile->costPerHour($machine),
                    'setup_minutes' => $machine->average_setup_minutes,
                    'output_per_hour' => $machine->target_output_per_hour ?? $machine->capacity_per_hour,
                    'profit' => $profit['profit'] ?? null,
                    'margin_percent' => $profit['margin_percent'] ?? null,
                    'utilization_percent' => $cap['current_utilization_percent'] ?? null,
                    'forecast_utilization_percent' => $cap['forecast_utilization_percent'] ?? null,
                    'is_bottleneck' => $cap['is_bottleneck'] ?? false,
                ];
            });

        $sortedByProfit = $machines->sortByDesc('profit');
        $sortedByUtil = $machines->sortByDesc('utilization_percent');
        $sortedByCost = $machines->sortBy('cost_per_hour');

        return [
            'machines' => $machines->values()->all(),
            'summary' => [
                'most_profitable' => $profitability['best_performing'] ?? $sortedByProfit->first(),
                'highest_utilization' => $sortedByUtil->first(),
                'lowest_cost' => $sortedByCost->first(),
                'capacity_bottleneck' => collect($capacity['bottlenecks'] ?? [])->first(),
            ],
            'profitability' => $profitability,
            'capacity' => $capacity,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function inkIntelligence(int $companyId, ?int $branchId = null): array
    {
        $profiles = PrintInkProfile::query()
            ->where('company_id', $companyId)
            ->where('active', true)
            ->orderBy('name')
            ->get();

        $estimateCounts = PrintArtworkInkEstimate::query()
            ->where('company_id', $companyId)
            ->whereIn('ink_profile_id', $profiles->pluck('id'))
            ->selectRaw('ink_profile_id, COUNT(*) as aggregate')
            ->groupBy('ink_profile_id')
            ->pluck('aggregate', 'ink_profile_id');

        $profiles = $profiles->map(function (PrintInkProfile $profile) use ($companyId, $estimateCounts) {
                $estimates = (int) ($estimateCounts[$profile->id] ?? 0);

                $costPerMl = $this->inkCostProfile->costPerMl($profile);
                $yieldPage = $this->inkCostProfile->yieldPerPage($profile);

                return [
                    'ink_profile_id' => $profile->id,
                    'name' => $profile->name,
                    'ink_type' => $profile->ink_type?->value ?? (string) $profile->ink_type,
                    'cost_per_ml' => $costPerMl,
                    'yield_per_page' => $yieldPage,
                    'yield_per_sq_m' => $this->inkCostProfile->yieldPerSquareMeter($profile),
                    'cartridge_cost' => $this->inkCostProfile->currentCartridgeCost($profile),
                    'consumption_estimate_count' => $estimates,
                    'forecast_usage' => $estimates > 0 ? round($estimates * 1.05, 1) : null,
                ];
            });

        $inventoryRisk = app(InventoryRiskForecastService::class)->forecast(['company_id' => $companyId, 'branch_id' => $branchId]);
        $inkRisk = collect($inventoryRisk['categories'] ?? [])->firstWhere('category', 'ink');

        $coverageCount = PrintArtworkAnalysis::query()
            ->where('company_id', $companyId)
            ->whereNotNull('rgb_coverage_percent')
            ->count();

        return [
            'profiles' => $profiles->values()->all(),
            'summary' => [
                'highest_cost' => $profiles->sortByDesc('cost_per_ml')->first(),
                'most_consumed' => $profiles->sortByDesc('consumption_estimate_count')->first(),
                'lowest_yield' => $profiles->filter(fn ($p) => $p['yield_per_page'] !== null)->sortByDesc('yield_per_page')->last(),
                'highest_margin_risk' => $inkRisk,
            ],
            'coverage_analysis_count' => $coverageCount,
            'inventory_risk' => $inkRisk,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function materialIntelligence(int $companyId, ?int $branchId = null): array
    {
        $filters = ['company_id' => $companyId, 'branch_id' => $branchId];
        $window = (int) config('inventory_intelligence.default_snapshot_window', 30);
        $velocityCounts = $this->velocityService->overviewCounts($companyId, $branchId, $window);
        $deadStock = $this->deadStockService->detect($companyId, array_filter(['branch_id' => $branchId]));
        $inventoryRisk = app(InventoryRiskForecastService::class)->forecast($filters);

        $items = InventoryItem::query()
            ->where('company_id', $companyId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->where('is_active', true)
            ->orderBy('item_name')
            ->limit(100)
            ->get()
            ->map(function (InventoryItem $item) {
                $ctx = $this->materialCostContext->context($item->id);

                return [
                    'item_id' => $item->id,
                    'name' => $item->item_name,
                    'sku' => $item->sku,
                    'current_cost' => $ctx['current_cost'] ?? 0,
                    'stock' => $ctx['stock_availability'] ?? 0,
                    'velocity_class' => $ctx['velocity']['velocity_class'] ?? null,
                    'risk_level' => $ctx['risk_level'] ?? null,
                    'forecast_risk' => $ctx['risk_level'] ?? null,
                ];
            });

        $topCost = $items->sortByDesc('current_cost')->first();
        $highestVelocity = $items->filter(fn ($i) => ($i['velocity_class'] ?? '') === 'fast_moving')->first()
            ?? $items->first();

        return [
            'materials' => $items->values()->all(),
            'dead_stock' => $deadStock->take(20)->map(fn ($row) => [
                'name' => $row['item']->item_name,
                'sku' => $row['item']->sku,
                'balance' => $row['balance'],
                'estimated_value' => $row['estimated_value'],
                'days_inactive' => $row['days_inactive'],
            ])->values()->all(),
            'summary' => [
                'top_material_cost' => $topCost,
                'highest_velocity' => $highestVelocity,
                'dead_stock_value' => (float) ($velocityCounts['dead_stock_value'] ?? 0),
                'material_risk_level' => collect($inventoryRisk['categories'] ?? [])->firstWhere('category', 'paper'),
            ],
            'velocity_counts' => $velocityCounts,
            'inventory_risk' => $inventoryRisk,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function costIntelligence(int $companyId, ?int $branchId = null): array
    {
        $filters = ['company_id' => $companyId, 'branch_id' => $branchId, 'days' => 90];
        $canEstimateActual = auth()->user()?->can('printing.estimate-actual.view');
        $analytics = $canEstimateActual ? $this->gateway->accuracyAnalyticsContext($filters) : null;
        $leakage = $canEstimateActual ? $this->gateway->marginLeakage($filters) : null;
        $profitability = auth()->user()?->can('printing.profitability.view')
            ? $this->gateway->profitabilityOverview($companyId, $branchId, $filters)
            : null;
        $calibration = auth()->user()?->can('printing.calibration.view')
            ? $this->gateway->calibrationRecommendations($companyId)
            : null;
        $formulaVersions = $this->gateway->formulaVersions($companyId);

        $jobQuery = \App\Models\PrintingIntelligence\PrintProfitabilitySnapshot::query()
            ->where('company_id', $companyId)
            ->where('snapshot_type', \App\Enums\ProfitabilitySnapshotType::Job)
            ->where('snapshot_date', '>=', now()->subDays(90)->toDateString());

        PrintingIntelligenceScope::applyBranchScope($jobQuery, $filters);

        $jobSnapshots = $jobQuery->get();

        $totalRevenue = $jobSnapshots->sum(fn ($r) => (float) $r->revenue);
        $composition = [
            'material' => $jobSnapshots->sum(fn ($r) => (float) $r->material_cost),
            'ink' => $jobSnapshots->sum(fn ($r) => (float) $r->ink_cost),
            'machine' => $jobSnapshots->sum(fn ($r) => (float) $r->machine_cost),
            'labour' => $jobSnapshots->sum(fn ($r) => (float) $r->labour_cost),
            'overhead' => $jobSnapshots->sum(fn ($r) => (float) $r->overhead_cost),
        ];
        $totalCost = array_sum($composition) ?: 1;
        $compositionPercent = collect($composition)->map(fn ($v) => round(($v / $totalCost) * 100, 1))->all();

        return [
            'summary' => [
                'average_job_cost' => $jobSnapshots->count() > 0
                    ? round($jobSnapshots->avg(fn ($r) => (float) $r->total_cost), 2)
                    : null,
                'average_accuracy' => $canEstimateActual ? ($analytics['summary']['average_accuracy_score'] ?? null) : null,
                'largest_variance_driver' => $canEstimateActual ? ($leakage['top_profit_erosion_drivers'][0] ?? null) : null,
                'formula_version' => config('printing_intelligence.quotation_formula_version', 'PI5-V1'),
            ],
            'composition' => $composition,
            'composition_percent' => $compositionPercent,
            'analytics' => $analytics,
            'leakage' => $leakage,
            'profitability' => $profitability,
            'calibration' => $calibration,
            'formula_versions' => $formulaVersions,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function quotationIntelligence(int $companyId, ?int $branchId = null): array
    {
        $estimates = PrintQuotationEstimate::query()
            ->where('company_id', $companyId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->with(['quotation:id,quotation_number,total_amount', 'analysis:id,original_filename'])
            ->latest('id')
            ->limit(50)
            ->get();

        $comparisonMap = PrintEstimateActualComparison::query()
            ->whereIn('print_quotation_estimate_id', $estimates->pluck('id'))
            ->latest('compared_at')
            ->get()
            ->unique('print_quotation_estimate_id')
            ->keyBy('print_quotation_estimate_id');

        $estimates = $estimates->map(function (PrintQuotationEstimate $estimate) use ($comparisonMap) {
                $comparison = $comparisonMap->get($estimate->id);

                $recommended = (float) ($estimate->recommended_selling_price ?? 0);
                $actualCost = $comparison ? (float) $comparison->actual_total_cost : null;
                $actualMargin = ($recommended > 0 && $actualCost !== null)
                    ? round((($recommended - $actualCost) / $recommended) * 100, 2)
                    : null;

                return [
                    'estimate_id' => $estimate->id,
                    'quotation_number' => $estimate->quotation?->quotation_number,
                    'quotation_id' => $estimate->quotation_id,
                    'estimated_cost' => (float) $estimate->estimated_total_cost,
                    'recommended_price' => $recommended,
                    'expected_margin_percent' => $estimate->expected_margin_percent,
                    'actual_cost' => $actualCost,
                    'actual_margin_percent' => $actualMargin,
                    'accuracy_score' => $comparison?->accuracy_score,
                    'applied' => $estimate->applied_at !== null,
                    'formula_version' => $estimate->formula_version,
                    'created_at' => $estimate->created_at?->toDateTimeString(),
                ];
            });

        $withMargin = $estimates->filter(fn ($e) => $e['expected_margin_percent'] !== null);
        $withAccuracy = $estimates->filter(fn ($e) => $e['accuracy_score'] !== null);

        $mostProfitable = $estimates->sortByDesc('actual_margin_percent')->first();
        $mostUnderpriced = $estimates->filter(fn ($e) => $e['actual_margin_percent'] !== null && $e['actual_margin_percent'] < 15)->sortBy('actual_margin_percent')->first();

        return [
            'estimates' => $estimates->values()->all(),
            'summary' => [
                'average_recommended_margin' => $withMargin->isNotEmpty()
                    ? round($withMargin->avg('expected_margin_percent'), 2)
                    : null,
                'average_quote_accuracy' => $withAccuracy->isNotEmpty()
                    ? round($withAccuracy->avg('accuracy_score'), 2)
                    : null,
                'most_profitable_quote' => $mostProfitable,
                'most_underpriced_quote' => $mostUnderpriced,
                'total_estimates' => $estimates->count(),
                'applied_count' => $estimates->where('applied', true)->count(),
            ],
        ];
    }
}
