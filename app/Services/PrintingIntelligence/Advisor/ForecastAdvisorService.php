<?php

namespace App\Services\PrintingIntelligence\Advisor;

use App\Enums\AdvisorRecommendationType;
use App\Enums\AdvisorSeverity;
use App\Enums\ProfitabilityClass;
use App\Enums\ProfitabilitySnapshotType;
use App\Models\PrintingIntelligence\PrintProfitabilitySnapshot;
use App\Services\PrintingIntelligence\DemandForecastService;
use App\Services\PrintingIntelligence\ProfitForecastService;
use App\Services\PrintingIntelligence\RevenueForecastService;

class ForecastAdvisorService
{
    public function __construct(
        protected AdvisorConfidenceService $confidence,
    ) {}

    /**
     * @param  array{company_id?: int, branch_id?: int|null}  $filters
     * @return list<array<string, mixed>>
     */
    public function recommend(array $filters = []): array
    {
        $companyId = (int) ($filters['company_id'] ?? tenant()->companyId() ?? auth()->user()?->company_id);
        $filters['company_id'] = $companyId;

        $revenue = app(RevenueForecastService::class)->forecast($filters);
        $profit = app(ProfitForecastService::class)->forecast($filters);
        $demand = app(DemandForecastService::class)->forecast($filters);

        $recommendations = [];
        $historical = $revenue['historical_series'] ?? [];

        if (count($historical) >= 2) {
            $last = (float) ($historical[count($historical) - 1]['value'] ?? 0);
            $prev = (float) ($historical[count($historical) - 2]['value'] ?? 0);
            if ($prev > 0 && (($last - $prev) / $prev) < -0.08) {
                $recommendations[] = AdvisorRecommendationWriter::payload(
                    AdvisorRecommendationType::Forecast,
                    AdvisorSeverity::High,
                    'forecast:revenue_decline',
                    __('Revenue decline warning'),
                    __('Recent monthly revenue trend is declining.'),
                    __('Executive forecast indicates revenue pressure — review pipeline and pricing.'),
                    'PI9',
                    $this->confidence->score(['forecast_confidence' => $revenue['next_month']['confidence_score'] ?? 60, 'historical_periods' => count($historical)]),
                    __('Review sales pipeline and discount policy.'),
                );
            }
        }

        if ((float) ($profit['forecast_profit']['forecast_value'] ?? 0) < 0) {
            $recommendations[] = AdvisorRecommendationWriter::payload(
                AdvisorRecommendationType::Forecast,
                AdvisorSeverity::Critical,
                'forecast:negative_profit',
                __('Negative profit forecast'),
                __('Next month profit forecast is negative.'),
                __('Forecast profit outlook requires immediate cost and pricing review.'),
                'PI9',
                $this->confidence->score(['forecast_confidence' => $profit['forecast_profit']['confidence_score'] ?? 50]),
                __('Escalate to finance leadership.'),
            );
        }

        foreach ($demand['growing_demand'] ?? [] as $product) {
            $growth = (float) ($product['growth_percent'] ?? 0);
            if ($growth >= 15) {
                $recommendations[] = AdvisorRecommendationWriter::payload(
                    AdvisorRecommendationType::Forecast,
                    AdvisorSeverity::Info,
                    'forecast:demand_growth:'.($product['product_key'] ?? md5(json_encode($product))),
                    __('Demand growth opportunity'),
                    __(':label demand forecast increased :pct%.', [
                        'label' => $product['product_label'] ?? $product['label'] ?? __('Product'),
                        'pct' => $growth,
                    ]),
                    __('Business Cards demand forecast increased — consider capacity and inventory planning.'),
                    'PI9',
                    $this->confidence->score(['forecast_confidence' => 70, 'signal_strength' => $growth]),
                    __('Align production schedule with expected demand uplift.'),
                    null,
                    null,
                    $product,
                );
            }
        }

        return array_merge($recommendations, $this->capacityRecommendations($filters));
    }

    /**
     * @param  array{company_id?: int, branch_id?: int|null}  $filters
     * @return list<array<string, mixed>>
     */
    protected function capacityRecommendations(array $filters): array
    {
        $capacity = app(\App\Services\PrintingIntelligence\CapacityForecastService::class)->forecast($filters);
        $confidence = app(AdvisorConfidenceService::class);
        $recommendations = [];

        foreach ($capacity['bottlenecks'] ?? [] as $bottleneck) {
            $recommendations[] = AdvisorRecommendationWriter::payload(
                AdvisorRecommendationType::Forecast,
                AdvisorSeverity::High,
                'forecast:capacity_bottleneck:'.($bottleneck['machine_profile_id'] ?? uniqid()),
                __('Capacity bottleneck warning'),
                __(':name forecast utilization :pct%.', [
                    'name' => $bottleneck['machine_name'] ?? __('Machine'),
                    'pct' => $bottleneck['forecast_utilization_percent'] ?? 0,
                ]),
                __('Forecast indicates capacity pressure — plan load balancing or overtime.'),
                'PI9',
                $confidence->score(['forecast_confidence' => 65, 'historical_periods' => 2]),
                __('Review machine schedule for next forecast period.'),
                \App\Models\Assets\MachineProfile::class,
                $bottleneck['machine_profile_id'] ?? null,
                $bottleneck,
            );
        }

        return $recommendations;
    }
}
