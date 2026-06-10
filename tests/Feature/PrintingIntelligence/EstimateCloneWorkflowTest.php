<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Enums\QuotationEstimationStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\PrintingIntelligence\PrintArtworkAnalysis;
use App\Models\PrintingIntelligence\PrintQuotationEstimate;
use App\Services\PrintingIntelligence\QuotationEstimateLifecycleService;
use App\Services\PrintingIntelligence\QuotationEstimationService;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EstimateCloneWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_clone_via_estimation_service_creates_writable_successor(): void
    {
        $original = $this->estimate();

        $clone = app(QuotationEstimationService::class)->cloneEstimate($original);

        $this->assertNotSame($original->id, $clone->id);
        $this->assertSame(2, (int) $clone->version);
        $this->assertNull($clone->applied_at);
        $this->assertSame(QuotationEstimationStatus::Completed, $clone->estimation_status);
        $this->assertSame($original->id, $clone->metadata['cloned_from_estimate_id'] ?? null);
        $this->assertEqualsWithDelta(
            (float) $original->estimated_total_cost,
            (float) $clone->estimated_total_cost,
            0.01,
        );
    }

    public function test_clone_via_lifecycle_service_increments_version(): void
    {
        $original = $this->estimate();

        $clone = app(QuotationEstimateLifecycleService::class)->cloneEstimate($original);

        $this->assertDatabaseHas('print_quotation_estimates', [
            'id' => $clone->id,
            'version' => 2,
            'print_artwork_analysis_id' => $original->print_artwork_analysis_id,
        ]);
    }

    protected function estimate(): PrintQuotationEstimate
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();

        $analysis = PrintArtworkAnalysis::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'original_filename' => 'clone.png',
            'stored_filename' => 'clone.png',
            'file_path' => 'printing-intelligence/artwork/clone.png',
            'disk' => 'local',
            'mime_type' => 'image/png',
            'file_extension' => 'png',
            'file_size_bytes' => 100,
            'file_hash' => hash('sha256', 'clone-estimate'),
            'analysis_status' => \App\Enums\ArtworkAnalysisStatus::Completed,
            'analysis_source' => 'upload',
        ]);

        return PrintQuotationEstimate::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'print_artwork_analysis_id' => $analysis->id,
            'estimation_status' => QuotationEstimationStatus::Completed,
            'quantity' => 1,
            'version' => 1,
            'estimated_material_cost' => 100,
            'estimated_ink_cost' => 50,
            'estimated_machine_cost' => 200,
            'estimated_labour_cost' => 100,
            'estimated_overhead_cost' => 50,
            'estimated_total_cost' => 500,
            'recommended_selling_price' => 750,
            'formula_version' => 'PI5-V1',
        ]);
    }
}
