<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Enums\EstimateActualComparisonStatus;
use App\Enums\QuotationEstimationStatus;
use App\Models\PrintingIntelligence\PrintEstimateActualComparison;
use App\Models\PrintingIntelligence\PrintQuotationEstimate;
use App\Models\Sales\Quotation;
use App\Services\PrintingIntelligence\EstimateActualComparisonService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\PrintingIntelligence\Concerns\BuildsProductionCostFixtures;
use Tests\TestCase;

class EstimateActualComparisonServiceTest extends TestCase
{
    use BuildsProductionCostFixtures;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedProductionCostStack();
        config(['printing_intelligence.estimate_actual_learning_enabled' => true]);
    }

    public function test_compares_estimate_to_completed_job(): void
    {
        [$company, $branch, $jobCard] = $this->jobWithCostSheet();
        $estimate = $this->createEstimate($company->id, $branch->id, $jobCard->id);

        $comparison = app(EstimateActualComparisonService::class)->compareEstimate($estimate);

        $this->assertSame(EstimateActualComparisonStatus::Completed, $comparison->comparison_status);
        $this->assertGreaterThan(0, (float) $comparison->actual_total_cost);
        $this->assertNotNull($comparison->accuracy_score);
    }

    public function test_rerun_updates_idempotently(): void
    {
        [$company, $branch, $jobCard] = $this->jobWithCostSheet();
        $estimate = $this->createEstimate($company->id, $branch->id, $jobCard->id);
        $service = app(EstimateActualComparisonService::class);

        $first = $service->compareEstimate($estimate);
        $second = $service->compareEstimate($estimate->fresh());

        $this->assertSame($first->id, $second->id);
        $this->assertSame(1, PrintEstimateActualComparison::query()->where('print_quotation_estimate_id', $estimate->id)->count());
    }

    public function test_marks_manual_review_when_actuals_unavailable(): void
    {
        $company = \App\Models\Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = \App\Models\Branch::query()->where('company_id', $company->id)->firstOrFail();

        $estimate = PrintQuotationEstimate::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'print_artwork_analysis_id' => $this->createAnalysis($company->id, $branch->id)->id,
            'estimation_status' => QuotationEstimationStatus::Completed,
            'quantity' => 1,
            'estimated_material_cost' => 100,
            'estimated_total_cost' => 500,
            'recommended_selling_price' => 750,
            'formula_version' => 'PI5-V1',
        ]);

        $comparison = app(EstimateActualComparisonService::class)->compareEstimate($estimate);

        $this->assertSame(EstimateActualComparisonStatus::ManualReview, $comparison->comparison_status);
        $this->assertNotEmpty($comparison->warnings);
    }

    protected function createEstimate(int $companyId, int $branchId, int $jobId): PrintQuotationEstimate
    {
        $quotation = Quotation::factory()->create([
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'total_amount' => 8000,
        ]);

        \App\Models\Production\ProductionJobCard::query()
            ->whereKey($jobId)
            ->update(['quotation_id' => $quotation->id]);

        return PrintQuotationEstimate::query()->create([
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'quotation_id' => $quotation->id,
            'print_artwork_analysis_id' => $this->createAnalysis($companyId, $branchId, $quotation->id)->id,
            'estimation_status' => QuotationEstimationStatus::Completed,
            'quantity' => 1,
            'estimated_material_cost' => 30,
            'estimated_ink_cost' => 10,
            'estimated_machine_cost' => 50,
            'estimated_labour_cost' => 20,
            'estimated_overhead_cost' => 10,
            'estimated_total_cost' => 120,
            'recommended_selling_price' => 200,
            'expected_margin_percent' => 40,
            'confidence_score' => 75,
            'formula_version' => 'PI5-V1',
        ]);
    }

    protected function createAnalysis(int $companyId, int $branchId, ?int $quotationId = null): \App\Models\PrintingIntelligence\PrintArtworkAnalysis
    {
        return \App\Models\PrintingIntelligence\PrintArtworkAnalysis::query()->create([
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'quotation_id' => $quotationId,
            'original_filename' => 'pi6.png',
            'stored_filename' => 'pi6.png',
            'file_path' => 'printing-intelligence/artwork/pi6.png',
            'disk' => 'local',
            'mime_type' => 'image/png',
            'file_extension' => 'png',
            'file_size_bytes' => 100,
            'file_hash' => hash('sha256', uniqid('pi6', true)),
            'analysis_status' => \App\Enums\ArtworkAnalysisStatus::Completed,
            'analysis_source' => 'upload',
        ]);
    }
}
