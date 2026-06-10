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

class ArtworkAnalysisWorkspaceTest extends TestCase
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

    public function test_page_loads(): void
    {
        [$company, $branch, $user] = $this->userWith(['printing.intelligence.view']);

        $this->actingAs($user)
            ->withSession(['active_company_id' => $company->id, 'active_branch_id' => $branch->id])
            ->get(route('admin.printing-intelligence.artwork-analysis.index'))
            ->assertOk()
            ->assertSee(__('Artwork Analysis'));
    }

    public function test_upload_works_with_analyze_permission(): void
    {
        [$company, $branch, $user] = $this->userWith(['printing.intelligence.view', 'printing.artwork.analyze']);

        $response = $this->actingAs($user)
            ->withSession(['active_company_id' => $company->id, 'active_branch_id' => $branch->id])
            ->post(route('admin.printing-intelligence.artwork-analysis.upload'), [
                'file' => UploadedFile::fake()->image('upload-test.png', 300, 200),
            ]);

        $response->assertRedirect();
        $this->assertDatabaseCount('print_artwork_analyses', 1);
    }

    public function test_recent_analysis_appears_on_index(): void
    {
        [$company, $branch, $user] = $this->userWith(['printing.intelligence.view', 'printing.artwork.analyze']);

        $this->actingAs($user)
            ->withSession(['active_company_id' => $company->id, 'active_branch_id' => $branch->id])
            ->post(route('admin.printing-intelligence.artwork-analysis.upload'), [
                'file' => UploadedFile::fake()->image('listed.png', 200, 200),
            ]);

        $this->actingAs($user)
            ->withSession(['active_company_id' => $company->id, 'active_branch_id' => $branch->id])
            ->get(route('admin.printing-intelligence.artwork-analysis.index'))
            ->assertOk()
            ->assertSee('listed.png');
    }

    public function test_detail_page_loads(): void
    {
        [$company, $branch, $user] = $this->userWith(['printing.intelligence.view', 'printing.artwork.analyze']);

        $this->actingAs($user)
            ->withSession(['active_company_id' => $company->id, 'active_branch_id' => $branch->id])
            ->post(route('admin.printing-intelligence.artwork-analysis.upload'), [
                'file' => UploadedFile::fake()->image('detail.png', 250, 250),
            ]);

        $analysisId = (int) \App\Models\PrintingIntelligence\PrintArtworkAnalysis::query()->value('id');

        $this->actingAs($user)
            ->withSession(['active_company_id' => $company->id, 'active_branch_id' => $branch->id])
            ->get(route('admin.printing-intelligence.artwork-analysis.show', $analysisId))
            ->assertOk()
            ->assertSee('detail.png');
    }

    public function test_permissions_enforced_on_upload(): void
    {
        [$company, $branch, $user] = $this->userWith(['printing.intelligence.view']);

        $this->actingAs($user)
            ->withSession(['active_company_id' => $company->id, 'active_branch_id' => $branch->id])
            ->post(route('admin.printing-intelligence.artwork-analysis.upload'), [
                'file' => UploadedFile::fake()->image('denied.png', 100, 100),
            ])
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
