<?php

namespace App\Services\PrintingIntelligence;

use App\Enums\AdvisorRecommendationStatus;
use App\Enums\AdvisorRecommendationType;
use App\Enums\ProfitabilityClass;
use App\Enums\ProfitabilitySnapshotType;
use App\Models\Assets\MachineProfile;
use App\Services\PrintingIntelligence\Advisor\AdvisorExecutiveSummaryService;
use App\Services\PrintingIntelligence\Advisor\PrintOperationsAdvisorService;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\InventoryValuation;
use App\Models\Inventory\InventoryVelocitySnapshot;
use App\Models\PrintingIntelligence\PrintArtworkAnalysis;
use App\Models\PrintingIntelligence\PrintEstimateActualComparison;
use App\Models\PrintingIntelligence\PrintInkProfile;
use App\Models\PrintingIntelligence\PrintProfitabilitySnapshot;
use App\Models\PrintingIntelligence\PrintQuotationEstimate;
use App\Models\Sales\Quotation;
use App\Services\Inventory\InventoryVelocityService;

class PrintingIntelligenceGateway
{
    public function __construct(
        protected MaterialCostContextService $materialCostContext,
        protected MachineCostProfileService $machineCostProfile,
        protected InkCostProfileService $inkCostProfile,
        protected PrintingCostContextService $printingCostContext,
        protected ProductionCostRealityService $productionReality,
        protected InventoryVelocityService $velocityService,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function materialContext(int $inventoryItemId, ?int $warehouseId = null): array
    {
        return $this->materialCostContext->context($inventoryItemId, $warehouseId);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function machineContext(int $machineProfileId, float $runHours = 1): ?array
    {
        $machine = MachineProfile::query()->find($machineProfileId);

        if ($machine === null) {
            return null;
        }

        return [
            'machine_profile_id' => $machine->id,
            'machine_code' => $machine->machine_code,
            'machine_type' => $machine->machine_type,
            'cost_per_hour' => $this->machineCostProfile->costPerHour($machine),
            'estimated_setup_cost' => $this->machineCostProfile->estimatedSetupCost($machine),
            'estimated_electricity_cost' => $this->machineCostProfile->estimatedElectricityCost($machine, $runHours),
            'estimated_machine_cost' => $this->machineCostProfile->estimatedMachineCost($machine, $runHours),
            'target_output_per_hour' => $machine->target_output_per_hour !== null
                ? (float) $machine->target_output_per_hour
                : (float) $machine->capacity_per_hour,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function inkContext(int $inkProfileId): ?array
    {
        $profile = PrintInkProfile::query()->find($inkProfileId);

        if ($profile === null) {
            return null;
        }

        return [
            'ink_profile_id' => $profile->id,
            'name' => $profile->name,
            'ink_type' => $profile->ink_type?->value ?? (string) $profile->ink_type,
            'cartridge_cost' => $this->inkCostProfile->currentCartridgeCost($profile),
            'cost_per_ml' => $this->inkCostProfile->costPerMl($profile),
            'yield_per_page' => $this->inkCostProfile->yieldPerPage($profile),
            'yield_per_sq_m' => $this->inkCostProfile->yieldPerSquareMeter($profile),
            'inventory_item_id' => $profile->inventory_item_id,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function inventoryContext(int $inventoryItemId, ?int $warehouseId = null): array
    {
        $cost = $this->printingCostContext->getInventoryCost($inventoryItemId, $warehouseId);

        return array_merge($cost, [
            'velocity' => $this->printingCostContext->getVelocityData($inventoryItemId, $warehouseId),
            'stock_balance' => $warehouseId
                ? $this->printingCostContext->stockBalance($inventoryItemId, $warehouseId)
                : null,
        ]);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function productionReality(int $jobCardId): ?array
    {
        if (! $this->productionReality->jobCardExists($jobCardId)) {
            return null;
        }

        return [
            'job_card_id' => $jobCardId,
            'actual_material_cost' => $this->productionReality->actualMaterialCost($jobCardId),
            'actual_machine_cost' => $this->productionReality->actualMachineCost($jobCardId),
            'actual_production_cost' => $this->productionReality->actualProductionCost($jobCardId),
            'actual_job_cost' => $this->productionReality->actualJobCost($jobCardId),
            'consumption' => $this->productionReality->actualConsumption($jobCardId),
            'profitability' => $this->productionReality->jobProfitability($jobCardId),
            'movements' => $this->productionReality->movementLedgerForJob($jobCardId),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function quotationContext(int $quotationId): ?array
    {
        $quotation = Quotation::query()->with('items')->find($quotationId);

        if ($quotation === null) {
            return null;
        }

        $hasEstimation = filled($quotation->estimation_version);

        return [
            'quotation_id' => $quotation->id,
            'quotation_number' => $quotation->quotation_number,
            'status' => $quotation->status?->value ?? (string) $quotation->status,
            'total_amount' => (float) $quotation->total_amount,
            'estimated_material_cost' => $hasEstimation && $quotation->estimated_material_cost !== null
                ? (float) $quotation->estimated_material_cost : null,
            'estimated_ink_cost' => $hasEstimation && $quotation->estimated_ink_cost !== null
                ? (float) $quotation->estimated_ink_cost : null,
            'estimated_machine_cost' => $hasEstimation && $quotation->estimated_machine_cost !== null
                ? (float) $quotation->estimated_machine_cost : null,
            'estimated_labour_cost' => $hasEstimation && $quotation->estimated_labour_cost !== null
                ? (float) $quotation->estimated_labour_cost : null,
            'estimated_overhead_cost' => $hasEstimation && $quotation->estimated_overhead_cost !== null
                ? (float) $quotation->estimated_overhead_cost : null,
            'estimated_total_cost' => $hasEstimation && $quotation->estimated_total_cost !== null
                ? (float) $quotation->estimated_total_cost : null,
            'estimated_margin_percent' => $hasEstimation && $quotation->estimated_margin_percent !== null
                ? (float) $quotation->estimated_margin_percent : null,
            'recommended_price' => $hasEstimation && $quotation->recommended_price !== null
                ? (float) $quotation->recommended_price : null,
            'confidence_score' => $hasEstimation && $quotation->confidence_score !== null
                ? (float) $quotation->confidence_score : null,
            'estimation_version' => $quotation->estimation_version,
            'line_count' => $quotation->items->count(),
            'intelligence_ready' => ! $hasEstimation,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function artworkContext(PrintArtworkAnalysis $analysis, bool $includeStoragePath = false): ?array
    {
        $analysis->loadMissing(['pages', 'quotation', 'productionJobCard']);

        return [
            'analysis_id' => $analysis->id,
            'original_filename' => $analysis->original_filename,
            'file_extension' => $analysis->file_extension,
            'mime_type' => $analysis->mime_type,
            'file_size_bytes' => $analysis->file_size_bytes,
            'file_hash' => $analysis->file_hash,
            'analysis_status' => $analysis->analysis_status?->value ?? (string) $analysis->analysis_status,
            'analysis_source' => $analysis->analysis_source?->value ?? (string) $analysis->analysis_source,
            'page_count' => $analysis->page_count,
            'width_mm' => $analysis->width_mm !== null ? (float) $analysis->width_mm : null,
            'height_mm' => $analysis->height_mm !== null ? (float) $analysis->height_mm : null,
            'area_square_m' => $analysis->area_square_m !== null ? (float) $analysis->area_square_m : null,
            'resolution_dpi' => $analysis->resolution_dpi !== null ? (float) $analysis->resolution_dpi : null,
            'colour_mode' => $analysis->colour_mode,
            'has_transparency' => $analysis->has_transparency,
            'metadata' => $analysis->metadata,
            'warnings' => $analysis->warnings ?? [],
            'errors' => $analysis->errors ?? [],
            'analyzed_at' => $analysis->analyzed_at?->toIso8601String(),
            'pages' => $analysis->pages->map(fn ($page) => [
                'page_number' => $page->page_number,
                'width_mm' => $page->width_mm !== null ? (float) $page->width_mm : null,
                'height_mm' => $page->height_mm !== null ? (float) $page->height_mm : null,
                'area_square_m' => $page->area_square_m !== null ? (float) $page->area_square_m : null,
                'resolution_dpi' => $page->resolution_dpi !== null ? (float) $page->resolution_dpi : null,
                'colour_mode' => $page->colour_mode,
                'metadata' => $page->metadata,
                'warnings' => $page->warnings ?? [],
            ])->values()->all(),
            'quotation' => $analysis->quotation_id
                ? $this->quotationContext((int) $analysis->quotation_id)
                : null,
            'production_job' => $analysis->production_job_card_id
                ? $this->productionReality((int) $analysis->production_job_card_id)
                : null,
            'material_context_placeholder' => [
                'ready' => false,
                'note' => __('Material cost context available via materialContext() when item is linked.'),
            ],
            'machine_context_placeholder' => [
                'ready' => false,
                'note' => __('Machine cost context available via machineContext() when machine is selected.'),
            ],
            'ink_context_placeholder' => [
                'ready' => false,
                'note' => __('Ink cost context available via inkContext() in PI2+.'),
            ],
            'storage' => $includeStoragePath ? [
                'disk' => $analysis->disk,
                'file_path' => $analysis->file_path,
            ] : [
                'available' => true,
                'path_redacted' => true,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function artworkColourContext(PrintArtworkAnalysis $analysis): array
    {
        $analysis->loadMissing(['pages']);

        return [
            'analysis_id' => $analysis->id,
            'colour_analysis_status' => $analysis->colour_analysis_status?->value ?? (string) $analysis->colour_analysis_status,
            'coverage_class' => $analysis->coverage_class?->value ?? (string) $analysis->coverage_class,
            'rgb_coverage_percent' => $analysis->rgb_coverage_percent !== null ? (float) $analysis->rgb_coverage_percent : null,
            'cmyk_coverage_percent' => $analysis->cmyk_coverage_percent !== null ? (float) $analysis->cmyk_coverage_percent : null,
            'cyan_coverage_percent' => $analysis->cyan_coverage_percent !== null ? (float) $analysis->cyan_coverage_percent : null,
            'magenta_coverage_percent' => $analysis->magenta_coverage_percent !== null ? (float) $analysis->magenta_coverage_percent : null,
            'yellow_coverage_percent' => $analysis->yellow_coverage_percent !== null ? (float) $analysis->yellow_coverage_percent : null,
            'black_coverage_percent' => $analysis->black_coverage_percent !== null ? (float) $analysis->black_coverage_percent : null,
            'white_area_percent' => $analysis->white_area_percent !== null ? (float) $analysis->white_area_percent : null,
            'transparent_area_percent' => $analysis->transparent_area_percent !== null ? (float) $analysis->transparent_area_percent : null,
            'average_ink_density_percent' => $analysis->average_ink_density_percent !== null ? (float) $analysis->average_ink_density_percent : null,
            'heavy_coverage_score' => $analysis->heavy_coverage_score !== null ? (float) $analysis->heavy_coverage_score : null,
            'dominant_colours' => $analysis->dominant_colours ?? [],
            'warnings' => $analysis->colour_analysis_warnings ?? [],
            'colour_analyzed_at' => $analysis->colour_analyzed_at?->toIso8601String(),
            'pages' => $analysis->pages->map(fn ($page) => [
                'page_number' => $page->page_number,
                'rgb_coverage_percent' => $page->rgb_coverage_percent !== null ? (float) $page->rgb_coverage_percent : null,
                'cmyk_coverage_percent' => $page->cmyk_coverage_percent !== null ? (float) $page->cmyk_coverage_percent : null,
                'cyan_coverage_percent' => $page->cyan_coverage_percent !== null ? (float) $page->cyan_coverage_percent : null,
                'magenta_coverage_percent' => $page->magenta_coverage_percent !== null ? (float) $page->magenta_coverage_percent : null,
                'yellow_coverage_percent' => $page->yellow_coverage_percent !== null ? (float) $page->yellow_coverage_percent : null,
                'black_coverage_percent' => $page->black_coverage_percent !== null ? (float) $page->black_coverage_percent : null,
                'white_area_percent' => $page->white_area_percent !== null ? (float) $page->white_area_percent : null,
                'transparent_area_percent' => $page->transparent_area_percent !== null ? (float) $page->transparent_area_percent : null,
                'coverage_class' => $page->coverage_class?->value ?? (string) $page->coverage_class,
                'dominant_colours' => $page->dominant_colours ?? [],
            ])->values()->all(),
            'ink_costing_ready' => $this->colourDataReadyForInkCosting($analysis),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function inkEstimationContext(PrintArtworkAnalysis $analysis): array
    {
        $analysis->loadMissing(['pages', 'inkEstimates.inkProfile']);

        $colour = $this->artworkColourContext($analysis);
        $estimate = $analysis->inkEstimates->first();

        return [
            'analysis_id' => $analysis->id,
            'ink_costing_enabled' => (bool) config('printing_intelligence.ink_costing_enabled', true),
            'coverage' => [
                'coverage_class' => $colour['coverage_class'],
                'rgb_coverage_percent' => $colour['rgb_coverage_percent'],
                'cmyk_coverage_percent' => $colour['cmyk_coverage_percent'],
                'cyan_coverage_percent' => $colour['cyan_coverage_percent'],
                'magenta_coverage_percent' => $colour['magenta_coverage_percent'],
                'yellow_coverage_percent' => $colour['yellow_coverage_percent'],
                'black_coverage_percent' => $colour['black_coverage_percent'],
                'white_area_percent' => $colour['white_area_percent'],
                'area_square_m' => $analysis->area_square_m !== null ? (float) $analysis->area_square_m : null,
                'page_count' => $analysis->page_count,
            ],
            'ink_estimate' => $estimate ? [
                'estimate_id' => $estimate->id,
                'estimation_status' => $estimate->estimation_status?->value ?? (string) $estimate->estimation_status,
                'coverage_percent' => $estimate->coverage_percent !== null ? (float) $estimate->coverage_percent : null,
                'coverage_area_sq_m' => $estimate->coverage_area_sq_m !== null ? (float) $estimate->coverage_area_sq_m : null,
                'estimated_cyan_ml' => $estimate->estimated_cyan_ml !== null ? (float) $estimate->estimated_cyan_ml : null,
                'estimated_magenta_ml' => $estimate->estimated_magenta_ml !== null ? (float) $estimate->estimated_magenta_ml : null,
                'estimated_yellow_ml' => $estimate->estimated_yellow_ml !== null ? (float) $estimate->estimated_yellow_ml : null,
                'estimated_black_ml' => $estimate->estimated_black_ml !== null ? (float) $estimate->estimated_black_ml : null,
                'estimated_total_ml' => $estimate->estimated_total_ml !== null ? (float) $estimate->estimated_total_ml : null,
                'estimated_cartridge_percent' => $estimate->estimated_cartridge_percent !== null ? (float) $estimate->estimated_cartridge_percent : null,
                'estimated_ink_cost' => $estimate->estimated_ink_cost !== null ? (float) $estimate->estimated_ink_cost : null,
                'confidence_score' => $estimate->confidence_score !== null ? (float) $estimate->confidence_score : null,
                'formula_version' => $estimate->formula_version,
                'warnings' => $estimate->warnings ?? [],
                'estimated_at' => $estimate->estimated_at?->toIso8601String(),
            ] : null,
            'ink_profile' => $estimate?->inkProfile
                ? $this->inkContext((int) $estimate->ink_profile_id)
                : null,
            'warnings' => array_values(array_unique(array_merge(
                $colour['warnings'] ?? [],
                $estimate?->warnings ?? [],
            ))),
        ];
    }

    protected function colourDataReadyForInkCosting(PrintArtworkAnalysis $analysis): bool
    {
        if (! config('printing_intelligence.ink_costing_enabled', true)) {
            return false;
        }

        return $analysis->colour_analysis_status !== null
            && in_array($analysis->colour_analysis_status->value ?? (string) $analysis->colour_analysis_status, ['completed', 'manual_review'], true)
            && ($analysis->rgb_coverage_percent !== null || $analysis->cmyk_coverage_percent !== null);
    }

    /**
     * @return array<string, mixed>
     */
    public function productionCostEstimationContext(PrintArtworkAnalysis $analysis): array
    {
        $analysis->loadMissing(['productionEstimate.machineProfile', 'inkEstimates', 'pages']);

        $estimate = $analysis->productionEstimate;
        $inkContext = $this->inkEstimationContext($analysis);

        return [
            'analysis_id' => $analysis->id,
            'production_costing_enabled' => (bool) config('printing_intelligence.production_costing_enabled', true),
            'coverage' => $inkContext['coverage'],
            'ink_estimate' => $inkContext['ink_estimate'],
            'production_estimate' => $estimate ? [
                'estimate_id' => $estimate->id,
                'estimation_status' => $estimate->estimation_status?->value ?? (string) $estimate->estimation_status,
                'quantity' => $estimate->quantity,
                'total_area_sq_m' => $estimate->total_area_sq_m !== null ? (float) $estimate->total_area_sq_m : null,
                'estimated_run_hours' => $estimate->estimated_run_hours !== null ? (float) $estimate->estimated_run_hours : null,
                'estimated_setup_cost' => $estimate->estimated_setup_cost !== null ? (float) $estimate->estimated_setup_cost : null,
                'estimated_electricity_cost' => $estimate->estimated_electricity_cost !== null ? (float) $estimate->estimated_electricity_cost : null,
                'estimated_machine_cost' => $estimate->estimated_machine_cost !== null ? (float) $estimate->estimated_machine_cost : null,
                'estimated_labour_cost' => $estimate->estimated_labour_cost !== null ? (float) $estimate->estimated_labour_cost : null,
                'estimated_ink_cost' => $estimate->estimated_ink_cost !== null ? (float) $estimate->estimated_ink_cost : null,
                'estimated_material_cost' => $estimate->estimated_material_cost !== null ? (float) $estimate->estimated_material_cost : null,
                'estimated_overhead_cost' => $estimate->estimated_overhead_cost !== null ? (float) $estimate->estimated_overhead_cost : null,
                'estimated_total_production_cost' => $estimate->estimated_total_production_cost !== null ? (float) $estimate->estimated_total_production_cost : null,
                'selection_score' => $estimate->selection_score !== null ? (float) $estimate->selection_score : null,
                'confidence_score' => $estimate->confidence_score !== null ? (float) $estimate->confidence_score : null,
                'formula_version' => $estimate->formula_version,
                'machine_alternatives' => $estimate->machine_alternatives ?? [],
                'warnings' => $estimate->warnings ?? [],
                'estimated_at' => $estimate->estimated_at?->toIso8601String(),
            ] : null,
            'machine' => $estimate?->machineProfile
                ? $this->machineContext((int) $estimate->machine_profile_id, (float) ($estimate->estimated_run_hours ?? 1))
                : null,
            'warnings' => array_values(array_unique(array_merge(
                $inkContext['warnings'] ?? [],
                $estimate?->warnings ?? [],
            ))),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function quotationEstimateContext(PrintArtworkAnalysis $analysis, int $quantity = 1): array
    {
        $analysis->loadMissing(['quotationEstimates']);

        $estimate = $analysis->quotationEstimates
            ->firstWhere('quantity', $quantity) ?? $analysis->quotationEstimates->first();

        return [
            'analysis_id' => $analysis->id,
            'quantity' => $quantity,
            'quotation_estimation_enabled' => (bool) config('printing_intelligence.quotation_estimation_enabled', true),
            'quotation_estimate' => $estimate ? [
                'estimate_id' => $estimate->id,
                'estimation_status' => $estimate->estimation_status?->value ?? (string) $estimate->estimation_status,
                'estimated_material_cost' => (float) $estimate->estimated_material_cost,
                'estimated_ink_cost' => (float) $estimate->estimated_ink_cost,
                'estimated_machine_cost' => (float) $estimate->estimated_machine_cost,
                'estimated_labour_cost' => (float) $estimate->estimated_labour_cost,
                'estimated_electricity_cost' => (float) $estimate->estimated_electricity_cost,
                'estimated_overhead_cost' => (float) $estimate->estimated_overhead_cost,
                'estimated_wastage_cost' => (float) $estimate->estimated_wastage_cost,
                'estimated_total_cost' => (float) $estimate->estimated_total_cost,
                'minimum_selling_price' => (float) $estimate->minimum_selling_price,
                'recommended_selling_price' => (float) $estimate->recommended_selling_price,
                'expected_margin_percent' => $estimate->expected_margin_percent !== null ? (float) $estimate->expected_margin_percent : null,
                'confidence_score' => $estimate->confidence_score !== null ? (float) $estimate->confidence_score : null,
                'formula_version' => $estimate->formula_version,
                'calculation_breakdown' => $estimate->calculation_breakdown ?? [],
                'warnings' => $estimate->warnings ?? [],
                'applied_at' => $estimate->applied_at?->toIso8601String(),
            ] : null,
            'production_context' => $this->productionCostEstimationContext($analysis),
            'ink_context' => $this->inkEstimationContext($analysis),
            'warnings' => $estimate?->warnings ?? [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function estimateActualContext(PrintQuotationEstimate $estimate): array
    {
        $estimate->loadMissing(['actualComparisons', 'quotation', 'analysis']);

        $comparison = $estimate->actualComparisons->sortByDesc('compared_at')->first();

        return [
            'estimate_id' => $estimate->id,
            'quotation_id' => $estimate->quotation_id,
            'estimate_actual_learning_enabled' => (bool) config('printing_intelligence.estimate_actual_learning_enabled', true),
            'comparison' => $comparison ? $this->formatComparison($comparison) : null,
            'estimated_total_cost' => (float) $estimate->estimated_total_cost,
            'recommended_selling_price' => (float) $estimate->recommended_selling_price,
            'confidence_score' => $estimate->confidence_score !== null ? (float) $estimate->confidence_score : null,
            'warnings' => $comparison?->warnings ?? [],
            'recommendation' => $comparison?->recommendation,
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function accuracyAnalyticsContext(array $filters = []): array
    {
        $analytics = app(EstimateAccuracyAnalyticsService::class)->aggregate($filters);

        return [
            'estimate_actual_learning_enabled' => (bool) config('printing_intelligence.estimate_actual_learning_enabled', true),
            'formula_version' => config('printing_intelligence.estimate_actual_formula_version', 'PI6-V1'),
            'summary' => $analytics,
            'top_variance_drivers' => $analytics['top_variance_drivers'] ?? [],
            'warnings' => $analytics['comparison_count'] === 0
                ? [__('No estimate vs actual comparisons recorded yet.')]
                : [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function formatComparison(PrintEstimateActualComparison $comparison): array
    {
        return [
            'comparison_id' => $comparison->id,
            'comparison_status' => $comparison->comparison_status?->value ?? (string) $comparison->comparison_status,
            'variance_class' => $comparison->variance_class?->value ?? (string) $comparison->variance_class,
            'accuracy_score' => $comparison->accuracy_score !== null ? (float) $comparison->accuracy_score : null,
            'estimated_total_cost' => (float) $comparison->estimated_total_cost,
            'actual_total_cost' => (float) $comparison->actual_total_cost,
            'total_cost_variance' => (float) $comparison->total_cost_variance,
            'total_cost_variance_percent' => $comparison->total_cost_variance_percent !== null
                ? (float) $comparison->total_cost_variance_percent : null,
            'recommended_price' => $comparison->recommended_price !== null ? (float) $comparison->recommended_price : null,
            'actual_selling_price' => $comparison->actual_selling_price !== null ? (float) $comparison->actual_selling_price : null,
            'estimated_margin_percent' => $comparison->estimated_margin_percent !== null
                ? (float) $comparison->estimated_margin_percent : null,
            'actual_margin_percent' => $comparison->actual_margin_percent !== null
                ? (float) $comparison->actual_margin_percent : null,
            'margin_variance_percent' => $comparison->margin_variance_percent !== null
                ? (float) $comparison->margin_variance_percent : null,
            'categories' => [
                'material' => [
                    'estimated' => (float) $comparison->estimated_material_cost,
                    'actual' => (float) $comparison->actual_material_cost,
                    'variance_percent' => $comparison->material_cost_variance_percent !== null
                        ? (float) $comparison->material_cost_variance_percent : null,
                ],
                'ink' => [
                    'estimated' => (float) $comparison->estimated_ink_cost,
                    'actual' => (float) $comparison->actual_ink_cost,
                    'variance_percent' => $comparison->ink_cost_variance_percent !== null
                        ? (float) $comparison->ink_cost_variance_percent : null,
                ],
                'machine' => [
                    'estimated' => (float) $comparison->estimated_machine_cost,
                    'actual' => (float) $comparison->actual_machine_cost,
                    'variance_percent' => $comparison->machine_cost_variance_percent !== null
                        ? (float) $comparison->machine_cost_variance_percent : null,
                ],
                'labour' => [
                    'estimated' => (float) $comparison->estimated_labour_cost,
                    'actual' => (float) $comparison->actual_labour_cost,
                    'variance_percent' => $comparison->labour_cost_variance_percent !== null
                        ? (float) $comparison->labour_cost_variance_percent : null,
                ],
                'overhead' => [
                    'estimated' => (float) $comparison->estimated_overhead_cost,
                    'actual' => (float) $comparison->actual_overhead_cost,
                    'variance_percent' => $comparison->overhead_cost_variance_percent !== null
                        ? (float) $comparison->overhead_cost_variance_percent : null,
                ],
            ],
            'calculation_breakdown' => $comparison->calculation_breakdown ?? [],
            'warnings' => $comparison->warnings ?? [],
            'recommendation' => $comparison->recommendation,
            'compared_at' => $comparison->compared_at?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function calibrationRecommendations(?int $companyId = null): array
    {
        $companyId ??= (int) (tenant()->companyId() ?? auth()->user()?->company_id);

        $rules = \App\Models\PrintingIntelligence\PrintCalibrationRule::query()
            ->where('company_id', $companyId)
            ->whereIn('status', ['draft', 'pending_review'])
            ->latest('id')
            ->limit(50)
            ->get();

        return [
            'calibration_enabled' => (bool) config('printing_intelligence.calibration_recommendation_enabled', true),
            'formula_version' => config('printing_intelligence.calibration_formula_version', 'PI7-V1'),
            'recommendations' => $rules->map(fn ($rule) => [
                'rule_id' => $rule->id,
                'rule_type' => $rule->rule_type?->value,
                'rule_key' => $rule->rule_key,
                'current_value' => $rule->current_value !== null ? (float) $rule->current_value : null,
                'proposed_value' => $rule->proposed_value !== null ? (float) $rule->proposed_value : null,
                'status' => $rule->status?->value,
                'reason' => $rule->reason,
                'confidence' => $rule->metadata['confidence'] ?? null,
                'evidence' => $rule->metadata['evidence'] ?? [],
            ])->values()->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function activeCostingProfile(?int $companyId = null): array
    {
        return app(ActiveCostingProfileService::class)->profile($companyId);
    }

    /**
     * @return array<string, string>
     */
    public function formulaVersions(?int $companyId = null): array
    {
        return app(CostFormulaVersionService::class)->currentVersions($companyId);
    }

    /**
     * @return array<string, mixed>
     */
    public function impactSimulation(int $ruleId): array
    {
        $rule = \App\Models\PrintingIntelligence\PrintCalibrationRule::query()->findOrFail($ruleId);

        return app(CalibrationImpactSimulationService::class)->simulate($rule);
    }

    /**
     * @return array<string, mixed>
     */
    public function profitabilityOverview(?int $companyId = null, ?int $branchId = null, array $filters = []): array
    {
        $companyId ??= (int) (tenant()->companyId() ?? auth()->user()?->company_id);
        $days = (int) ($filters['days'] ?? 90);

        $query = PrintProfitabilitySnapshot::query()
            ->where('company_id', $companyId)
            ->where('snapshot_type', ProfitabilitySnapshotType::Job)
            ->where('snapshot_date', '>=', now()->subDays($days)->toDateString());

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $jobs = $query->with(['jobCard:id,job_card_number', 'customer:id,company_name'])->get();
        $analytics = app(ProfitabilityAnalyticsService::class)->summarize([
            'company_id' => $companyId,
            'days' => $days,
        ]);
        $customers = app(CustomerProfitabilityService::class)->analyze(['company_id' => $companyId, 'days' => $days]);
        $machines = app(MachineProfitabilityService::class)->analyze(['company_id' => $companyId, 'days' => $days]);
        $products = app(ProductProfitabilityService::class)->analyze(['company_id' => $companyId, 'days' => $days]);

        $topProfitable = $jobs->sortByDesc('gross_profit')->take(10)->values()
            ->map(fn ($row) => $this->formatJobSnapshot($row))->all();
        $topLossMaking = $jobs->where('profitability_class', ProfitabilityClass::LossMaking)
            ->sortBy('gross_profit')->take(10)->values()
            ->map(fn ($row) => $this->formatJobSnapshot($row))->all();

        return [
            'formula_version' => config('printing_intelligence.profitability_formula_version', 'PI8-V1'),
            'enabled' => (bool) config('printing_intelligence.profitability_intelligence_enabled', true),
            'summary' => [
                'total_revenue' => $analytics['total_revenue'],
                'total_cost' => $analytics['total_cost'],
                'total_profit' => $analytics['total_profit'],
                'average_margin' => $analytics['average_margin'],
                'excellent_jobs' => $analytics['excellent_jobs'],
                'loss_making_jobs' => $analytics['loss_making_jobs'],
                'job_count' => $jobs->count(),
            ],
            'most_profitable_customer' => $customers['most_profitable'],
            'most_profitable_machine' => $machines['best_performing'],
            'top_profitable_jobs' => $topProfitable,
            'top_loss_making_jobs' => $topLossMaking,
            'top_customers' => $customers['rankings'],
            'top_machines' => $machines['rankings'],
            'top_products' => $products['rankings'],
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function jobProfitability(array $filters = []): array
    {
        $companyId = (int) ($filters['company_id'] ?? tenant()->companyId() ?? auth()->user()?->company_id);
        $days = (int) ($filters['days'] ?? 90);

        $rows = PrintProfitabilitySnapshot::query()
            ->where('company_id', $companyId)
            ->where('snapshot_type', ProfitabilitySnapshotType::Job)
            ->where('snapshot_date', '>=', now()->subDays($days)->toDateString())
            ->with(['jobCard:id,job_card_number', 'customer:id,company_name'])
            ->orderByDesc('gross_profit')
            ->limit(100)
            ->get();

        return [
            'jobs' => $rows->map(fn ($row) => $this->formatJobSnapshot($row))->all(),
            'count' => $rows->count(),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function customerProfitability(array $filters = []): array
    {
        return app(CustomerProfitabilityService::class)->analyze($filters);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function machineProfitability(array $filters = []): array
    {
        return app(MachineProfitabilityService::class)->analyze($filters);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function productProfitability(array $filters = []): array
    {
        return app(ProductProfitabilityService::class)->analyze($filters);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function marginLeakage(array $filters = []): array
    {
        return app(MarginLeakageAnalysisService::class)->analyze($filters);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function analyticsSummary(array $filters = []): array
    {
        return app(ProfitabilityAnalyticsService::class)->summarize($filters);
    }

    /**
     * @return array<string, mixed>
     */
    protected function formatJobSnapshot(PrintProfitabilitySnapshot $row): array
    {
        return [
            'snapshot_id' => $row->id,
            'production_job_card_id' => $row->production_job_card_id,
            'job_card_number' => $row->jobCard?->job_card_number ?? $row->metadata['job_card_number'] ?? null,
            'customer_name' => $row->customer?->company_name,
            'revenue' => (float) $row->revenue,
            'total_cost' => (float) $row->total_cost,
            'gross_profit' => (float) $row->gross_profit,
            'gross_margin_percent' => $row->gross_margin_percent !== null ? (float) $row->gross_margin_percent : null,
            'profitability_class' => $row->profitability_class?->value,
            'profitability_score' => $row->profitability_score !== null ? (float) $row->profitability_score : null,
            'snapshot_date' => $row->snapshot_date?->toDateString(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function overviewMetrics(int $companyId, ?int $branchId = null): array
    {
        $window = (int) config('inventory_intelligence.default_snapshot_window', 30);
        $velocityCounts = $this->velocityService->overviewCounts($companyId, $branchId, $window);

        $itemsWithCost = InventoryValuation::query()
            ->where('company_id', $companyId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->where('average_unit_cost', '>', 0)
            ->distinct('inventory_item_id')
            ->count('inventory_item_id');

        $itemsWithVelocity = InventoryVelocitySnapshot::query()
            ->where('company_id', $companyId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->where('movement_window_days', $window)
            ->where('period_end', today()->toDateString())
            ->distinct('inventory_item_id')
            ->count('inventory_item_id');

        $materialsTracked = InventoryItem::query()
            ->where('company_id', $companyId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->where('is_active', true)
            ->count();

        $stockoutRisk = (int) $velocityCounts['critical_stockout'] + (int) $velocityCounts['high_risk'];

        $avgDays = $velocityCounts['average_days_to_depletion'];
        $healthScore = $avgDays === null
            ? null
            : round(min(100, max(0, ($avgDays / 30) * 100)), 1);

        return [
            'materials_tracked' => $materialsTracked,
            'ink_profiles' => PrintInkProfile::query()->where('company_id', $companyId)->where('active', true)->count(),
            'machine_profiles' => MachineProfile::query()->where('company_id', $companyId)->count(),
            'items_with_cost_data' => $itemsWithCost,
            'items_with_velocity_data' => $itemsWithVelocity,
            'items_at_stockout_risk' => $stockoutRisk,
            'dead_stock_value' => (float) $velocityCounts['dead_stock_value'],
            'average_inventory_health' => $healthScore,
            'critical_stockout_items' => (int) $velocityCounts['critical_stockout'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function forecastOverview(?int $companyId = null, array $filters = []): array
    {
        $companyId ??= (int) (tenant()->companyId() ?? auth()->user()?->company_id);
        $filters['company_id'] = $companyId;

        $revenue = app(RevenueForecastService::class)->forecast($filters);
        $profit = app(ProfitForecastService::class)->forecast($filters);
        $capacity = app(CapacityForecastService::class)->forecast($filters);
        $demand = app(DemandForecastService::class)->forecast($filters);
        $customers = app(CustomerTrendForecastService::class)->forecast($filters);
        $inventory = app(InventoryRiskForecastService::class)->forecast($filters);
        $alerts = app(ExecutiveForecastAlertService::class)->generate($filters);
        $accuracy = app(EstimateAccuracyAnalyticsService::class)->aggregate(['company_id' => $companyId]);
        $profitability = $this->profitabilityOverview($companyId, null, $filters);

        return [
            'formula_version' => config('printing_intelligence.forecast_formula_version', 'PI9-V1'),
            'enabled' => (bool) config('printing_intelligence.executive_forecasting_enabled', true),
            'forecast_revenue' => $revenue['next_month'] ?? null,
            'forecast_profit' => $profit['forecast_profit'] ?? null,
            'forecast_margin' => $profit['forecast_margin_percent'] ?? null,
            'forecast_accuracy' => $accuracy['average_accuracy_score'] ?? null,
            'capacity_utilization_forecast' => $capacity['overall_utilization_forecast'] ?? null,
            'top_growth_customer' => ($customers['top_growth_customers'][0] ?? null),
            'top_demand_product' => ($demand['growing_demand'][0] ?? $demand['products'][0] ?? null),
            'highest_inventory_risk' => $inventory['highest_risk'] ?? null,
            'most_profitable_machine' => $profitability['most_profitable_machine'] ?? null,
            'customer_concentration_risk_percent' => $customers['customer_concentration_risk_percent'] ?? null,
            'alerts' => $alerts['alerts'] ?? [],
            'alert_count' => $alerts['alert_count'] ?? 0,
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function revenueForecast(array $filters = []): array
    {
        return app(RevenueForecastService::class)->forecast($filters);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function profitForecast(array $filters = []): array
    {
        return app(ProfitForecastService::class)->forecast($filters);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function capacityForecast(array $filters = []): array
    {
        return app(CapacityForecastService::class)->forecast($filters);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function demandForecast(array $filters = []): array
    {
        return app(DemandForecastService::class)->forecast($filters);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function inventoryRiskForecast(array $filters = []): array
    {
        return app(InventoryRiskForecastService::class)->forecast($filters);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function customerTrendForecast(array $filters = []): array
    {
        return app(CustomerTrendForecastService::class)->forecast($filters);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function scenarioSimulation(array $filters = []): array
    {
        return app(ScenarioSimulationService::class)->simulate($filters);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function executiveAlerts(array $filters = []): array
    {
        return app(ExecutiveForecastAlertService::class)->generate($filters);
    }

    /**
     * @return array<string, mixed>
     */
    public function platformOverviewContext(int $companyId, ?int $branchId = null): array
    {
        return app(PrintingIntelligenceWorkspaceContext::class)->platformOverview($companyId, $branchId);
    }

    /**
     * @return array<string, mixed>
     */
    public function machineIntelligenceContext(int $companyId, ?int $branchId = null): array
    {
        return app(PrintingIntelligenceWorkspaceContext::class)->machineIntelligence($companyId, $branchId);
    }

    /**
     * @return array<string, mixed>
     */
    public function inkIntelligenceContext(int $companyId, ?int $branchId = null): array
    {
        return app(PrintingIntelligenceWorkspaceContext::class)->inkIntelligence($companyId, $branchId);
    }

    /**
     * @return array<string, mixed>
     */
    public function materialIntelligenceContext(int $companyId, ?int $branchId = null): array
    {
        return app(PrintingIntelligenceWorkspaceContext::class)->materialIntelligence($companyId, $branchId);
    }

    /**
     * @return array<string, mixed>
     */
    public function costIntelligenceContext(int $companyId, ?int $branchId = null): array
    {
        return app(PrintingIntelligenceWorkspaceContext::class)->costIntelligence($companyId, $branchId);
    }

    /**
     * @return array<string, mixed>
     */
    public function quotationIntelligenceContext(int $companyId, ?int $branchId = null): array
    {
        return app(PrintingIntelligenceWorkspaceContext::class)->quotationIntelligence($companyId, $branchId);
    }

    /**
     * @return array<string, mixed>
     */
    public function advisorOverview(int $companyId, ?int $branchId = null, ?string $type = null, ?string $status = null): array
    {
        $typeEnum = $type ? AdvisorRecommendationType::tryFrom($type) : null;
        $statusEnum = $status ? AdvisorRecommendationStatus::tryFrom($status) : null;

        return app(PrintOperationsAdvisorService::class)->overview($companyId, $branchId, $typeEnum, $statusEnum);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return list<array<string, mixed>>
     */
    public function quotationRecommendations(array $filters = []): array
    {
        return app(\App\Services\PrintingIntelligence\Advisor\QuotationAdvisorService::class)->recommend($filters);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return list<array<string, mixed>>
     */
    public function artworkRecommendations(array $filters = []): array
    {
        return app(\App\Services\PrintingIntelligence\Advisor\ArtworkAdvisorService::class)->recommend($filters);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return list<array<string, mixed>>
     */
    public function machineRecommendations(array $filters = []): array
    {
        return app(\App\Services\PrintingIntelligence\Advisor\MachineAdvisorService::class)->recommend($filters);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return list<array<string, mixed>>
     */
    public function inventoryRecommendations(array $filters = []): array
    {
        return app(\App\Services\PrintingIntelligence\Advisor\InventoryAdvisorService::class)->recommend($filters);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return list<array<string, mixed>>
     */
    public function customerRecommendations(array $filters = []): array
    {
        return app(\App\Services\PrintingIntelligence\Advisor\CustomerAdvisorService::class)->recommend($filters);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return list<array<string, mixed>>
     */
    public function profitabilityRecommendations(array $filters = []): array
    {
        return app(\App\Services\PrintingIntelligence\Advisor\ProfitabilityAdvisorService::class)->recommend($filters);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return list<array<string, mixed>>
     */
    public function forecastRecommendations(array $filters = []): array
    {
        return app(\App\Services\PrintingIntelligence\Advisor\ForecastAdvisorService::class)->recommend($filters);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function executiveAdvisorSummary(array $filters = []): array
    {
        return app(AdvisorExecutiveSummaryService::class)->summarize($filters);
    }
}
