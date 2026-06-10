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
use App\Services\PrintingIntelligence\QuotationPricingService;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuotationCostBreakdownIntegrityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_composed_and_priced_totals_include_electricity_exactly_once(): void
    {
        $analysis = $this->analysisWithEstimates();
        $composed = app(QuotationCostComposerService::class)->compose($analysis, [
            'quantity' => 2,
            'material_unit_cost_override' => 100,
            'material_quantity_override' => 1,
        ]);

        $this->assertEqualsWithDelta(600, $composed['machine_cost'], 0.01);
        $this->assertEqualsWithDelta(80, $composed['electricity_cost'], 0.01);

        $priced = app(QuotationPricingService::class)->price([
            'material_cost' => $composed['material_cost'],
            'ink_cost' => $composed['ink_cost'],
            'machine_cost' => $composed['machine_cost'],
            'labour_cost' => $composed['labour_cost'],
            'electricity_cost' => $composed['electricity_cost'],
            'overhead_cost' => $composed['overhead_cost'],
            'wastage_cost' => $composed['wastage_cost'],
        ], 20, 35);

        $componentSum = (float) $composed['material_cost']
            + (float) $composed['ink_cost']
            + (float) $composed['machine_cost']
            + (float) $composed['labour_cost']
            + (float) $composed['electricity_cost']
            + (float) $composed['overhead_cost']
            + (float) $composed['wastage_cost'];

        $this->assertEqualsWithDelta($componentSum, $priced['estimated_total_cost'], 0.01);
    }

    protected function analysisWithEstimates(): PrintArtworkAnalysis
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();

        $analysis = PrintArtworkAnalysis::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'original_filename' => 'breakdown.png',
            'stored_filename' => 'breakdown.png',
            'file_path' => 'printing-intelligence/artwork/breakdown.png',
            'disk' => 'local',
            'mime_type' => 'image/png',
            'file_extension' => 'png',
            'file_size_bytes' => 512,
            'file_hash' => hash('sha256', 'breakdown-integrity'),
            'analysis_status' => ArtworkAnalysisStatus::Completed,
            'analysis_source' => 'upload',
            'colour_analysis_status' => ColourAnalysisStatus::Completed,
            'page_count' => 1,
            'area_square_m' => 0.05,
        ]);

        PrintArtworkInkEstimate::query()->create([
            'company_id' => $company->id,
            'print_artwork_analysis_id' => $analysis->id,
            'estimation_status' => InkEstimationStatus::Completed,
            'estimated_ink_cost' => 50,
            'estimated_total_ml' => 10,
            'formula_version' => 'PI3-V1',
            'estimated_at' => now(),
        ]);

        PrintArtworkProductionEstimate::query()->create([
            'company_id' => $company->id,
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
