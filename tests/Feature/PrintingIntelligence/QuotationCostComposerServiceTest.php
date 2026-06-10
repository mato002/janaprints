<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Enums\ArtworkAnalysisStatus;
use App\Enums\ColourAnalysisStatus;
use App\Enums\InkEstimationStatus;
use App\Enums\ProductionEstimationStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\PrintingIntelligence\PrintArtworkAnalysis;
use App\Models\PrintingIntelligence\PrintArtworkInkEstimate;
use App\Models\PrintingIntelligence\PrintArtworkProductionEstimate;
use App\Services\PrintingIntelligence\QuotationCostComposerService;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuotationCostComposerServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_composes_material_ink_and_machine_costs(): void
    {
        $analysis = $this->analysisWithEstimates();

        $result = app(QuotationCostComposerService::class)->compose($analysis, [
            'quantity' => 1,
            'material_unit_cost_override' => 100,
            'material_quantity_override' => 2,
            'wastage_percent' => 5,
        ]);

        $this->assertEqualsWithDelta(200, $result['material_cost'], 0.01);
        $this->assertEqualsWithDelta(50, $result['ink_cost'], 0.01);
        $this->assertEqualsWithDelta(300, $result['machine_cost'], 0.01);
        $this->assertEqualsWithDelta(12.5, $result['wastage_cost'], 0.01);
        $this->assertSame('completed', $result['status']);
    }

    public function test_warns_when_ink_estimate_missing(): void
    {
        $analysis = $this->baseAnalysis();

        $result = app(QuotationCostComposerService::class)->compose($analysis, [
            'material_unit_cost_override' => 10,
            'material_quantity_override' => 1,
        ]);

        $this->assertContains(__('Ink estimate missing; ink cost unavailable.'), $result['warnings']);
        $this->assertSame('manual_review', $result['status']);
    }

    public function test_warns_when_machine_estimate_missing(): void
    {
        $analysis = $this->baseAnalysis();
        PrintArtworkInkEstimate::query()->create([
            'company_id' => $analysis->company_id,
            'print_artwork_analysis_id' => $analysis->id,
            'estimation_status' => InkEstimationStatus::Completed,
            'estimated_ink_cost' => 25,
            'estimated_total_ml' => 5,
            'formula_version' => 'PI3-V1',
            'estimated_at' => now(),
        ]);

        $result = app(QuotationCostComposerService::class)->compose($analysis, [
            'material_unit_cost_override' => 10,
            'material_quantity_override' => 1,
        ]);

        $this->assertContains(__('Machine/production estimate missing; process costs unavailable.'), $result['warnings']);
    }

    public function test_warns_when_material_missing(): void
    {
        $analysis = $this->analysisWithEstimates();

        $result = app(QuotationCostComposerService::class)->compose($analysis, ['quantity' => 1]);

        $this->assertContains(__('Material not selected; material cost unavailable.'), $result['warnings']);
    }

    protected function baseAnalysis(): PrintArtworkAnalysis
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();

        return PrintArtworkAnalysis::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'original_filename' => 'quote-compose.png',
            'stored_filename' => 'quote-compose.png',
            'file_path' => 'printing-intelligence/artwork/quote-compose.png',
            'disk' => 'local',
            'mime_type' => 'image/png',
            'file_extension' => 'png',
            'file_size_bytes' => 512,
            'file_hash' => hash('sha256', 'quote-compose'),
            'analysis_status' => ArtworkAnalysisStatus::Completed,
            'analysis_source' => 'upload',
            'colour_analysis_status' => ColourAnalysisStatus::Completed,
            'page_count' => 1,
            'area_square_m' => 0.05,
        ]);
    }

    protected function analysisWithEstimates(): PrintArtworkAnalysis
    {
        $analysis = $this->baseAnalysis();

        PrintArtworkInkEstimate::query()->create([
            'company_id' => $analysis->company_id,
            'print_artwork_analysis_id' => $analysis->id,
            'estimation_status' => InkEstimationStatus::Completed,
            'estimated_ink_cost' => 50,
            'estimated_total_ml' => 10,
            'formula_version' => 'PI3-V1',
            'estimated_at' => now(),
        ]);

        PrintArtworkProductionEstimate::query()->create([
            'company_id' => $analysis->company_id,
            'print_artwork_analysis_id' => $analysis->id,
            'estimation_status' => ProductionEstimationStatus::Completed,
            'quantity' => 1,
            'estimated_machine_cost' => 300,
            'estimated_labour_cost' => 150,
            'estimated_electricity_cost' => 40,
            'estimated_overhead_cost' => 45,
            'estimated_total_production_cost' => 535,
            'formula_version' => 'PI4-V1',
            'estimated_at' => now(),
        ]);

        return $analysis->fresh(['inkEstimates', 'productionEstimate']);
    }
}
