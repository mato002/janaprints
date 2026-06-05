<?php

namespace App\Support\Reports;

use App\Services\Assets\AssetAnalyticsService;
use App\Services\Assets\AssetExecutiveIntelligenceService;
use App\Services\Assets\AssetReplacementService;
use App\Support\Reports\Concerns\BuildsIntelligenceSections;
use Illuminate\Http\Request;

class Asset360IntelligencePresenter
{
    use BuildsIntelligenceSections;

    public function __construct(
        protected IntelligenceScopeResolver $scopeResolver,
        protected AssetExecutiveIntelligenceService $executive,
        protected AssetAnalyticsService $analytics,
        protected AssetReplacementService $replacement,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function present(Request $request): array
    {
        $resolved = $this->scopeResolver->resolve($request, defaultBranchFromTenant: true);
        $companyId = (int) tenant()->companyId();
        $branchId = $resolved['scope']->branchId;
        $exec = $this->executive->build($companyId, $branchId);
        $analytics = $this->analytics->build($companyId, $branchId);

        return [
            'title' => __('Asset Intelligence'),
            'description' => __('Executive asset intelligence — valuation, health, utilization, and lifecycle analytics.'),
            'filters' => $resolved['filters'],
            'branches' => $resolved['branches'],
            'can_export' => false,
            'read_only' => true,
            'sections' => [
                $this->kpiSection(__('Asset Summary'), [
                    $this->kpi(__('Total asset value'), number_format($exec['total_asset_value'], 2), 'chip'),
                    $this->kpi(__('Net book value'), number_format($exec['net_book_value'], 2), 'chart-pie'),
                    $this->kpi(__('Monthly depreciation'), number_format($exec['depreciation_this_month'], 2), 'trending-down'),
                    $this->kpi(__('Near end of life'), (string) $exec['assets_near_end_of_life'], 'calendar'),
                    $this->kpi(__('Critical assets'), (string) $exec['critical_assets'], 'exclamation'),
                    $this->kpi(__('Replacement candidates'), (string) $exec['replacement_candidates'], 'switch-horizontal'),
                ]),
                $this->tableSection(
                    __('Assets by category'),
                    [__('Category'), __('Count'), __('NBV')],
                    collect($exec['by_category'])->map(fn ($r) => [
                        'cells' => [$r['category'], (string) $r['count'], number_format($r['nbv'], 2)],
                    ])->all(),
                ),
                $this->tableSection(
                    __('Age distribution'),
                    [__('Bucket'), __('Count')],
                    collect($analytics['age_distribution'])->map(fn ($r) => ['cells' => [$r['label'], (string) $r['count']]])->all(),
                ),
                $this->tableSection(
                    __('Maintenance trend (6 mo)'),
                    [__('Month'), __('Work orders')],
                    collect($analytics['maintenance_trend'])->map(fn ($r) => ['cells' => [$r['month'], (string) $r['value']]])->all(),
                ),
            ],
        ];
    }
}
