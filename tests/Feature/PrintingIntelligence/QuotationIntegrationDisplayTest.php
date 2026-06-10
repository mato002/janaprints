<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Enums\ArtworkAnalysisStatus;
use App\Enums\QuotationEstimationStatus;
use App\Enums\QuotationStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\PrintingIntelligence\PrintArtworkAnalysis;
use App\Models\PrintingIntelligence\PrintQuotationEstimate;
use App\Models\Sales\Quotation;
use App\Models\User;
use App\Services\PrintingIntelligence\ApplyEstimateToQuotationService;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class QuotationIntegrationDisplayTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_quotation_detail_shows_advisory_estimate_without_changing_totals(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);
        Role::findByName('Storekeeper', 'web')->syncPermissions(['quotations.view']);
        $user->assignRole('Storekeeper');

        $quotation = Quotation::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'subtotal' => 2000,
            'tax_amount' => 320,
            'total_amount' => 2320,
            'status' => QuotationStatus::Draft,
            'prepared_by' => $user->id,
        ]);

        $analysis = PrintArtworkAnalysis::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'quotation_id' => $quotation->id,
            'original_filename' => 'q-int.png',
            'stored_filename' => 'q-int.png',
            'file_path' => 'printing-intelligence/artwork/q-int.png',
            'disk' => 'local',
            'mime_type' => 'image/png',
            'file_extension' => 'png',
            'file_size_bytes' => 100,
            'file_hash' => hash('sha256', 'q-int'),
            'analysis_status' => ArtworkAnalysisStatus::Completed,
            'analysis_source' => 'upload',
        ]);

        $estimate = PrintQuotationEstimate::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'quotation_id' => $quotation->id,
            'print_artwork_analysis_id' => $analysis->id,
            'estimation_status' => QuotationEstimationStatus::Completed,
            'quantity' => 1,
            'estimated_material_cost' => 300,
            'estimated_ink_cost' => 100,
            'estimated_machine_cost' => 200,
            'estimated_labour_cost' => 100,
            'estimated_overhead_cost' => 100,
            'estimated_total_cost' => 800,
            'recommended_selling_price' => 1100,
            'confidence_score' => 70,
            'formula_version' => 'PI5-V1',
        ]);

        app(ApplyEstimateToQuotationService::class)->apply($estimate, $quotation, $user);

        $this->actingAs($user)
            ->withSession(['active_company_id' => $company->id, 'active_branch_id' => $branch->id])
            ->get(route('admin.quotations.show', $quotation))
            ->assertOk()
            ->assertSee(__('Printing Intelligence Estimate'))
            ->assertSee('PI5-V1')
            ->assertSee('2,320.00');
    }
}
