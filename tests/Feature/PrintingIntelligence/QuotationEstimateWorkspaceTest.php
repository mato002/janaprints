<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Enums\ArtworkAnalysisStatus;
use App\Enums\ColourAnalysisStatus;
use App\Enums\InkEstimationStatus;
use App\Enums\ProductionEstimationStatus;
use App\Enums\QuotationEstimationStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\PrintingIntelligence\PrintArtworkAnalysis;
use App\Models\PrintingIntelligence\PrintArtworkInkEstimate;
use App\Models\PrintingIntelligence\PrintArtworkProductionEstimate;
use App\Models\PrintingIntelligence\PrintQuotationEstimate;
use App\Models\User;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class QuotationEstimateWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        config(['printing_intelligence.quotation_estimation_enabled' => true]);
    }

    public function test_generate_estimate_action_works(): void
    {
        [$company, $branch, $user, $analysis] = $this->fixtures();

        $this->actingAs($user)
            ->withSession(['active_company_id' => $company->id, 'active_branch_id' => $branch->id])
            ->post(route('admin.printing-intelligence.artwork-analysis.estimate-quotation', $analysis), [
                'quantity' => 1,
                'material_unit_cost_override' => 120,
                'material_quantity_override' => 1,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('print_quotation_estimates', [
            'print_artwork_analysis_id' => $analysis->id,
        ]);
    }

    public function test_shows_breakdown_on_detail(): void
    {
        [$company, $branch, $user, $analysis] = $this->fixtures();

        PrintQuotationEstimate::query()->create([
            'company_id' => $company->id,
            'print_artwork_analysis_id' => $analysis->id,
            'estimation_status' => QuotationEstimationStatus::Completed,
            'quantity' => 1,
            'estimated_total_cost' => 900,
            'recommended_selling_price' => 1200,
            'confidence_score' => 75,
            'formula_version' => 'PI5-V1',
        ]);

        $this->actingAs($user)
            ->withSession(['active_company_id' => $company->id, 'active_branch_id' => $branch->id])
            ->get(route('admin.printing-intelligence.artwork-analysis.show', $analysis))
            ->assertOk()
            ->assertSee(__('Quotation recommendation'))
            ->assertSee(__('Recommended price'));
    }

    public function test_apply_requires_permission(): void
    {
        [$company, $branch, $user, $analysis] = $this->fixtures(['printing.intelligence.view', 'printing.quotation.estimate']);

        $estimate = PrintQuotationEstimate::query()->create([
            'company_id' => $company->id,
            'print_artwork_analysis_id' => $analysis->id,
            'estimation_status' => QuotationEstimationStatus::Completed,
            'quantity' => 1,
            'estimated_total_cost' => 500,
            'recommended_selling_price' => 700,
            'formula_version' => 'PI5-V1',
        ]);

        $this->actingAs($user)
            ->withSession(['active_company_id' => $company->id, 'active_branch_id' => $branch->id])
            ->post(route('admin.printing-intelligence.artwork-analysis.apply-quotation-estimate', [$analysis, $estimate]), [
                'confirm_apply' => 1,
            ])
            ->assertForbidden();
    }

    /**
     * @param  list<string>|null  $permissions
     * @return array{0: Company, 1: Branch, 2: User, 3: PrintArtworkAnalysis}
     */
    protected function fixtures(?array $permissions = null): array
    {
        $permissions ??= ['printing.intelligence.view', 'printing.quotation.estimate', 'printing.quotation.apply-estimate'];
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);
        Role::findByName('Storekeeper', 'web')->syncPermissions($permissions);
        $user->assignRole('Storekeeper');

        $analysis = PrintArtworkAnalysis::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'original_filename' => 'quote-ui.png',
            'stored_filename' => 'quote-ui.png',
            'file_path' => 'printing-intelligence/artwork/quote-ui.png',
            'disk' => 'local',
            'mime_type' => 'image/png',
            'file_extension' => 'png',
            'file_size_bytes' => 256,
            'file_hash' => hash('sha256', 'quote-ui'),
            'analysis_status' => ArtworkAnalysisStatus::Completed,
            'analysis_source' => 'upload',
            'colour_analysis_status' => ColourAnalysisStatus::Completed,
            'page_count' => 1,
            'area_square_m' => 0.04,
        ]);

        PrintArtworkInkEstimate::query()->create([
            'company_id' => $company->id,
            'print_artwork_analysis_id' => $analysis->id,
            'estimation_status' => InkEstimationStatus::Completed,
            'estimated_ink_cost' => 40,
            'estimated_total_ml' => 8,
            'formula_version' => 'PI3-V1',
            'estimated_at' => now(),
        ]);

        PrintArtworkProductionEstimate::query()->create([
            'company_id' => $company->id,
            'print_artwork_analysis_id' => $analysis->id,
            'estimation_status' => ProductionEstimationStatus::Completed,
            'quantity' => 1,
            'estimated_machine_cost' => 200,
            'estimated_labour_cost' => 100,
            'estimated_electricity_cost' => 20,
            'estimated_overhead_cost' => 30,
            'estimated_total_production_cost' => 350,
            'formula_version' => 'PI4-V1',
            'estimated_at' => now(),
        ]);

        return [$company, $branch, $user, $analysis];
    }
}
