<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Enums\QuotationEstimationStatus;
use App\Enums\QuotationStatus;
use App\Exceptions\PrintingIntelligence\AppliedEstimateImmutableException;
use App\Models\Branch;
use App\Models\Company;
use App\Models\PrintingIntelligence\PrintArtworkAnalysis;
use App\Models\PrintingIntelligence\PrintQuotationEstimate;
use App\Models\Sales\Quotation;
use App\Models\User;
use App\Services\PrintingIntelligence\ApplyEstimateToQuotationService;
use App\Services\PrintingIntelligence\QuotationEstimateLifecycleService;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppliedEstimateImmutableTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        config(['printing_intelligence.allow_apply_to_quotation' => true]);
    }

    public function test_applied_estimate_update_throws_immutable_exception(): void
    {
        [$estimate, $quotation, $user] = $this->fixtures();

        app(ApplyEstimateToQuotationService::class)->apply($estimate, $quotation, $user);
        $estimate->refresh();

        $this->expectException(AppliedEstimateImmutableException::class);

        $estimate->update(['estimated_total_cost' => 999]);
    }

    public function test_lifecycle_service_blocks_mutation_of_applied_estimate(): void
    {
        [$estimate, $quotation, $user] = $this->fixtures();

        app(ApplyEstimateToQuotationService::class)->apply($estimate, $quotation, $user);

        $this->expectException(AppliedEstimateImmutableException::class);

        app(QuotationEstimateLifecycleService::class)->assertMutable($estimate->fresh());
    }

    /**
     * @return array{0: PrintQuotationEstimate, 1: Quotation, 2: User}
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
            'original_filename' => 'immutable.png',
            'stored_filename' => 'immutable.png',
            'file_path' => 'printing-intelligence/artwork/immutable.png',
            'disk' => 'local',
            'mime_type' => 'image/png',
            'file_extension' => 'png',
            'file_size_bytes' => 100,
            'file_hash' => hash('sha256', 'immutable-estimate'),
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
            'estimated_electricity_cost' => 40,
            'estimated_overhead_cost' => 50,
            'estimated_total_cost' => 540,
            'recommended_selling_price' => 750,
            'expected_margin_percent' => 33.33,
            'confidence_score' => 80,
            'formula_version' => 'PI5-V1',
        ]);

        return [$estimate, $quotation, $user];
    }
}
