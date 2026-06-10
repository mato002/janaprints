<?php

namespace App\Services\PrintingIntelligence;

use App\Enums\CalibrationRuleStatus;
use App\Enums\CalibrationRuleType;
use App\Enums\EstimateActualComparisonStatus;
use App\Models\PrintingIntelligence\PrintCalibrationRule;
use App\Models\PrintingIntelligence\PrintEstimateActualComparison;
use Illuminate\Support\Collection;

class CalibrationRecommendationService
{
    public function __construct(
        protected ActiveCostingProfileService $activeProfile,
        protected CostFormulaVersionService $formulaVersions,
    ) {}

    /**
     * @return list<PrintCalibrationRule>
     */
    public function generate(int $companyId, int $days = 90, bool $persist = true): array
    {
        if (! config('printing_intelligence.calibration_recommendation_enabled', true)) {
            return [];
        }

        $comparisons = $this->completedComparisons($companyId, $days);
        $minSamples = (int) config('printing_intelligence.calibration_min_sample_size', 20);
        $created = [];

        if ($comparisons->count() >= $minSamples) {
            $inkAvg = $this->averageVariancePercent($comparisons, 'ink_cost_variance_percent');
            if ($inkAvg !== null && abs($inkAvg) >= (float) config('printing_intelligence.calibration_ink_variance_trigger_percent', 15)) {
                $created[] = $this->storeRecommendation(
                    $companyId,
                    CalibrationRuleType::InkYield,
                    'default_cmyk_coverage_factor',
                    (float) config('printing_intelligence.default_cmyk_coverage_factor', 1.0),
                    $this->proposedInkYield($inkAvg),
                    $inkAvg,
                    $comparisons,
                    $persist,
                    __('Ink variance average :percent% across :count comparisons — review ink yield factor.', [
                        'percent' => number_format(abs($inkAvg), 1),
                        'count' => $comparisons->count(),
                    ]),
                );
            }

            $machineAvg = $this->averageVariancePercent($comparisons, 'machine_cost_variance_percent');
            if ($machineAvg !== null && abs($machineAvg) >= (float) config('printing_intelligence.calibration_machine_variance_trigger_percent', 10)) {
                $currentRate = (float) ($this->activeProfile->value(
                    CalibrationRuleType::MachineRate,
                    null,
                    $companyId,
                    1500.0,
                ) ?? 1500.0);
                $created[] = $this->storeRecommendation(
                    $companyId,
                    CalibrationRuleType::MachineRate,
                    'machine_cost_per_hour',
                    $currentRate,
                    round($currentRate * (1 + ($machineAvg / 100)), 4),
                    $machineAvg,
                    $comparisons,
                    $persist,
                    __('Machine/process variance average :percent% — review machine cost profile.', [
                        'percent' => number_format(abs($machineAvg), 1),
                    ]),
                );
            }

            $overheadAvg = $this->averageVariancePercent($comparisons, 'overhead_cost_variance_percent');
            if ($overheadAvg !== null && abs($overheadAvg) >= 10) {
                $current = (float) config('printing_intelligence.default_overhead_percent', 10);
                $created[] = $this->storeRecommendation(
                    $companyId,
                    CalibrationRuleType::OverheadRate,
                    'default_overhead_percent',
                    $current,
                    round(max(0, $current + $overheadAvg), 4),
                    $overheadAvg,
                    $comparisons,
                    $persist,
                    __('Overhead variance average :percent% — review overhead allocation.', [
                        'percent' => number_format(abs($overheadAvg), 1),
                    ]),
                );
            }
        }

        return array_values(array_filter($created));
    }

    /**
     * @return Collection<int, PrintEstimateActualComparison>
     */
    protected function completedComparisons(int $companyId, int $days): Collection
    {
        return PrintEstimateActualComparison::query()
            ->where('company_id', $companyId)
            ->where('comparison_status', EstimateActualComparisonStatus::Completed)
            ->where('compared_at', '>=', now()->subDays($days))
            ->get();
    }

    /**
     * @param  Collection<int, PrintEstimateActualComparison>  $comparisons
     */
    protected function averageVariancePercent(Collection $comparisons, string $field): ?float
    {
        $values = $comparisons->pluck($field)->filter(fn ($v) => $v !== null)->map(fn ($v) => (float) $v);

        return $values->isNotEmpty() ? round($values->avg(), 3) : null;
    }

    protected function proposedInkYield(float $inkVariancePercent): float
    {
        $current = (float) config('printing_intelligence.default_cmyk_coverage_factor', 1.0);

        if ($inkVariancePercent < 0) {
            return round($current * (1 + (abs($inkVariancePercent) / 100)), 4);
        }

        return round(max(0.1, $current * (1 - ($inkVariancePercent / 200))), 4);
    }

    /**
     * @param  Collection<int, PrintEstimateActualComparison>  $comparisons
     */
    protected function storeRecommendation(
        int $companyId,
        CalibrationRuleType $type,
        string $ruleKey,
        float $currentValue,
        float $proposedValue,
        float $varianceTrigger,
        Collection $comparisons,
        bool $persist,
        string $reason,
    ): ?PrintCalibrationRule {
        $existing = PrintCalibrationRule::query()
            ->where('company_id', $companyId)
            ->where('rule_key', $ruleKey)
            ->whereIn('status', [CalibrationRuleStatus::Draft, CalibrationRuleStatus::PendingReview])
            ->first();

        $payload = [
            'company_id' => $companyId,
            'rule_type' => $type,
            'rule_key' => $ruleKey,
            'current_value' => $currentValue,
            'proposed_value' => $proposedValue,
            'variance_trigger_percent' => abs($varianceTrigger),
            'status' => CalibrationRuleStatus::Draft,
            'reason' => $reason,
            'rule_version' => $this->formulaVersions->nextVersion($type, $companyId),
            'metadata' => [
                'evidence' => [
                    'sample_size' => $comparisons->count(),
                    'average_accuracy' => round((float) $comparisons->avg('accuracy_score'), 2),
                    'generated_at' => now()->toIso8601String(),
                ],
                'confidence' => min(100, 40 + $comparisons->count()),
            ],
        ];

        if (! $persist) {
            return new PrintCalibrationRule($payload);
        }

        if ($existing) {
            $existing->update($payload);

            return $existing->fresh();
        }

        return PrintCalibrationRule::query()->create($payload);
    }
}
