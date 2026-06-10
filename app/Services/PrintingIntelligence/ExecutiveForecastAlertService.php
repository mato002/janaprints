<?php

namespace App\Services\PrintingIntelligence;

class ExecutiveForecastAlertService
{
    /**
     * @param  array{company_id?: int}  $filters
     * @return array<string, mixed>
     */
    public function generate(array $filters = []): array
    {
        $companyId = (int) ($filters['company_id'] ?? tenant()->companyId() ?? auth()->user()?->company_id);

        $revenue = app(RevenueForecastService::class)->forecast(['company_id' => $companyId]);
        $profit = app(ProfitForecastService::class)->forecast(['company_id' => $companyId]);
        $capacity = app(CapacityForecastService::class)->forecast(['company_id' => $companyId]);
        $inventory = app(InventoryRiskForecastService::class)->forecast(['company_id' => $companyId]);
        $customers = app(CustomerTrendForecastService::class)->forecast(['company_id' => $companyId]);
        $accuracy = app(EstimateAccuracyAnalyticsService::class)->aggregate(['company_id' => $companyId]);

        $alerts = [];
        $historical = $revenue['historical_series'] ?? [];
        if (count($historical) >= 2) {
            $last = (float) ($historical[count($historical) - 1]['value'] ?? 0);
            $prev = (float) ($historical[count($historical) - 2]['value'] ?? 0);
            if ($prev > 0 && (($last - $prev) / $prev) < -0.10) {
                $alerts[] = $this->alert('revenue_decline', __('Revenue decline warning'), __('Recent monthly revenue trend is down more than 10%.'));
            }
        }

        $forecastProfit = (float) ($profit['forecast_profit']['forecast_value'] ?? 0);
        if ($forecastProfit < 0) {
            $alerts[] = $this->alert('profit_decline', __('Profit decline warning'), __('Forecast profit for next month is negative.'));
        }

        $forecastMargin = (float) ($profit['forecast_margin_percent']['forecast_value'] ?? 100);
        if ($forecastMargin < (float) config('printing_intelligence.margin_erosion_alert_threshold', 15)) {
            $alerts[] = $this->alert('margin_erosion', __('Margin erosion warning'), __('Forecast margin is below the configured threshold.'));
        }

        if (! empty($capacity['bottlenecks'])) {
            $alerts[] = $this->alert('capacity_bottleneck', __('Capacity bottleneck warning'), __('One or more machines exceed utilization bottleneck threshold.'));
        }

        $highestRisk = $inventory['highest_risk'] ?? null;
        if ($highestRisk && in_array($highestRisk['risk_class'] ?? '', ['critical', 'high'], true)) {
            $alerts[] = $this->alert('inventory_risk', __('Inventory risk warning'), __(':label — estimated :days days to stockout risk.', [
                'label' => $highestRisk['label'] ?? __('Inventory'),
                'days' => $highestRisk['days_to_risk'] ?? '—',
            ]));
        }

        if ($customers['concentration_risk'] ?? false) {
            $alerts[] = $this->alert('customer_concentration', __('Customer concentration warning'), __('Top customers represent :percent% of revenue.', [
                'percent' => $customers['customer_concentration_risk_percent'] ?? 0,
            ]));
        }

        if (($accuracy['average_accuracy_score'] ?? 100) < 70) {
            $alerts[] = $this->alert('forecast_accuracy', __('Forecast accuracy warning'), __('PI6 estimate accuracy is below 70% — executive forecasts may be less reliable.'));
        }

        return [
            'alerts' => $alerts,
            'alert_count' => count($alerts),
            'read_only' => true,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function alert(string $type, string $title, string $message): array
    {
        return [
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'severity' => match ($type) {
                'revenue_decline', 'profit_decline', 'inventory_risk' => 'high',
                'margin_erosion', 'capacity_bottleneck', 'customer_concentration' => 'medium',
                default => 'low',
            },
        ];
    }
}
