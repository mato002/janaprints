<?php

namespace App\Services\PrintingIntelligence;

use App\Enums\ForecastPeriodType;

class RevenueForecastService
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

        $series = $this->engine->monthlyMetricSeries($companyId, 'revenue', $months);
        $values = array_column($series, 'value');

        return [
            'historical_series' => $series,
            'next_month' => $this->periodForecast($values, ForecastPeriodType::Month),
            'next_quarter' => $this->periodForecast($values, ForecastPeriodType::Quarter),
            'next_year' => $this->periodForecast($values, ForecastPeriodType::Year),
            'formula_version' => config('printing_intelligence.forecast_formula_version', 'PI9-V1'),
        ];
    }

    /**
     * @param  list<float|int>  $values
     * @return array<string, mixed>
     */
    protected function periodForecast(array $values, ForecastPeriodType $periodType): array
    {
        $projection = $this->engine->project($values);
        $range = $this->engine->nextPeriodRange($periodType);
        $multiplier = match ($periodType) {
            ForecastPeriodType::Quarter => 3,
            ForecastPeriodType::Year => 12,
            default => 1,
        };

        $forecast = ($projection['forecast_value'] ?? 0) * $multiplier;

        return array_merge($projection, [
            'forecast_value' => round($forecast, 2),
            'lower_bound' => round(($projection['lower_bound'] ?? 0) * $multiplier, 2),
            'upper_bound' => round(($projection['upper_bound'] ?? 0) * $multiplier, 2),
            'period_type' => $periodType->value,
            'forecast_period_start' => $range['start'],
            'forecast_period_end' => $range['end'],
        ]);
    }
}
