<?php

namespace App\Services\PrintingIntelligence;

use App\Enums\ProfitabilityClass;
use App\Models\PrintingIntelligence\PrintEstimateActualComparison;
use App\Models\PrintingIntelligence\PrintQuotationEstimate;
use App\Models\Production\ProductionJobCard;
use App\Models\Sales\Quotation;

class ProductionProfitabilityService
{
    public function __construct(
        protected ActualProductionCostResolver $actualCosts,
        protected RevenueResolutionService $revenueResolution,
    ) {}

    /**
     * PI8-V1 profitability calculation (read-only analytics).
     *
     * @return array<string, mixed>
     */
    public function calculateForJob(ProductionJobCard $jobCard): array
    {
        $jobCard->loadMissing(['salesOrder', 'quotation', 'customer']);

        $quotation = $jobCard->quotation;
        $actuals = $this->actualCosts->resolve($jobCard, $quotation);

        $revenue = $this->revenueResolution->resolve(
            $jobCard,
            $quotation,
            isset($actuals['job_cost_sheet_id'])
                ? \App\Models\Production\JobCostSheet::query()->find($actuals['job_cost_sheet_id'])
                : null,
        ) ?? 0.0;

        $materialCost = (float) $actuals['actual_material_cost'];
        $inkCost = (float) $actuals['actual_ink_cost'];
        $machineCost = (float) $actuals['actual_machine_cost'];
        $labourCost = (float) $actuals['actual_labour_cost'];
        $overheadCost = (float) $actuals['actual_overhead_cost'];
        $electricityCost = $this->resolveElectricityCost($jobCard, $quotation);
        $totalCost = (float) $actuals['actual_total_cost'];

        $grossProfit = round($revenue - $totalCost, 2);
        $grossMargin = $revenue > 0 ? round(($grossProfit / $revenue) * 100, 3) : null;

        $estimate = $this->resolveEstimate($jobCard, $quotation);
        $estimatedProfit = null;
        $estimatedMargin = null;

        if ($estimate !== null) {
            $estimatedTotal = (float) $estimate->estimated_total_cost;
            $recommended = (float) ($estimate->recommended_selling_price ?? $revenue);
            $estimatedProfit = round($recommended - $estimatedTotal, 2);
            $estimatedMargin = $recommended > 0
                ? round(($estimatedProfit / $recommended) * 100, 3)
                : ($estimate->expected_margin_percent !== null ? (float) $estimate->expected_margin_percent : null);
        } elseif ($quotation !== null && (float) $quotation->estimated_total_cost > 0) {
            $estimatedTotal = (float) $quotation->estimated_total_cost;
            $recommended = (float) ($quotation->recommended_price ?? $revenue);
            $estimatedProfit = round($recommended - $estimatedTotal, 2);
            $estimatedMargin = $recommended > 0 ? round(($estimatedProfit / $recommended) * 100, 3) : null;
        }

        $profitVariance = ($estimatedProfit !== null) ? round($grossProfit - $estimatedProfit, 2) : null;
        $marginVariance = ($estimatedMargin !== null && $grossMargin !== null)
            ? round($grossMargin - $estimatedMargin, 3)
            : null;

        $profitabilityClass = $this->classify($grossMargin);
        $score = $this->score($grossMargin, $profitVariance);

        $comparison = PrintEstimateActualComparison::query()
            ->where('production_job_card_id', $jobCard->id)
            ->latest('compared_at')
            ->first();

        return [
            'revenue' => $revenue,
            'material_cost' => $materialCost,
            'ink_cost' => $inkCost,
            'machine_cost' => $machineCost,
            'labour_cost' => $labourCost,
            'electricity_cost' => $electricityCost,
            'overhead_cost' => $overheadCost,
            'total_cost' => $totalCost,
            'gross_profit' => $grossProfit,
            'gross_margin_percent' => $grossMargin,
            'estimated_profit' => $estimatedProfit,
            'estimated_margin_percent' => $estimatedMargin,
            'profit_variance' => $profitVariance,
            'margin_variance_percent' => $marginVariance,
            'profitability_score' => $score,
            'profitability_class' => $profitabilityClass,
            'customer_id' => $jobCard->customer_id ?? $quotation?->customer_id,
            'quotation_id' => $jobCard->quotation_id,
            'machine_profile_id' => $this->resolveMachineProfileId($jobCard),
            'metadata' => [
                'formula_version' => config('printing_intelligence.profitability_formula_version', 'PI8-V1'),
                'job_card_number' => $jobCard->job_card_number,
                'production_type' => $jobCard->production_type?->value,
                'comparison_id' => $comparison?->id,
                'warnings' => $actuals['warnings'] ?? [],
            ],
        ];
    }

    protected function resolveElectricityCost(ProductionJobCard $jobCard, ?Quotation $quotation): float
    {
        $estimate = $this->resolveEstimate($jobCard, $quotation);

        if ($estimate !== null && (float) $estimate->estimated_electricity_cost > 0) {
            return (float) $estimate->estimated_electricity_cost;
        }

        $productionEstimate = \App\Models\PrintingIntelligence\PrintArtworkProductionEstimate::query()
            ->whereHas('analysis', function ($q) use ($jobCard, $quotation) {
                $q->where('production_job_card_id', $jobCard->id);
                if ($quotation !== null) {
                    $q->orWhere('quotation_id', $quotation->id);
                }
            })
            ->latest('id')
            ->first();

        return (float) ($productionEstimate?->estimated_electricity_cost ?? 0);
    }

    public function classify(?float $grossMarginPercent): ProfitabilityClass
    {
        if ($grossMarginPercent === null) {
            return ProfitabilityClass::Unknown;
        }

        return match (true) {
            $grossMarginPercent > 40 => ProfitabilityClass::Excellent,
            $grossMarginPercent > 25 => ProfitabilityClass::Good,
            $grossMarginPercent > 15 => ProfitabilityClass::Average,
            $grossMarginPercent > 0 => ProfitabilityClass::Weak,
            default => ProfitabilityClass::LossMaking,
        };
    }

    protected function score(?float $grossMarginPercent, ?float $profitVariance): ?float
    {
        if ($grossMarginPercent === null) {
            return null;
        }

        $base = max(0, min(100, $grossMarginPercent));
        if ($profitVariance !== null && $profitVariance < 0) {
            $base = max(0, $base + ($profitVariance / 100));
        }

        return round($base, 2);
    }

    protected function resolveEstimate(ProductionJobCard $jobCard, ?Quotation $quotation): ?PrintQuotationEstimate
    {
        if ($quotation !== null) {
            $estimate = PrintQuotationEstimate::query()
                ->where('quotation_id', $quotation->id)
                ->latest('id')
                ->first();

            if ($estimate !== null) {
                return $estimate;
            }
        }

        return PrintQuotationEstimate::query()
            ->whereHas('analysis', fn ($q) => $q->where('production_job_card_id', $jobCard->id))
            ->latest('id')
            ->first();
    }

    protected function resolveMachineProfileId(ProductionJobCard $jobCard): ?int
    {
        $estimate = \App\Models\PrintingIntelligence\PrintArtworkProductionEstimate::query()
            ->whereHas('analysis', function ($q) use ($jobCard) {
                $q->where('production_job_card_id', $jobCard->id)
                    ->orWhere('quotation_id', $jobCard->quotation_id);
            })
            ->latest('id')
            ->first();

        return $estimate?->machine_profile_id;
    }
}
