<?php

namespace App\Services\PrintingIntelligence;

class ForecastConfidenceService
{
    /**
     * PI9-V1 confidence model (deterministic, 0–100).
     *
     * @param  array{periods_with_data?: int, historical_periods?: int, values?: list<float|int>}  $context
     */
    public function score(array $context): float
    {
        $minPeriods = (int) config('printing_intelligence.forecast_min_history_periods', 3);
        $periodsWithData = (int) ($context['periods_with_data'] ?? $context['historical_periods'] ?? 0);
        $historicalPeriods = (int) ($context['historical_periods'] ?? $periodsWithData);
        $values = $context['values'] ?? [];

        $dataCompleteness = min(40, ($minPeriods > 0 ? ($periodsWithData / $minPeriods) : 0) * 40);
        $historyDepth = min(30, ($historicalPeriods / 12) * 30);
        $trendStability = $this->trendStabilityScore($values);

        return round(min(100, max(0, $dataCompleteness + $historyDepth + $trendStability)), 2);
    }

    public function band(?float $score): string
    {
        if ($score === null) {
            return 'unknown';
        }

        return match (true) {
            $score < 40 => 'low',
            $score <= 70 => 'medium',
            default => 'high',
        };
    }

    public function bandLabel(?float $score): string
    {
        return match ($this->band($score)) {
            'low' => __('Low'),
            'medium' => __('Medium'),
            'high' => __('High'),
            default => __('Unknown'),
        };
    }

    /**
     * @param  list<float|int>  $values
     */
    protected function trendStabilityScore(array $values): float
    {
        if (count($values) < 2) {
            return 10;
        }

        $mean = array_sum($values) / count($values);
        if (abs($mean) < 0.0001) {
            return 15;
        }

        $variance = 0;
        foreach ($values as $value) {
            $variance += ($value - $mean) ** 2;
        }
        $stdDev = sqrt($variance / count($values));
        $cv = $stdDev / abs($mean);

        return max(0, min(30, 30 - ($cv * 100)));
    }
}
