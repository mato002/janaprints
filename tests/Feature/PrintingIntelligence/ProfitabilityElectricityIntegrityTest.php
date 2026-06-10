<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Enums\QuotationEstimationStatus;
use App\Enums\QuotationStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\PrintingIntelligence\PrintArtworkAnalysis;
use App\Models\PrintingIntelligence\PrintQuotationEstimate;
use App\Models\Production\ProductionJobCard;
use App\Models\Sales\Quotation;
use App\Services\PrintingIntelligence\ProductionProfitabilityService;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfitabilityElectricityIntegrityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_production_profitability_returns_electricity_from_applied_estimate(): void
    {
        [$jobCard, $estimate] = $this->fixtures();

        $metrics = app(ProductionProfitabilityService::class)->calculateForJob($jobCard->fresh(['quotation']));

        $this->assertGreaterThan(0, (float) $metrics['electricity_cost']);
        $this->assertEqualsWithDelta(
            (float) $estimate->estimated_electricity_cost,
            (float) $metrics['electricity_cost'],
            0.01,
        );
    }

    /**
     * @return array{0: ProductionJobCard, 1: PrintQuotationEstimate}
     */
    protected function fixtures(): array
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();

        $quotation = Quotation::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'status' => QuotationStatus::Draft,
            'total_amount' => 10000,
        ]);

        $jobCard = ProductionJobCard::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'quotation_id' => $quotation->id,
        ]);

        $analysis = PrintArtworkAnalysis::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'quotation_id' => $quotation->id,
            'production_job_card_id' => $jobCard->id,
            'original_filename' => 'elec-profit.png',
            'stored_filename' => 'elec-profit.png',
            'file_path' => 'printing-intelligence/artwork/elec-profit.png',
            'disk' => 'local',
            'mime_type' => 'image/png',
            'file_extension' => 'png',
            'file_size_bytes' => 100,
            'file_hash' => hash('sha256', 'elec-profit'),
            'analysis_status' => \App\Enums\ArtworkAnalysisStatus::Completed,
            'analysis_source' => 'upload',
        ]);

        $estimate = PrintQuotationEstimate::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'quotation_id' => $quotation->id,
            'print_artwork_analysis_id' => $analysis->id,
            'estimation_status' => QuotationEstimationStatus::Completed,
            'quantity' => 1,
            'version' => 1,
            'estimated_material_cost' => 100,
            'estimated_ink_cost' => 50,
            'estimated_machine_cost' => 200,
            'estimated_labour_cost' => 100,
            'estimated_electricity_cost' => 65,
            'estimated_overhead_cost' => 50,
            'estimated_total_cost' => 565,
            'recommended_selling_price' => 800,
            'formula_version' => 'PI5-V1',
        ]);

        return [$jobCard, $estimate];
    }
}
