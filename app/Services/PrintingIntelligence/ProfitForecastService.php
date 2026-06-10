<?php

namespace App\Services\PrintingIntelligence;

use App\Enums\ForecastPeriodType;

class ProfitForecastService
{
    public function __construct(
        protected ExecutiveForecastingService $engine,
    ) {}

    /**
     * @param  array{company_id?: int, months?: int}  $filters
     * @return array<string, mixed>
     */
    public function forecast(array $filters = []): array
    {
        $companyId = (int) ($filters['company_id'] ?? tenant()->companyId() ?? auth()->user()?->company_id);
        $months = (int) ($filters['months'] ?? config('printing_intelligence.forecast_history_months', 12));

        $profitSeries = $this->engine->monthlyMetricSeries($companyId, 'profit', $months);
        $marginSeries = $this->engine->monthlyMetricSeries($companyId, 'margin', $months);
        $profitValues = array_column($profitSeries, 'value');
        $marginValues = array_column($marginSeries, 'value');

        $profitProjection = $this->engine->project($profitValues);
        $marginProjection = $this->engine->project($marginValues);
        $range = $this->engine->nextPeriodRange(ForecastPeriodType::Month);

        return [
            'historical_profit_series' => $profitSeries,
            'historical_margin_series' => $marginSeries,
            'forecast_profit' => array_merge($profitProjection, [
                'period_type' => ForecastPeriodType::Month->value,
                'forecast_period_start' => $range['start'],
                'forecast_period_end' => $range['end'],
            ]),
            'forecast_margin_percent' => $marginProjection,
            'formula_version' => config('printing_intelligence.forecast_formula_version', 'PI9-V1'),
        ];
    }
}
