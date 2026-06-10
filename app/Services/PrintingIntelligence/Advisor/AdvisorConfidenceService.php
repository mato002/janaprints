<?php

namespace App\Services\PrintingIntelligence\Advisor;

class AdvisorConfidenceService
{
    /**
     * PI10 confidence formula.
     *
     * data_completeness (40) + historical_depth (30) + signal_strength (30)
     *
     * @param  array{
     *     data_points?: int,
     *     required_points?: int,
     *     historical_periods?: int,
     *     forecast_confidence?: float|null,
     *     estimate_accuracy?: float|null,
     *     signal_strength?: float|null
     * }  $context
     */
    public function score(array $context = []): float
    {
        $required = max(1, (int) ($context['required_points'] ?? 3));
        $dataPoints = (int) ($context['data_points'] ?? 0);
        $completeness = min(40, ($dataPoints / $required) * 40);

        $periods = (int) ($context['historical_periods'] ?? 0);
        $depth = min(30, $periods * 6);

        $forecast = $context['forecast_confidence'] ?? null;
        $accuracy = $context['estimate_accuracy'] ?? null;
        $signal = $context['signal_strength'] ?? null;

        $signalScore = 0.0;
        if ($signal !== null) {
            $signalScore = min(30, max(0, (float) $signal) * 0.3);
        } elseif ($forecast !== null) {
            $signalScore = min(30, (float) $forecast * 0.3);
        } elseif ($accuracy !== null) {
            $signalScore = min(30, (float) $accuracy * 0.3);
        } else {
            $signalScore = 15;
        }

        return round(min(100, $completeness + $depth + $signalScore), 2);
    }

    public function band(float $score): string
    {
        return match (true) {
            $score >= 75 => 'high',
            $score >= 45 => 'medium',
            default => 'low',
        };
    }
}
