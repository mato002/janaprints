<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Enums\ArtworkAnalysisStatus;
use App\Enums\ColourAnalysisStatus;
use App\Enums\InkEstimationStatus;
use App\Enums\PrintInkType;
use App\Models\Branch;
use App\Models\Company;
use App\Models\PrintingIntelligence\PrintArtworkAnalysis;
use App\Models\PrintingIntelligence\PrintArtworkInkEstimate;
use App\Models\PrintingIntelligence\PrintInkProfile;
use App\Models\User;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ArtworkInkEstimateWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        config(['printing_intelligence.ink_costing_enabled' => true]);
    }

    public function test_estimate_section_visible_with_confidence_and_warnings(): void
    {
        [$company, $branch, $user, $analysis] = $this->setupWithEstimate();

        $this->actingAs($user)
            ->withSession(['active_company_id' => $company->id, 'active_branch_id' => $branch->id])
            ->get(route('admin.printing-intelligence.artwork-analysis.show', $analysis))
            ->assertOk()
            ->assertSee(__('Ink estimate'))
            ->assertSee(__('Confidence'))
            ->assertSee(__('CMYK ink (ml)'))
            ->assertSee(__('Ink estimation warnings'));
    }

    public function test_run_ink_estimation_action_works(): void
    {
        [$company, $branch, $user, $analysis] = $this->setupAnalysisOnly();

        $this->actingAs($user)
            ->withSession(['active_company_id' => $company->id, 'active_branch_id' => $branch->id])
            ->post(route('admin.printing-intelligence.artwork-analysis.estimate-ink', $analysis))
            ->assertRedirect();

        $this->assertNotNull(
            PrintArtworkInkEstimate::query()->where('print_artwork_analysis_id', $analysis->id)->value('estimated_at'),
        );
    }

    public function test_permissions_enforced_on_ink_estimation(): void
    {
        [$company, $branch, $user, $analysis] = $this->setupAnalysisOnly([
            'printing.intelligence.view',
            'printing.artwork.analyze',
        ]);

        $this->actingAs($user)
            ->withSession(['active_company_id' => $company->id, 'active_branch_id' => $branch->id])
            ->post(route('admin.printing-intelligence.artwork-analysis.estimate-ink', $analysis))
            ->assertForbidden();
    }

    /**
     * @param  list<string>  $permissions
     * @return array{0: Company, 1: Branch, 2: User, 3: PrintArtworkAnalysis}
     */
    protected function setupAnalysisOnly(?array $permissions = null): array
    {
        $permissions ??= [
            'printing.intelligence.view',
            'printing.artwork.analyze',
            'printing.artwork.colour-analyze',
            'printing.artwork.estimate-ink',
        ];

        [$company, $branch, $user] = $this->userWith($permissions);

        PrintInkProfile::query()->create([
            'company_id' => $company->id,
            'name' => 'UI Ink',
            'ink_type' => PrintInkType::Cmyk,
            'cartridge_cost' => 3000,
            'estimated_ml' => 800,
            'estimated_yield_sq_m' => 40,
            'active' => true,
        ]);

        $analysis = PrintArtworkAnalysis::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'original_filename' => 'ui-ink.png',
            'stored_filename' => 'ui-ink.png',
            'file_path' => 'printing-intelligence/artwork/ui-ink.png',
            'disk' => 'local',
            'mime_type' => 'image/png',
            'file_extension' => 'png',
            'file_size_bytes' => 256,
            'file_hash' => hash('sha256', 'ui-ink'),
            'analysis_status' => ArtworkAnalysisStatus::Completed,
            'analysis_source' => 'upload',
            'colour_analysis_status' => ColourAnalysisStatus::Completed,
            'page_count' => 1,
            'area_square_m' => 0.04,
            'resolution_dpi' => 300,
            'rgb_coverage_percent' => 55,
            'cyan_coverage_percent' => 14,
            'magenta_coverage_percent' => 14,
            'yellow_coverage_percent' => 14,
            'black_coverage_percent' => 13,
        ]);

        return [$company, $branch, $user, $analysis];
    }

    /**
     * @return array{0: Company, 1: Branch, 2: User, 3: PrintArtworkAnalysis}
     */
    protected function setupWithEstimate(): array
    {
        [$company, $branch, $user, $analysis] = $this->setupAnalysisOnly();
        $profile = PrintInkProfile::query()->where('company_id', $company->id)->firstOrFail();

        PrintArtworkInkEstimate::query()->create([
            'company_id' => $company->id,
            'print_artwork_analysis_id' => $analysis->id,
            'ink_profile_id' => $profile->id,
            'estimation_status' => InkEstimationStatus::ManualReview,
            'estimated_total_ml' => 12.5,
            'estimated_cyan_ml' => 3,
            'estimated_magenta_ml' => 3,
            'estimated_yellow_ml' => 3,
            'estimated_black_ml' => 3.5,
            'estimated_ink_cost' => 62.5,
            'confidence_score' => 72,
            'formula_version' => 'PI3-V1',
            'warnings' => ['test_warning'],
            'estimated_at' => now(),
        ]);

        return [$company, $branch, $user, $analysis];
    }

    /**
     * @param  list<string>  $permissions
     * @return array{0: Company, 1: Branch, 2: User}
     */
    protected function userWith(array $permissions): array
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        Role::findByName('Storekeeper', 'web')->syncPermissions($permissions);
        $user->assignRole('Storekeeper');

        return [$company, $branch, $user];
    }
}
