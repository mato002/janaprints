<?php

namespace App\Services\PrintingIntelligence\Advisor;

use App\Enums\AdvisorRecommendationType;
use App\Enums\AdvisorSeverity;
use App\Models\Crm\Customer;
use App\Services\PrintingIntelligence\CustomerProfitabilityService;
use App\Services\PrintingIntelligence\CustomerTrendForecastService;

class CustomerAdvisorService
{
    public function __construct(
        protected AdvisorConfidenceService $confidence,
    ) {}

    /**
     * @param  array{company_id?: int, branch_id?: int|null, days?: int}  $filters
     * @return list<array<string, mixed>>
     */
    public function recommend(array $filters = []): array
    {
        $companyId = (int) ($filters['company_id'] ?? tenant()->companyId() ?? auth()->user()?->company_id);
        $filters['company_id'] = $companyId;
        $filters['days'] = (int) ($filters['days'] ?? 90);

        $profitability = app(CustomerProfitabilityService::class)->analyze($filters);
        $trends = app(CustomerTrendForecastService::class)->forecast($filters);

        $recommendations = [];

        if ($trends['concentration_risk'] ?? false) {
            $recommendations[] = AdvisorRecommendationWriter::payload(
                AdvisorRecommendationType::Customer,
                AdvisorSeverity::High,
                'customer:concentration_risk',
                __('Customer concentration risk'),
                __('Top customers represent :pct% of revenue.', ['pct' => $trends['customer_concentration_risk_percent'] ?? 0]),
                __('Revenue is concentrated — diversify customer base to reduce exposure.'),
                'PI9',
                $this->confidence->score(['forecast_confidence' => 70, 'historical_periods' => 3]),
                __('Develop mid-tier customer acquisition plan.'),
            );
        }

        foreach ($trends['declining_customers'] ?? [] as $customer) {
            $recommendations[] = AdvisorRecommendationWriter::payload(
                AdvisorRecommendationType::Customer,
                AdvisorSeverity::Medium,
                'customer:declining:'.($customer['customer_id'] ?? uniqid()),
                __('Declining customer trend'),
                __(':name revenue declined :pct% over recent periods.', [
                    'name' => $customer['customer_name'] ?? __('Customer'),
                    'pct' => abs($customer['growth_percent'] ?? 18),
                ]),
                __('Customer engagement review recommended before churn.'),
                'PI9',
                $this->confidence->score(['data_points' => 2, 'signal_strength' => abs($customer['growth_percent'] ?? 18)]),
                __('Schedule account review with sales.'),
                Customer::class,
                $customer['customer_id'] ?? null,
                $customer,
            );
        }

        $least = $profitability['least_profitable'] ?? null;
        if ($least && ($least['margin_percent'] ?? 100) < 15) {
            $recommendations[] = AdvisorRecommendationWriter::payload(
                AdvisorRecommendationType::Customer,
                AdvisorSeverity::High,
                'customer:margin_erosion:'.($least['customer_id'] ?? 'worst'),
                __('Margin erosion'),
                __(':name margin :pct%.', ['name' => $least['customer_name'], 'pct' => $least['margin_percent']]),
                __('Customer profitability is eroding — review pricing and job mix.'),
                'PI8',
                $this->confidence->score(['data_points' => 3, 'historical_periods' => 2]),
                __('Analyze recent quotes and discounts for this customer.'),
                Customer::class,
                $least['customer_id'] ?? null,
                $least,
            );
        }

        return $recommendations;
    }
}
