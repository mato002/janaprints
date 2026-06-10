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

class InkScalingConsistencyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_ink_scales_by_production_quantity_ratio_not_raw_quotation_quantity(): void
    {
        $analysis = $this->analysisWithProductionQty(100);

        $result = app(QuotationCostComposerService::class)->compose($analysis, [
            'quantity' => 200,
            'material_unit_cost_override' => 10,
            'material_quantity_override' => 1,
        ]);

        $this->assertEqualsWithDelta(2.0, $result['breakdown']['production_scale'], 0.0001);
        $this->assertEqualsWithDelta(100, $result['ink_cost'], 0.01);
        $this->assertNotEqualsWithDelta(10000, $result['ink_cost'], 0.01);
    }

    protected function analysisWithProductionQty(int $productionQty): PrintArtworkAnalysis
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();

        $analysis = PrintArtworkAnalysis::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'original_filename' => 'ink-scale.png',
            'stored_filename' => 'ink-scale.png',
            'file_path' => 'printing-intelligence/artwork/ink-scale.png',
            'disk' => 'local',
            'mime_type' => 'image/png',
            'file_extension' => 'png',
            'file_size_bytes' => 512,
            'file_hash' => hash('sha256', 'ink-scale'),
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
            'quantity' => $productionQty,
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
