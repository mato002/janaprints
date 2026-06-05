<?php

namespace App\Support\Procurement\Performance;

class SupplierPerformanceScoreCalculator
{
    /**
     * @param  array{
     *     on_time_percent: ?float,
     *     quality_acceptance_percent: ?float,
     *     responsiveness_score: ?float,
     *     rfq_participation_percent: ?float,
     *     award_win_percent: ?float,
     *     price_competitiveness: ?float,
     * }  $metrics
     */
    public function overallScore(array $metrics): ?float
    {
        $components = [
            ['value' => $metrics['on_time_percent'] ?? null, 'weight' => 0.30],
            ['value' => $metrics['quality_acceptance_percent'] ?? null, 'weight' => 0.25],
            ['value' => $metrics['responsiveness_score'] ?? null, 'weight' => 0.15],
            ['value' => $metrics['rfq_participation_percent'] ?? null, 'weight' => 0.10],
            ['value' => $metrics['award_win_percent'] ?? null, 'weight' => 0.10],
            ['value' => $metrics['price_competitiveness'] ?? null, 'weight' => 0.10],
        ];

        $weighted = 0.0;
        $totalWeight = 0.0;

        foreach ($components as $component) {
            if ($component['value'] === null) {
                continue;
            }

            $weighted += ((float) $component['value']) * $component['weight'];
            $totalWeight += $component['weight'];
        }

        if ($totalWeight <= 0) {
            return null;
        }

        return round($weighted / $totalWeight, 1);
    }

    public function grade(?float $score): string
    {
        if ($score === null) {
            return '—';
        }

        return match (true) {
            $score >= 90 => 'A',
            $score >= 80 => 'B',
            $score >= 70 => 'C',
            $score >= 60 => 'D',
            default => 'F',
        };
    }

    public function responsivenessScore(?float $averageResponseDays): ?float
    {
        if ($averageResponseDays === null) {
            return null;
        }

        return match (true) {
            $averageResponseDays <= 1 => 100.0,
            $averageResponseDays <= 2 => 92.0,
            $averageResponseDays <= 3 => 85.0,
            $averageResponseDays <= 5 => 75.0,
            $averageResponseDays <= 7 => 65.0,
            $averageResponseDays <= 14 => 50.0,
            default => 30.0,
        };
    }
}
