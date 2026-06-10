<?php

namespace App\Services\PrintingIntelligence\Advisor;

use App\Enums\AdvisorRecommendationType;
use App\Enums\AdvisorSeverity;
use App\Models\PrintingIntelligence\PrintArtworkAnalysis;
use App\Models\PrintingIntelligence\PrintArtworkInkEstimate;

class ArtworkAdvisorService
{
    public function __construct(
        protected AdvisorConfidenceService $confidence,
    ) {}

    /**
     * @param  array{company_id?: int, branch_id?: int|null}  $filters
     * @return list<array<string, mixed>>
     */
    public function recommend(array $filters = []): array
    {
        $companyId = (int) ($filters['company_id'] ?? tenant()->companyId() ?? auth()->user()?->company_id);
        $branchId = $filters['branch_id'] ?? null;

        $analyses = PrintArtworkAnalysis::query()
            ->where('company_id', $companyId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->latest('id')
            ->limit(40)
            ->with('inkEstimates')
            ->get();

        $recommendations = [];

        foreach ($analyses as $analysis) {
            $coverage = (float) ($analysis->cmyk_coverage_percent ?? $analysis->rgb_coverage_percent ?? 0);

            if ($coverage > 55) {
                $recommendations[] = AdvisorRecommendationWriter::payload(
                    AdvisorRecommendationType::Artwork,
                    $coverage > 75 ? AdvisorSeverity::High : AdvisorSeverity::Medium,
                    "artwork:high_coverage:{$analysis->id}",
                    __('High coverage artwork'),
                    __('Artwork :file coverage :pct%.', ['file' => $analysis->original_filename, 'pct' => $coverage]),
                    __('This artwork exceeds typical ink coverage for its category.'),
                    'PI2',
                    $this->confidence->score(['data_points' => 2, 'signal_strength' => min(100, $coverage)]),
                    __('Consider production review or premium ink allowance.'),
                    PrintArtworkAnalysis::class,
                    $analysis->id,
                    ['coverage_percent' => $coverage],
                );
            }

            $black = (float) ($analysis->black_coverage_percent ?? 0);
            if ($black > 40) {
                $recommendations[] = AdvisorRecommendationWriter::payload(
                    AdvisorRecommendationType::Artwork,
                    AdvisorSeverity::Medium,
                    "artwork:solid_fill:{$analysis->id}",
                    __('Large solid fill detected'),
                    __('Black channel :pct% on :file.', ['pct' => $black, 'file' => $analysis->original_filename]),
                    __('Large solid fills increase ink consumption and drying time risk.'),
                    'PI2',
                    $this->confidence->score(['data_points' => 2, 'signal_strength' => $black]),
                    __('Validate machine drying capacity before scheduling.'),
                    PrintArtworkAnalysis::class,
                    $analysis->id,
                    ['black_coverage_percent' => $black],
                );
            }

            /** @var PrintArtworkInkEstimate|null $ink */
            $ink = $analysis->inkEstimates->first();
            if ($ink !== null && (float) ($ink->estimated_total_ml ?? 0) > 50) {
                $recommendations[] = AdvisorRecommendationWriter::payload(
                    AdvisorRecommendationType::Artwork,
                    AdvisorSeverity::High,
                    "artwork:high_consumption:{$analysis->id}",
                    __('High ink consumption'),
                    __('Estimated :ml ml ink on :file.', ['ml' => $ink->estimated_total_ml, 'file' => $analysis->original_filename]),
                    __('Ink consumption estimate is above typical job thresholds.'),
                    'PI3',
                    $this->confidence->score(['data_points' => 3, 'signal_strength' => min(100, (float) $ink->estimated_total_ml)]),
                    __('Reconcile ink profile yield before quoting.'),
                    PrintArtworkAnalysis::class,
                    $analysis->id,
                    ['estimated_total_ml' => $ink->estimated_total_ml],
                );
            }

            if ($analysis->page_count > 20) {
                $recommendations[] = AdvisorRecommendationWriter::payload(
                    AdvisorRecommendationType::Artwork,
                    AdvisorSeverity::Low,
                    "artwork:production_risk:{$analysis->id}",
                    __('Production complexity risk'),
                    __('Artwork :file has :pages pages.', ['file' => $analysis->original_filename, 'pages' => $analysis->page_count]),
                    __('Multi-page artwork may increase setup and run time — validate PI4 machine estimate.'),
                    'PI1',
                    $this->confidence->score(['data_points' => 1, 'required_points' => 2]),
                    __('Run production estimate before final quote.'),
                    PrintArtworkAnalysis::class,
                    $analysis->id,
                    ['page_count' => $analysis->page_count],
                );
            }
        }

        return $recommendations;
    }
}
