<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Enums\QuotationEstimationStatus;
use App\Enums\QuotationStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\PrintingIntelligence\PrintQuotationEstimate;
use App\Models\Sales\Quotation;
use App\Models\User;
use App\Services\PrintingIntelligence\ApplyEstimateToQuotationService;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplyEstimateToQuotationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        config(['printing_intelligence.allow_apply_to_quotation' => true]);
    }

    public function test_updates_only_advisory_quotation_fields(): void
    {
        [$quotation, $estimate, $user] = $this->fixtures();

        app(ApplyEstimateToQuotationService::class)->apply($estimate, $quotation, $user);

        $quotation->refresh();

        $this->assertEqualsWithDelta(500, (float) $quotation->estimated_total_cost, 0.01);
        $this->assertEqualsWithDelta(750, (float) $quotation->recommended_price, 0.01);
        $this->assertSame('PI5-V1', $quotation->estimation_version);
        $this->assertEqualsWithDelta(1160, (float) $quotation->total_amount, 0.01);
        $this->assertEqualsWithDelta(1000, (float) $quotation->subtotal, 0.01);
    }

    public function test_marks_estimate_applied(): void
    {
        [$quotation, $estimate, $user] = $this->fixtures();

        app(ApplyEstimateToQuotationService::class)->apply($estimate, $quotation, $user);

        $estimate->refresh();
        $this->assertSame(QuotationEstimationStatus::AppliedToQuotation, $estimate->estimation_status);
        $this->assertNotNull($estimate->applied_at);
        $this->assertSame($user->id, $estimate->applied_by);
    }

    /**
     * @return array{0: Quotation, 1: PrintQuotationEstimate, 2: User}
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
            'subtotal' => 1000,
            'tax_amount' => 160,
            'total_amount' => 1160,
            'status' => QuotationStatus::Draft,
            'prepared_by' => $user->id,
        ]);

        $analysis = \App\Models\PrintingIntelligence\PrintArtworkAnalysis::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'quotation_id' => $quotation->id,
            'original_filename' => 'apply.png',
            'stored_filename' => 'apply.png',
            'file_path' => 'printing-intelligence/artwork/apply.png',
            'disk' => 'local',
            'mime_type' => 'image/png',
            'file_extension' => 'png',
            'file_size_bytes' => 100,
            'file_hash' => hash('sha256', 'apply'),
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
            'estimated_material_cost' => 100,
            'estimated_ink_cost' => 50,
            'estimated_machine_cost' => 200,
            'estimated_labour_cost' => 100,
            'estimated_overhead_cost' => 50,
            'estimated_total_cost' => 500,
            'recommended_selling_price' => 750,
            'expected_margin_percent' => 33.33,
            'confidence_score' => 80,
            'formula_version' => 'PI5-V1',
        ]);

        return [$quotation, $estimate, $user];
    }
}
