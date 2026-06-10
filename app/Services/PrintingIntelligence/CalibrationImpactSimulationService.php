<?php

namespace App\Services\PrintingIntelligence;

use App\Enums\CalibrationRuleType;
use App\Models\PrintingIntelligence\PrintCalibrationRule;
use App\Models\PrintingIntelligence\PrintEstimateActualComparison;
use App\Models\PrintingIntelligence\PrintQuotationEstimate;

class CalibrationImpactSimulationService
{
    /**
     * PI7-V1 simulation (advisory):
     * adjusted_component = estimated_component × (current_value / proposed_value)
     * adjusted_total = total − component + adjusted_component
     * simulated_accuracy = 100 − |((actual − adjusted_total) / adjusted_total) × 100|
     *
     * @return array<string, mixed>
     */
    public function simulate(PrintCalibrationRule $rule, int $estimateWindowDays = 90): array
    {
        $companyId = (int) $rule->company_id;
        $estimates = PrintQuotationEstimate::query()
            ->where('company_id', $companyId)
            ->where('created_at', '>=', now()->subDays($estimateWindowDays))
            ->latest('id')
            ->limit(90)
            ->get();

        $comparisons = PrintEstimateActualComparison::query()
            ->where('company_id', $companyId)
            ->whereNotNull('print_quotation_estimate_id')
            ->latest('compared_at')
            ->limit(90)
            ->get()
            ->keyBy('print_quotation_estimate_id');

        $beforeScores = [];
        $afterScores = [];

        foreach ($estimates as $estimate) {
            $comparison = $comparisons->get($estimate->id);
            if ($comparison === null || (float) $comparison->actual_total_cost <= 0) {
                continue;
            }

            $beforeTotal = (float) $estimate->estimated_total_cost;
            $actual = (float) $comparison->actual_total_cost;

            if ($beforeTotal <= 0) {
                continue;
            }

            $beforeScores[] = max(0, 100 - abs((($actual - $beforeTotal) / $beforeTotal) * 100));

            $adjustedTotal = $this->adjustTotal($estimate, $rule, $beforeTotal);
            if ($adjustedTotal > 0) {
                $afterScores[] = max(0, min(100, 100 - abs((($actual - $adjustedTotal) / $adjustedTotal) * 100)));
            }
        }

        $beforeAvg = $beforeScores !== [] ? round(array_sum($beforeScores) / count($beforeScores), 2) : null;
        $afterAvg = $afterScores !== [] ? round(array_sum($afterScores) / count($afterScores), 2) : null;

        return [
            'formula_version' => config('printing_intelligence.calibration_formula_version', 'PI7-V1'),
            'sample_size' => count($beforeScores),
            'window_days' => $estimateWindowDays,
            'average_accuracy_before' => $beforeAvg,
            'average_accuracy_after' => $afterAvg,
            'expected_improvement' => ($beforeAvg !== null && $afterAvg !== null)
                ? round($afterAvg - $beforeAvg, 2)
                : null,
            'rule_id' => $rule->id,
            'rule_type' => $rule->rule_type?->value,
            'current_value' => $rule->current_value !== null ? (float) $rule->current_value : null,
            'proposed_value' => $rule->proposed_value !== null ? (float) $rule->proposed_value : null,
            'advisory' => true,
        ];
    }

    protected function adjustTotal(PrintQuotationEstimate $estimate, PrintCalibrationRule $rule, float $total): float
    {
        $current = (float) ($rule->current_value ?? 0);
        $proposed = (float) ($rule->proposed_value ?? 0);

        if ($current <= 0 || $proposed <= 0) {
            return $total;
        }

        $ratio = $current / $proposed;

        return match ($rule->rule_type) {
            CalibrationRuleType::InkYield, CalibrationRuleType::InkCost => round(
                $total - (float) $estimate->estimated_ink_cost + ((float) $estimate->estimated_ink_cost * $ratio),
                2,
            ),
            CalibrationRuleType::MachineRate => round(
                $total - (float) $estimate->estimated_machine_cost + ((float) $estimate->estimated_machine_cost * $ratio),
                2,
            ),
            CalibrationRuleType::OverheadRate => round(
                $total - (float) $estimate->estimated_overhead_cost + ((float) $estimate->estimated_overhead_cost * $ratio),
                2,
            ),
            CalibrationRuleType::WastageFactor => round(
                $total - (float) $estimate->estimated_wastage_cost + ((float) $estimate->estimated_wastage_cost * $ratio),
                2,
            ),
            default => $total,
        };
    }
}
