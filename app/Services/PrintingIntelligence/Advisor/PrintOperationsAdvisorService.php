<?php

namespace App\Services\PrintingIntelligence\Advisor;

use App\Enums\AdvisorRecommendationStatus;
use App\Enums\AdvisorRecommendationType;
use App\Enums\AdvisorSeverity;
use App\Enums\ProfitabilityClass;
use App\Enums\ProfitabilitySnapshotType;
use App\Models\PrintingIntelligence\PrintAdvisorRecommendation;
use App\Models\PrintingIntelligence\PrintProfitabilitySnapshot;

class PrintOperationsAdvisorService
{
    public function __construct(
        protected AdvisorRecommendationWriter $writer,
        protected QuotationAdvisorService $quotationAdvisor,
        protected ArtworkAdvisorService $artworkAdvisor,
        protected MachineAdvisorService $machineAdvisor,
        protected InventoryAdvisorService $inventoryAdvisor,
        protected CustomerAdvisorService $customerAdvisor,
        protected ProfitabilityAdvisorService $profitabilityAdvisor,
        protected ForecastAdvisorService $forecastAdvisor,
    ) {}

    /**
     * @param  array{company_id?: int, branch_id?: int|null, type?: string|null}  $filters
     * @return list<\App\Models\PrintingIntelligence\PrintAdvisorRecommendation>
     */
    public function generate(int $companyId, ?int $branchId = null, ?string $type = null, bool $persist = true): array
    {
        if (! config('printing_intelligence.advisor_enabled', true)) {
            return [];
        }

        $filters = array_filter([
            'company_id' => $companyId,
            'branch_id' => $branchId,
        ]);

        $all = [];

        if ($type === null || $type === 'quotation') {
            $all = array_merge($all, $this->quotationAdvisor->recommend($filters));
        }
        if ($type === null || $type === 'artwork') {
            $all = array_merge($all, $this->artworkAdvisor->recommend($filters));
        }
        if ($type === null || $type === 'machine') {
            $all = array_merge($all, $this->machineAdvisor->recommend($filters));
        }
        if ($type === null || $type === 'inventory') {
            $all = array_merge($all, $this->inventoryAdvisor->recommend($filters));
        }
        if ($type === null || $type === 'customer') {
            $all = array_merge($all, $this->customerAdvisor->recommend($filters));
        }
        if ($type === null || $type === 'profitability') {
            $all = array_merge($all, $this->profitabilityAdvisor->recommend($filters));
            $all = array_merge($all, $this->productionLossRecommendations($companyId, $branchId));
        }
        if ($type === null || $type === 'forecast') {
            $all = array_merge($all, $this->forecastAdvisor->recommend($filters));
        }

        return $this->writer->persist($companyId, $branchId, $all, $persist);
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function productionLossRecommendations(int $companyId, ?int $branchId): array
    {
        $confidence = app(AdvisorConfidenceService::class);
        $jobs = PrintProfitabilitySnapshot::query()
            ->where('company_id', $companyId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->where('snapshot_type', ProfitabilitySnapshotType::Job)
            ->where('profitability_class', ProfitabilityClass::LossMaking)
            ->latest('snapshot_date')
            ->limit(10)
            ->get();

        return $jobs->map(fn ($job) => AdvisorRecommendationWriter::payload(
            AdvisorRecommendationType::Production,
            AdvisorSeverity::Critical,
            'production:loss_job:'.$job->production_job_card_id,
            __('Loss-making production job'),
            __('Job snapshot :id shows loss-making class.', ['id' => $job->production_job_card_id]),
            __('Production job completed below cost — review estimate and execution.'),
            'PI8',
            $confidence->score(['data_points' => 3, 'signal_strength' => 85]),
            __('Review job costing with production supervisor.'),
            'production_job_card',
            $job->production_job_card_id,
            ['margin' => $job->gross_margin_percent],
        ))->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function overview(int $companyId, ?int $branchId = null, ?AdvisorRecommendationType $type = null, ?AdvisorRecommendationStatus $status = null): array
    {
        $query = PrintAdvisorRecommendation::query()
            ->where('company_id', $companyId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->when($type, fn ($q) => $q->where('recommendation_type', $type))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest('generated_at');

        $all = PrintAdvisorRecommendation::query()
            ->where('company_id', $companyId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId));

        return [
            'recommendations' => $query->limit(100)->get(),
            'summary' => [
                'open' => (clone $all)->where('status', AdvisorRecommendationStatus::Open)->count(),
                'acknowledged' => (clone $all)->where('status', AdvisorRecommendationStatus::Acknowledged)->count(),
                'dismissed' => (clone $all)->where('status', AdvisorRecommendationStatus::Dismissed)->count(),
                'critical' => (clone $all)->where('severity', AdvisorSeverity::Critical)->where('status', AdvisorRecommendationStatus::Open)->count(),
                'high_confidence' => (clone $all)->where('confidence_score', '>=', 75)->where('status', AdvisorRecommendationStatus::Open)->count(),
            ],
            'read_only' => true,
        ];
    }
}
