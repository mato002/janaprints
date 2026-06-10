<?php

namespace App\Services\PrintingIntelligence;

class QuotationPricingService
{
    /**
     * PI5-V1 pricing formula.
     *
     * estimated_total_cost = material + ink + machine + labour + electricity + overhead + wastage
     * minimum_selling_price = total / (1 - minimum_margin_percent/100)
     * recommended_selling_price = total / (1 - target_margin_percent/100)
     * expected_margin_percent = (recommended - total) / recommended * 100
     *
     * @param  array<string, float>  $components
     * @return array{
     *     estimated_total_cost: float,
     *     minimum_selling_price: float,
     *     recommended_selling_price: float,
     *     expected_margin_percent: float|null,
     *     formula_version: string,
     *     breakdown: array<string, mixed>
     * }
     */
    public function price(array $components, float $minimumMarginPercent, float $targetMarginPercent): array
    {
        $formulaVersion = (string) config('printing_intelligence.quotation_formula_version', 'PI5-V1');

        $total = round(
            ($components['material_cost'] ?? 0)
            + ($components['ink_cost'] ?? 0)
            + ($components['machine_cost'] ?? 0)
            + ($components['labour_cost'] ?? 0)
            + ($components['electricity_cost'] ?? 0)
            + ($components['overhead_cost'] ?? 0)
            + ($components['wastage_cost'] ?? 0),
            2,
        );

        $minimumPrice = $this->sellingPriceForMargin($total, $minimumMarginPercent);
        $recommendedPrice = $this->sellingPriceForMargin($total, $targetMarginPercent);

        $expectedMargin = $recommendedPrice > 0
            ? round((($recommendedPrice - $total) / $recommendedPrice) * 100, 3)
            : null;

        return [
            'estimated_total_cost' => $total,
            'minimum_selling_price' => $minimumPrice,
            'recommended_selling_price' => $recommendedPrice,
            'expected_margin_percent' => $expectedMargin,
            'formula_version' => $formulaVersion,
            'breakdown' => [
                'minimum_margin_percent' => $minimumMarginPercent,
                'target_margin_percent' => $targetMarginPercent,
                'rounding_rule' => config('printing_intelligence.rounding_rule', 'none'),
                'components' => $components,
            ],
        ];
    }

    protected function sellingPriceForMargin(float $totalCost, float $marginPercent): float
    {
        if ($totalCost <= 0) {
            return 0.0;
        }

        $marginPercent = min(99.999, max(0, $marginPercent));
        $divisor = 1 - ($marginPercent / 100);

        if ($divisor <= 0) {
            return round($totalCost, 2);
        }

        return $this->applyRounding(round($totalCost / $divisor, 2));
    }

    protected function applyRounding(float $amount): float
    {
        return match (config('printing_intelligence.rounding_rule', 'none')) {
            'nearest_5' => round($amount / 5) * 5,
            'nearest_10' => round($amount / 10) * 10,
            'nearest_50' => round($amount / 50) * 50,
            'nearest_100' => round($amount / 100) * 100,
            default => round($amount, 2),
        };
    }
}
