<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ArtworkColourWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        Storage::fake('local');
        config(['printing_intelligence.storage_disk' => 'local']);
    }

    public function test_index_shows_colour_columns(): void
    {
        [$company, $branch, $user] = $this->userWith(['printing.intelligence.view']);

        $this->actingAs($user)
            ->withSession(['active_company_id' => $company->id, 'active_branch_id' => $branch->id])
            ->get(route('admin.printing-intelligence.artwork-analysis.index'))
            ->assertOk()
            ->assertSee('colour coverage', false);
    }

    public function test_run_colour_analysis_action_works(): void
    {
        [$company, $branch, $user] = $this->userWith([
            'printing.intelligence.view',
            'printing.artwork.analyze',
            'printing.artwork.colour-analyze',
        ]);

        $this->actingAs($user)
            ->withSession(['active_company_id' => $company->id, 'active_branch_id' => $branch->id])
            ->post(route('admin.printing-intelligence.artwork-analysis.upload'), [
                'file' => UploadedFile::fake()->image('colour-ui.png', 60, 60),
            ]);

        $analysisId = (int) \App\Models\PrintingIntelligence\PrintArtworkAnalysis::query()->value('id');

        $this->actingAs($user)
            ->withSession(['active_company_id' => $company->id, 'active_branch_id' => $branch->id])
            ->post(route('admin.printing-intelligence.artwork-analysis.colour-analysis', $analysisId))
            ->assertRedirect();

        $this->assertNotNull(
            \App\Models\PrintingIntelligence\PrintArtworkAnalysis::query()->find($analysisId)?->colour_analyzed_at,
        );
    }

    public function test_detail_shows_cmyk_breakdown(): void
    {
        [$company, $branch, $user] = $this->userWith([
            'printing.intelligence.view',
            'printing.artwork.analyze',
            'printing.artwork.colour-analyze',
        ]);

        $this->actingAs($user)
            ->withSession(['active_company_id' => $company->id, 'active_branch_id' => $branch->id])
            ->post(route('admin.printing-intelligence.artwork-analysis.upload'), [
                'file' => UploadedFile::fake()->image('detail-colour.png', 50, 50),
            ]);

        $analysisId = (int) \App\Models\PrintingIntelligence\PrintArtworkAnalysis::query()->value('id');

        $this->actingAs($user)
            ->withSession(['active_company_id' => $company->id, 'active_branch_id' => $branch->id])
            ->post(route('admin.printing-intelligence.artwork-analysis.colour-analysis', $analysisId));

        $this->actingAs($user)
            ->withSession(['active_company_id' => $company->id, 'active_branch_id' => $branch->id])
            ->get(route('admin.printing-intelligence.artwork-analysis.show', $analysisId))
            ->assertOk()
            ->assertSee(__('CMYK breakdown'));
    }

    public function test_permissions_enforced_on_colour_analysis(): void
    {
        [$company, $branch, $user] = $this->userWith(['printing.intelligence.view', 'printing.artwork.analyze']);

        $this->actingAs($user)
            ->withSession(['active_company_id' => $company->id, 'active_branch_id' => $branch->id])
            ->post(route('admin.printing-intelligence.artwork-analysis.upload'), [
                'file' => UploadedFile::fake()->image('denied-colour.png', 40, 40),
            ]);

        $analysisId = (int) \App\Models\PrintingIntelligence\PrintArtworkAnalysis::query()->value('id');

        $this->actingAs($user)
            ->withSession(['active_company_id' => $company->id, 'active_branch_id' => $branch->id])
            ->post(route('admin.printing-intelligence.artwork-analysis.colour-analysis', $analysisId))
            ->assertForbidden();
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
