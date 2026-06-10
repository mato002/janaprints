<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Enums\ArtworkAnalysisStatus;
use App\Enums\ColourAnalysisStatus;
use App\Enums\InkEstimationStatus;
use App\Enums\ProductionEstimationStatus;
use App\Enums\QuotationEstimationStatus;
use App\Enums\QuotationStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\PrintingIntelligence\PrintArtworkAnalysis;
use App\Models\PrintingIntelligence\PrintArtworkInkEstimate;
use App\Models\PrintingIntelligence\PrintArtworkProductionEstimate;
use App\Models\Sales\Quotation;
use App\Models\User;
use App\Services\PrintingIntelligence\ApplyEstimateToQuotationService;
use App\Services\PrintingIntelligence\QuotationEstimationService;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EstimateVersionIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        config([
            'printing_intelligence.quotation_estimation_enabled' => true,
            'printing_intelligence.allow_apply_to_quotation' => true,
        ]);
    }

    public function test_re_estimate_after_apply_creates_new_version_without_overwriting_applied_costs(): void
    {
        [$analysis, $quotation, $user] = $this->fixtures();
        $service = app(QuotationEstimationService::class);

        $v1 = $service->estimate($analysis, [
            'quantity' => 1,
            'material_unit_cost_override' => 80,
            'material_quantity_override' => 1,
            'quotation_id' => $quotation->id,
        ]);

        $this->assertSame(1, (int) $v1->version);
        $v1Total = (float) $v1->estimated_total_cost;
        $v1Material = (float) $v1->estimated_material_cost;

        app(ApplyEstimateToQuotationService::class)->apply($v1, $quotation, $user);

        $v1->refresh();
        $this->assertNotNull($v1->applied_at);
        $this->assertSame(QuotationEstimationStatus::AppliedToQuotation, $v1->estimation_status);

        $v2 = $service->estimate($analysis, [
            'quantity' => 1,
            'material_unit_cost_override' => 200,
            'material_quantity_override' => 1,
            'quotation_id' => $quotation->id,
        ]);

        $this->assertNotSame($v1->id, $v2->id);
        $this->assertSame(2, (int) $v2->version);
        $this->assertGreaterThan($v1Total, (float) $v2->estimated_total_cost);

        $v1->refresh();
        $this->assertEqualsWithDelta($v1Total, (float) $v1->estimated_total_cost, 0.01);
        $this->assertEqualsWithDelta($v1Material, (float) $v1->estimated_material_cost, 0.01);
        $this->assertDatabaseCount('print_quotation_estimates', 2);
    }

    /**
     * @return array{0: PrintArtworkAnalysis, 1: Quotation, 2: User}
     */
    protected function fixtures(): array
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
        ]);

        $quotation = Quotation::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'status' => QuotationStatus::Draft,
            'prepared_by' => $user->id,
        ]);

        $analysis = PrintArtworkAnalysis::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'quotation_id' => $quotation->id,
            'original_filename' => 'version-isolation.png',
            'stored_filename' => 'version-isolation.png',
            'file_path' => 'printing-intelligence/artwork/version-isolation.png',
            'disk' => 'local',
            'mime_type' => 'image/png',
            'file_extension' => 'png',
            'file_size_bytes' => 512,
            'file_hash' => hash('sha256', 'version-isolation'),
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

        return [$analysis->fresh(['inkEstimates', 'productionEstimate']), $quotation, $user];
    }
}
