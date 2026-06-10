<?php

namespace App\Http\Controllers\Admin\PrintingIntelligence;

use App\Http\Controllers\Controller;
use App\Models\PrintingIntelligence\PrintAdvisorRecommendation;
use App\Models\PrintingIntelligence\PrintCalibrationRule;
use App\Models\PrintingIntelligence\PrintEstimateActualComparison;
use App\Services\PrintingIntelligence\Advisor\AdvisorRecommendationWorkflowService;
use App\Services\PrintingIntelligence\Advisor\PrintOperationsAdvisorService;
use App\Services\PrintingIntelligence\CalibrationApprovalService;
use App\Services\PrintingIntelligence\CalibrationImpactSimulationService;
use App\Services\PrintingIntelligence\CalibrationRecommendationService;
use App\Services\PrintingIntelligence\EstimateAccuracyAnalyticsService;
use App\Services\PrintingIntelligence\EstimateActualComparisonService;
use App\Services\PrintingIntelligence\PrintingIntelligenceGateway;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class PrintingIntelligenceController extends Controller
{
    public function __construct(
        protected PrintingIntelligenceGateway $gateway,
    ) {}

    public function overview(): View
    {
        $this->authorizeView();

        [$companyId, $branchId] = $this->tenantScope();

        return view('admin.printing-intelligence.overview', [
            'metrics' => $this->gateway->platformOverviewContext($companyId, $branchId),
            'config' => config('printing_intelligence'),
        ]);
    }

    public function materialIntelligence(Request $request): View
    {
        $this->authorizeView();

        [$companyId, $branchId] = $this->tenantScope();
        $tab = $this->resolveTab($request, ['overview', 'materials', 'cost-trends', 'velocity', 'dead-stock', 'forecasting']);

        return view('admin.printing-intelligence.material-intelligence', [
            'tab' => $tab,
            'context' => $this->gateway->materialIntelligenceContext($companyId, $branchId),
            'filters' => ['tab' => $tab],
        ]);
    }

    public function machineIntelligence(Request $request): View
    {
        $this->authorizeView();

        [$companyId, $branchId] = $this->tenantScope();
        $tab = $this->resolveTab($request, ['overview', 'profiles', 'costing', 'utilization', 'profitability', 'forecasting']);

        return view('admin.printing-intelligence.machine-intelligence', [
            'tab' => $tab,
            'context' => $this->gateway->machineIntelligenceContext($companyId, $branchId),
            'filters' => ['tab' => $tab],
        ]);
    }

    public function inkIntelligence(Request $request): View
    {
        $this->authorizeView();

        [$companyId, $branchId] = $this->tenantScope();
        $tab = $this->resolveTab($request, ['overview', 'profiles', 'coverage', 'costing', 'consumption', 'forecasting']);

        return view('admin.printing-intelligence.ink-intelligence', [
            'tab' => $tab,
            'context' => $this->gateway->inkIntelligenceContext($companyId, $branchId),
            'filters' => ['tab' => $tab],
        ]);
    }

    public function costIntelligence(Request $request): View
    {
        $this->authorizeView();

        [$companyId, $branchId] = $this->tenantScope();
        $tab = $this->resolveTab($request, ['overview', 'composition', 'accuracy', 'calibration', 'profitability']);

        return view('admin.printing-intelligence.cost-intelligence', [
            'tab' => $tab,
            'context' => $this->gateway->costIntelligenceContext($companyId, $branchId),
            'filters' => ['tab' => $tab],
        ]);
    }

    public function quotationIntelligence(Request $request): View
    {
        $this->authorizeView();

        [$companyId, $branchId] = $this->tenantScope();
        $tab = $this->resolveTab($request, ['overview', 'estimates', 'applied', 'accuracy', 'profitability']);

        return view('admin.printing-intelligence.quotation-intelligence', [
            'tab' => $tab,
            'context' => $this->gateway->quotationIntelligenceContext($companyId, $branchId),
            'filters' => ['tab' => $tab],
        ]);
    }

    /**
     * @param  list<string>  $allowed
     */
    protected function resolveTab(Request $request, array $allowed): string
    {
        $tab = $request->query('tab', 'overview');

        return in_array($tab, $allowed, true) ? $tab : 'overview';
    }

    public function estimateVsActual(Request $request): View
    {
        abort_unless(auth()->user()?->can('printing.estimate-actual.view'), 403);

        [$companyId, $branchId] = $this->tenantScope();
        $tab = in_array($request->query('tab'), ['overview', 'comparisons', 'analytics', 'drivers', 'recommendations'], true)
            ? $request->query('tab')
            : 'overview';

        $filters = array_filter([
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'from' => $request->query('from'),
            'to' => $request->query('to'),
            'variance_class' => $request->query('variance_class'),
        ]);

        $analytics = app(EstimateAccuracyAnalyticsService::class)->aggregate($filters);
        $comparisons = app(EstimateAccuracyAnalyticsService::class)
            ->comparisonsQuery($filters)
            ->paginate(20)
            ->withQueryString();

        return view('admin.printing-intelligence.estimate-vs-actual', [
            'tab' => $tab,
            'analytics' => $analytics,
            'comparisons' => $comparisons,
            'filters' => $filters,
            'config' => config('printing_intelligence'),
        ]);
    }

    public function estimateVsActualShow(PrintEstimateActualComparison $comparison): View
    {
        abort_unless(auth()->user()?->can('printing.estimate-actual.view'), 403);
        $this->authorize('view', $comparison);

        $comparison->load(['quotation', 'jobCard', 'jobCostSheet', 'quotationEstimate', 'productionOutput']);

        return view('admin.printing-intelligence.estimate-vs-actual-show', [
            'comparison' => $comparison,
        ]);
    }

    public function runEstimateComparison(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()?->can('printing.estimate-actual.compare'), 403);

        $service = app(EstimateActualComparisonService::class);

        if ($estimateId = $request->input('estimate_id')) {
            $estimate = \App\Models\PrintingIntelligence\PrintQuotationEstimate::query()
                ->where('company_id', tenant()->companyId() ?? auth()->user()?->company_id)
                ->findOrFail((int) $estimateId);
            $service->compareEstimate($estimate);
        } elseif ($jobId = $request->input('job_id')) {
            $job = \App\Models\Production\ProductionJobCard::query()
                ->where('company_id', tenant()->companyId() ?? auth()->user()?->company_id)
                ->findOrFail((int) $jobId);
            $service->compareJob($job);
        } elseif ($quotationId = $request->input('quotation_id')) {
            $quotation = \App\Models\Sales\Quotation::query()
                ->where('company_id', tenant()->companyId() ?? auth()->user()?->company_id)
                ->findOrFail((int) $quotationId);
            $service->compareQuotation($quotation);
        }

        return redirect()
            ->route('admin.printing-intelligence.estimate-vs-actual', ['tab' => 'comparisons'])
            ->with('status', __('Estimate vs actual comparison completed.'));
    }

    public function configuration(): View
    {
        abort_unless(auth()->user()?->can('printing.intelligence.configure'), 403);

        return view('admin.printing-intelligence.configuration', [
            'config' => config('printing_intelligence'),
        ]);
    }

    public function calibrationGovernance(Request $request): View
    {
        abort_unless(auth()->user()?->can('printing.calibration.view'), 403);

        [$companyId] = $this->tenantScope();
        $tab = in_array($request->query('tab'), [
            'overview', 'recommendations', 'pending', 'active', 'history', 'simulation',
        ], true) ? $request->query('tab') : 'overview';

        $analytics = app(EstimateAccuracyAnalyticsService::class)->aggregate(['company_id' => $companyId]);
        $recommendations = PrintCalibrationRule::query()
            ->where('company_id', $companyId)
            ->whereIn('status', ['draft', 'pending_review'])
            ->latest('id')
            ->get();
        $pending = PrintCalibrationRule::query()
            ->where('company_id', $companyId)
            ->where('status', 'pending_review')
            ->latest('id')
            ->get();
        $active = PrintCalibrationRule::query()
            ->where('company_id', $companyId)
            ->where('status', 'approved')
            ->latest('approved_at')
            ->get();
        $history = \App\Models\PrintingIntelligence\PrintCalibrationRuleHistory::query()
            ->where('company_id', $companyId)
            ->latest('recorded_at')
            ->limit(50)
            ->with('rule')
            ->get();

        $simulationRule = $request->query('rule_id')
            ? PrintCalibrationRule::query()
                ->where('company_id', $companyId)
                ->find((int) $request->query('rule_id'))
            : $recommendations->first();

        $simulation = $simulationRule
            ? app(CalibrationImpactSimulationService::class)->simulate($simulationRule)
            : null;

        return view('admin.printing-intelligence.calibration-governance', [
            'tab' => $tab,
            'analytics' => $analytics,
            'recommendations' => $recommendations,
            'pending' => $pending,
            'active' => $active,
            'history' => $history,
            'simulation' => $simulation,
            'simulationRule' => $simulationRule,
            'profile' => $this->gateway->activeCostingProfile($companyId),
            'formulaVersions' => $this->gateway->formulaVersions($companyId),
            'config' => config('printing_intelligence'),
        ]);
    }

    public function submitCalibrationRule(PrintCalibrationRule $rule): RedirectResponse
    {
        abort_unless(auth()->user()?->can('printing.calibration.manage'), 403);
        $this->authorize('update', $rule);

        app(CalibrationApprovalService::class)->submitForReview($rule, auth()->user());

        return redirect()
            ->route('admin.printing-intelligence.calibration-governance', ['tab' => 'pending'])
            ->with('status', __('Calibration rule submitted for review.'));
    }

    public function approveCalibrationRule(Request $request, PrintCalibrationRule $rule): RedirectResponse
    {
        abort_unless(auth()->user()?->can('printing.calibration.approve'), 403);
        $this->authorize('update', $rule);

        app(CalibrationApprovalService::class)->approve($rule, auth()->user(), $request->input('notes'));

        return redirect()
            ->route('admin.printing-intelligence.calibration-governance', ['tab' => 'active'])
            ->with('status', __('Calibration rule approved. Historical estimates remain on their original formula versions.'));
    }

    public function rejectCalibrationRule(Request $request, PrintCalibrationRule $rule): RedirectResponse
    {
        abort_unless(auth()->user()?->can('printing.calibration.review'), 403);
        $this->authorize('update', $rule);

        app(CalibrationApprovalService::class)->reject($rule, auth()->user(), $request->input('notes'));

        return redirect()
            ->route('admin.printing-intelligence.calibration-governance', ['tab' => 'recommendations'])
            ->with('status', __('Calibration rule rejected.'));
    }

    public function generateCalibrationRecommendations(): RedirectResponse
    {
        abort_unless(auth()->user()?->can('printing.calibration.manage'), 403);

        [$companyId] = $this->tenantScope();
        app(CalibrationRecommendationService::class)->generate($companyId, 90, true);

        return redirect()
            ->route('admin.printing-intelligence.calibration-governance', ['tab' => 'recommendations'])
            ->with('status', __('Calibration recommendations generated from PI6 analytics.'));
    }

    public function productionProfitability(Request $request): View
    {
        abort_unless(auth()->user()?->can('printing.profitability.view'), 403);

        [$companyId, $branchId] = $this->tenantScope();
        $tab = in_array($request->query('tab'), [
            'overview', 'jobs', 'customers', 'machines', 'products', 'leakage', 'analytics',
        ], true) ? $request->query('tab') : 'overview';

        $filters = array_filter([
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'days' => $request->query('days', 90),
            'period' => $request->query('period', 'month'),
        ]);

        $overview = $this->gateway->profitabilityOverview($companyId, $branchId, $filters);
        $jobs = $tab === 'jobs' ? $this->gateway->jobProfitability($filters) : null;
        $customers = $tab === 'customers' ? $this->gateway->customerProfitability($filters) : null;
        $machines = $tab === 'machines' ? $this->gateway->machineProfitability($filters) : null;
        $products = $tab === 'products' ? $this->gateway->productProfitability($filters) : null;
        $leakage = $tab === 'leakage' ? $this->gateway->marginLeakage($filters) : null;
        $analytics = $tab === 'analytics' && auth()->user()?->can('printing.profitability.analytics')
            ? $this->gateway->analyticsSummary($filters)
            : null;

        return view('admin.printing-intelligence.production-profitability', [
            'tab' => $tab,
            'overview' => $overview,
            'jobs' => $jobs,
            'customers' => $customers,
            'machines' => $machines,
            'products' => $products,
            'leakage' => $leakage,
            'analytics' => $analytics,
            'filters' => $filters,
            'config' => config('printing_intelligence'),
        ]);
    }

    public function generateProfitabilitySnapshots(): RedirectResponse
    {
        abort_unless(auth()->user()?->can('printing.profitability.generate'), 403);

        [$companyId] = $this->tenantScope();
        app(\App\Services\PrintingIntelligence\ProfitabilitySnapshotGeneratorService::class)
            ->generateForCompany($companyId, 90, null, true);

        return redirect()
            ->route('admin.printing-intelligence.production-profitability')
            ->with('status', __('Profitability snapshots generated.'));
    }

    public function executiveIntelligence(Request $request): View
    {
        abort_unless(auth()->user()?->can('printing.executive.view'), 403);

        [$companyId, $branchId] = $this->tenantScope();
        $tab = in_array($request->query('tab'), [
            'dashboard', 'revenue', 'profit', 'capacity', 'demand', 'inventory', 'customers', 'scenarios', 'alerts',
        ], true) ? $request->query('tab') : 'dashboard';

        $filters = array_filter([
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'scenario' => $request->query('scenario', 'sales_plus_10'),
        ]);

        $overview = Cache::remember(
            'pi.executive.overview.'.$companyId.'.'.($branchId ?? 'all'),
            120,
            fn () => $this->gateway->forecastOverview($companyId, $filters),
        );

        $hasAnalytics = auth()->user()?->can('printing.executive.analytics');

        $revenue = $tab === 'revenue' && $hasAnalytics ? $this->gateway->revenueForecast($filters) : null;
        $profit = $tab === 'profit' && $hasAnalytics ? $this->gateway->profitForecast($filters) : null;
        $capacity = $tab === 'capacity' && $hasAnalytics ? $this->gateway->capacityForecast($filters) : null;
        $demand = $tab === 'demand' && $hasAnalytics ? $this->gateway->demandForecast($filters) : null;
        $inventory = $tab === 'inventory' && $hasAnalytics ? $this->gateway->inventoryRiskForecast($filters) : null;
        $customers = $tab === 'customers' && $hasAnalytics ? $this->gateway->customerTrendForecast($filters) : null;
        $scenarios = $tab === 'scenarios' && auth()->user()?->can('printing.executive.forecast')
            ? $this->gateway->scenarioSimulation($filters)
            : null;
        $alerts = $tab === 'alerts' ? $this->gateway->executiveAlerts($filters) : null;

        return view('admin.printing-intelligence.executive-intelligence', [
            'tab' => $tab,
            'overview' => $overview,
            'revenue' => $revenue,
            'profit' => $profit,
            'capacity' => $capacity,
            'demand' => $demand,
            'inventory' => $inventory,
            'customers' => $customers,
            'scenarios' => $scenarios,
            'alerts' => $alerts,
            'filters' => $filters,
            'config' => config('printing_intelligence'),
        ]);
    }

    public function generateForecastSnapshots(): RedirectResponse
    {
        abort_unless(auth()->user()?->can('printing.executive.forecast'), 403);

        [$companyId] = $this->tenantScope();
        app(\App\Services\PrintingIntelligence\ForecastSnapshotGeneratorService::class)
            ->generateForCompany($companyId, null, null, true);

        return redirect()
            ->route('admin.printing-intelligence.executive-intelligence')
            ->with('status', __('Executive forecast snapshots generated.'));
    }

    public function operationsAdvisor(Request $request): View
    {
        abort_unless(auth()->user()?->can('printing.advisor.view'), 403);

        [$companyId, $branchId] = $this->tenantScope();
        $tab = in_array($request->query('tab'), [
            'overview', 'quotations', 'artwork', 'machines', 'inventory', 'customers', 'profitability', 'forecasts',
        ], true) ? $request->query('tab') : 'overview';

        $typeMap = [
            'quotations' => 'quotation',
            'artwork' => 'artwork',
            'machines' => 'machine',
            'inventory' => 'inventory',
            'customers' => 'customer',
            'profitability' => 'profitability',
            'forecasts' => 'forecast',
        ];

        $statusFilter = $request->query('status') ?: ($tab === 'overview' ? 'open' : null);
        $typeFilter = $tab !== 'overview' ? ($typeMap[$tab] ?? null) : null;

        $filters = array_filter([
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'status' => $statusFilter,
        ]);

        $overview = $this->gateway->advisorOverview(
            $companyId,
            $branchId,
            $typeFilter,
            $statusFilter,
        );
        $executiveSummary = $tab === 'overview'
            ? $this->gateway->executiveAdvisorSummary($filters)
            : null;

        $live = null;
        if ($tab !== 'overview') {
            $live = match ($tab) {
                'quotations' => $this->gateway->quotationRecommendations($filters),
                'artwork' => $this->gateway->artworkRecommendations($filters),
                'machines' => $this->gateway->machineRecommendations($filters),
                'inventory' => $this->gateway->inventoryRecommendations($filters),
                'customers' => $this->gateway->customerRecommendations($filters),
                'profitability' => $this->gateway->profitabilityRecommendations($filters),
                'forecasts' => $this->gateway->forecastRecommendations($filters),
                default => null,
            };
        }

        return view('admin.printing-intelligence.operations-advisor', [
            'tab' => $tab,
            'overview' => $overview,
            'executiveSummary' => $executiveSummary,
            'liveRecommendations' => $live,
            'filters' => $filters,
            'config' => config('printing_intelligence'),
        ]);
    }

    public function generateAdvisorRecommendations(): RedirectResponse
    {
        abort_unless(auth()->user()?->can('printing.advisor.manage'), 403);

        [$companyId, $branchId] = $this->tenantScope();
        app(PrintOperationsAdvisorService::class)->generate($companyId, $branchId, null, true);

        return redirect()
            ->route('admin.printing-intelligence.operations-advisor')
            ->with('status', __('Advisor recommendations generated.'));
    }

    public function acknowledgeAdvisorRecommendation(Request $request, PrintAdvisorRecommendation $recommendation): RedirectResponse
    {
        abort_unless(auth()->user()?->can('printing.advisor.manage'), 403);
        [$companyId] = $this->tenantScope();
        abort_unless((int) $recommendation->company_id === $companyId, 404);

        app(AdvisorRecommendationWorkflowService::class)->acknowledge(
            $recommendation,
            auth()->user(),
            $request->input('comment'),
        );

        return redirect()
            ->back()
            ->with('status', __('Recommendation acknowledged.'));
    }

    public function dismissAdvisorRecommendation(Request $request, PrintAdvisorRecommendation $recommendation): RedirectResponse
    {
        abort_unless(auth()->user()?->can('printing.advisor.manage'), 403);
        [$companyId] = $this->tenantScope();
        abort_unless((int) $recommendation->company_id === $companyId, 404);

        app(AdvisorRecommendationWorkflowService::class)->dismiss(
            $recommendation,
            auth()->user(),
            $request->input('comment'),
        );

        return redirect()
            ->back()
            ->with('status', __('Recommendation dismissed.'));
    }

    public function exportEstimateVsActual(Request $request): StreamedResponse
    {
        abort_unless(auth()->user()?->can('printing.estimate-actual.view'), 403);

        [$companyId, $branchId] = $this->tenantScope();
        $filters = array_filter([
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'from' => $request->query('from'),
            'to' => $request->query('to'),
            'variance_class' => $request->query('variance_class'),
        ]);

        $rows = app(EstimateAccuracyAnalyticsService::class)->comparisonsQuery($filters)->get();

        return $this->csvResponse('estimate-vs-actual.csv', [
            'ID', 'Quotation', 'Estimated Total', 'Actual Total', 'Variance', 'Accuracy', 'Class',
        ], $rows->map(fn ($row) => [
            $row->id,
            $row->quotation_id,
            $row->estimated_total_cost,
            $row->actual_total_cost,
            $row->total_cost_variance,
            $row->accuracy_score,
            $row->variance_class?->value ?? $row->variance_class,
        ]));
    }

    public function exportProfitability(Request $request): StreamedResponse
    {
        abort_unless(auth()->user()?->can('printing.profitability.view'), 403);

        [$companyId, $branchId] = $this->tenantScope();
        $filters = array_filter([
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'days' => $request->query('days', 90),
        ]);

        $jobs = $this->gateway->jobProfitability($filters);

        return $this->csvResponse('production-profitability.csv', [
            'Job', 'Revenue', 'Total Cost', 'Gross Profit', 'Margin %', 'Class',
        ], collect($jobs['rankings'] ?? [])->map(fn ($row) => [
            $row['job_card_number'] ?? $row['production_job_card_id'] ?? '',
            $row['revenue'] ?? '',
            $row['total_cost'] ?? '',
            $row['gross_profit'] ?? '',
            $row['gross_margin_percent'] ?? '',
            $row['profitability_class'] ?? '',
        ]));
    }

    public function exportCalibration(): StreamedResponse
    {
        abort_unless(auth()->user()?->can('printing.calibration.view'), 403);

        [$companyId] = $this->tenantScope();
        $rules = PrintCalibrationRule::query()->where('company_id', $companyId)->latest('id')->get();

        return $this->csvResponse('calibration-governance.csv', [
            'ID', 'Type', 'Key', 'Proposed Value', 'Status', 'Approved At',
        ], $rules->map(fn ($rule) => [
            $rule->id,
            $rule->rule_type?->value ?? $rule->rule_type,
            $rule->rule_key,
            $rule->proposed_value,
            $rule->status?->value ?? $rule->status,
            $rule->approved_at,
        ]));
    }

    public function exportForecasts(): StreamedResponse
    {
        abort_unless(auth()->user()?->can('printing.executive.view'), 403);

        [$companyId] = $this->tenantScope();
        $snapshots = \App\Models\PrintingIntelligence\PrintForecastSnapshot::query()
            ->where('company_id', $companyId)
            ->orderByDesc('forecast_period_start')
            ->limit(500)
            ->get();

        return $this->csvResponse('executive-forecasts.csv', [
            'Type', 'Period', 'Forecast Value', 'Confidence', 'Model',
        ], $snapshots->map(fn ($row) => [
            $row->forecast_type?->value ?? $row->forecast_type,
            $row->forecast_period_start,
            $row->forecast_value,
            $row->confidence_score,
            $row->forecast_model?->value ?? $row->forecast_model,
        ]));
    }

    /**
     * @param  list<string>  $headers
     * @param  iterable<int, list<mixed>>  $rows
     */
    protected function csvResponse(string $filename, array $headers, iterable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);
            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    protected function authorizeView(): void
    {
        abort_unless(auth()->user()?->can('printing.intelligence.view'), 403);
    }

    /**
     * @return array{0: int, 1: int|null}
     */
    protected function tenantScope(): array
    {
        return [
            (int) (tenant()->companyId() ?? auth()->user()?->company_id),
            tenant()->branchId(),
        ];
    }
}
