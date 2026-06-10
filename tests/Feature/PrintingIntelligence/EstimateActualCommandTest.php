<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Enums\QuotationEstimationStatus;
use App\Models\PrintingIntelligence\PrintEstimateActualComparison;
use App\Models\PrintingIntelligence\PrintQuotationEstimate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\PrintingIntelligence\Concerns\BuildsProductionCostFixtures;
use Tests\TestCase;

class EstimateActualCommandTest extends TestCase
{
    use BuildsProductionCostFixtures;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedProductionCostStack();
        config(['printing_intelligence.estimate_actual_learning_enabled' => true]);
    }

    public function test_command_compares_estimates(): void
    {
        [$company, $branch, $jobCard] = $this->jobWithCostSheet();
        $estimate = $this->createEstimate($company->id, $branch->id, $jobCard->id);

        $this->artisan('printing:estimate:compare-actuals', ['--estimate' => $estimate->id])
            ->assertSuccessful();

        $this->assertDatabaseHas('print_estimate_actual_comparisons', [
            'print_quotation_estimate_id' => $estimate->id,
        ]);
    }

    public function test_dry_run_does_not_persist(): void
    {
        [$company, $branch, $jobCard] = $this->jobWithCostSheet();
        $estimate = $this->createEstimate($company->id, $branch->id, $jobCard->id);

        $this->artisan('printing:estimate:compare-actuals', [
            '--estimate' => $estimate->id,
            '--dry-run' => true,
        ])->assertSuccessful();

        $this->assertSame(0, PrintEstimateActualComparison::query()->count());
    }

    protected function createEstimate(int $companyId, int $branchId, int $jobId): PrintQuotationEstimate
    {
        $quotation = \App\Models\Sales\Quotation::factory()->create([
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
            'print_artwork_analysis_id' => \App\Models\PrintingIntelligence\PrintArtworkAnalysis::query()->create([
                'company_id' => $companyId,
                'branch_id' => $branchId,
                'quotation_id' => $quotation->id,
                'original_filename' => 'cmd.png',
                'stored_filename' => 'cmd.png',
                'file_path' => 'printing-intelligence/artwork/cmd.png',
                'disk' => 'local',
                'mime_type' => 'image/png',
                'file_extension' => 'png',
                'file_size_bytes' => 100,
                'file_hash' => hash('sha256', uniqid('cmd', true)),
                'analysis_status' => \App\Enums\ArtworkAnalysisStatus::Completed,
                'analysis_source' => 'upload',
            ])->id,
            'estimation_status' => QuotationEstimationStatus::Completed,
            'quantity' => 1,
            'estimated_total_cost' => 120,
            'recommended_selling_price' => 200,
            'formula_version' => 'PI5-V1',
        ]);
    }
}
