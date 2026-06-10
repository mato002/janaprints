<?php

namespace App\Services\PrintingIntelligence;

use App\Enums\QuotationEstimationStatus;
use App\Models\PrintingIntelligence\PrintArtworkAnalysis;
use App\Models\PrintingIntelligence\PrintQuotationEstimate;
use Throwable;

class QuotationEstimationService
{
    public function __construct(
        protected QuotationCostComposerService $composer,
        protected QuotationPricingService $pricing,
        protected ActiveCostingProfileService $activeProfile,
        protected QuotationEstimateLifecycleService $lifecycle,
    ) {}

    /**
     * @param  array{
     *     quantity?: int,
     *     material_inventory_item_id?: int|null,
     *     material_unit_cost_override?: float|null,
     *     material_quantity_override?: float|null,
     *     minimum_margin_percent?: float|null,
     *     target_margin_percent?: float|null,
     *     wastage_percent?: float|null,
     *     quotation_id?: int|null
     * }  $params
     */
    public function estimate(PrintArtworkAnalysis $analysis, array $params = []): PrintQuotationEstimate
    {
        if (! config('printing_intelligence.quotation_estimation_enabled', true)) {
            abort(503, __('Quotation estimation is disabled.'));
        }

        $quantity = max(1, (int) ($params['quantity'] ?? 1));
        $materialItemId = isset($params['material_inventory_item_id']) && $params['material_inventory_item_id'] !== ''
            ? (int) $params['material_inventory_item_id']
            : null;

        $baseLookup = [
            'print_artwork_analysis_id' => $analysis->id,
            'quantity' => $quantity,
            'material_inventory_item_id' => $materialItemId,
        ];

        $lookup = $this->lifecycle->resolveWritableLookup($baseLookup);
        $profile = $this->activeProfile->profile((int) $analysis->company_id);

        PrintQuotationEstimate::query()->updateOrCreate($lookup, [
            'company_id' => $analysis->company_id,
            'branch_id' => $analysis->branch_id,
            'version' => $lookup['version'],
            'estimation_status' => QuotationEstimationStatus::Processing,
        ]);

        try {
            $minimumMargin = (float) ($params['minimum_margin_percent'] ?? $profile['minimum_margin_percent'] ?? 20);
            $targetMargin = (float) ($params['target_margin_percent'] ?? $profile['target_margin_percent'] ?? 35);

            $composed = $this->composer->compose($analysis, array_merge($params, [
                'quantity' => $quantity,
                'material_inventory_item_id' => $materialItemId,
            ]));

            $pricing = $this->pricing->price([
                'material_cost' => $composed['material_cost'],
                'ink_cost' => $composed['ink_cost'],
                'machine_cost' => $composed['machine_cost'],
                'labour_cost' => $composed['labour_cost'],
                'electricity_cost' => $composed['electricity_cost'],
                'overhead_cost' => $composed['overhead_cost'],
                'wastage_cost' => $composed['wastage_cost'],
            ], $minimumMargin, $targetMargin);

            $confidence = $this->resolveConfidence($analysis, $composed['warnings']);

            $status = $composed['status'] === 'manual_review'
                ? QuotationEstimationStatus::ManualReview
                : QuotationEstimationStatus::Completed;

            return PrintQuotationEstimate::query()->updateOrCreate($lookup, [
                'company_id' => $analysis->company_id,
                'branch_id' => $analysis->branch_id,
                'version' => $lookup['version'],
                'quotation_id' => $params['quotation_id'] ?? $analysis->quotation_id,
                'print_artwork_ink_estimate_id' => $composed['print_artwork_ink_estimate_id'],
                'print_machine_estimate_id' => $composed['print_machine_estimate_id'],
                'estimation_status' => $status,
                'material_inventory_item_id' => $materialItemId,
                'material_name' => $composed['material_name'],
                'material_unit_cost' => $composed['material_unit_cost'],
                'material_quantity' => $composed['material_quantity'],
                'estimated_material_cost' => $composed['material_cost'],
                'estimated_ink_cost' => $composed['ink_cost'],
                'estimated_machine_cost' => $composed['machine_cost'],
                'estimated_labour_cost' => $composed['labour_cost'],
                'estimated_electricity_cost' => $composed['electricity_cost'],
                'estimated_overhead_cost' => $composed['overhead_cost'],
                'estimated_wastage_cost' => $composed['wastage_cost'],
                'estimated_total_cost' => $pricing['estimated_total_cost'],
                'minimum_margin_percent' => $minimumMargin,
                'target_margin_percent' => $targetMargin,
                'minimum_selling_price' => $pricing['minimum_selling_price'],
                'recommended_selling_price' => $pricing['recommended_selling_price'],
                'expected_margin_percent' => $pricing['expected_margin_percent'],
                'confidence_score' => $confidence,
                'formula_version' => $pricing['formula_version'],
                'calculation_breakdown' => array_merge($composed['breakdown'], $pricing['breakdown'], [
                    'costing_profile_source' => $profile['source'] ?? 'active_costing_profile',
                ]),
                'warnings' => $composed['warnings'],
                'metadata' => [
                    'generated_at' => now()->toIso8601String(),
                    'estimate_version' => $lookup['version'],
                ],
            ]);
        } catch (Throwable $exception) {
            return PrintQuotationEstimate::query()->updateOrCreate($lookup, [
                'company_id' => $analysis->company_id,
                'branch_id' => $analysis->branch_id,
                'version' => $lookup['version'],
                'estimation_status' => QuotationEstimationStatus::Failed,
                'warnings' => [$exception->getMessage()],
                'formula_version' => config('printing_intelligence.quotation_formula_version', 'PI5-V1'),
            ]);
        }
    }

    public function cloneEstimate(PrintQuotationEstimate $estimate): PrintQuotationEstimate
    {
        $this->lifecycle->assertMutable($estimate);

        return $this->lifecycle->cloneEstimate($estimate);
    }

    /**
     * @param  list<string>  $warnings
     */
    protected function resolveConfidence(PrintArtworkAnalysis $analysis, array $warnings): float
    {
        $analysis->loadMissing(['inkEstimates', 'productionEstimate']);
        $scores = [];

        if ($analysis->inkEstimates->first()?->confidence_score !== null) {
            $scores[] = (float) $analysis->inkEstimates->first()->confidence_score;
        }

        if ($analysis->productionEstimate?->confidence_score !== null) {
            $scores[] = (float) $analysis->productionEstimate->confidence_score;
        }

        $base = $scores === [] ? 50.0 : array_sum($scores) / count($scores);
        $base -= min(30, count($warnings) * 5);

        return round(max(0, min(100, $base)), 2);
    }
}
