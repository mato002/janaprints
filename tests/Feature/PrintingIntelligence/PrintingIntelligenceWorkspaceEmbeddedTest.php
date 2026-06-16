<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PrintingIntelligenceWorkspaceEmbeddedTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_overview_section_renders_embedded_content_frame_src(): void
    {
        [$company, $branch, $user] = $this->userWith(['printing.intelligence.view']);

        $this->actingAs($user)
            ->withSession(['active_company_id' => $company->id, 'active_branch_id' => $branch->id])
            ->get(route('admin.workspaces.printing-intelligence.section', ['section' => 'overview']))
            ->assertOk()
            ->assertSee('compact-workspace-header', false)
            ->assertSee('workspace-pill-tabs', false)
            ->assertSee(route('admin.printing-intelligence.overview', ['embedded' => '1']), false);
    }

    public function test_overview_full_page_redirects_to_workspace_desk(): void
    {
        [$company, $branch, $user] = $this->userWith(['printing.intelligence.view']);

        $this->actingAs($user)
            ->withSession(['active_company_id' => $company->id, 'active_branch_id' => $branch->id])
            ->get(route('admin.printing-intelligence.overview'))
            ->assertRedirect();
    }

    public function test_overview_embedded_response_includes_workspace_frame_for_turbo_frame_request(): void
    {
        [$company, $branch, $user] = $this->userWith(['printing.intelligence.view']);

        $this->actingAs($user)
            ->withSession(['active_company_id' => $company->id, 'active_branch_id' => $branch->id])
            ->withHeaders(['Turbo-Frame' => 'module-workspace-content'])
            ->get(route('admin.printing-intelligence.overview', ['embedded' => '1']))
            ->assertOk()
            ->assertSee('id="module-workspace-content"', false)
            ->assertSee(__('Printing Intelligence'));
    }

    public function test_material_intelligence_desk_url_resolves_to_analysis_tab(): void
    {
        $url = app(\App\Support\Navigation\ModuleShellPresenter::class)
            ->deskUrlForFeatureRoute('admin.printing-intelligence.material');

        $this->assertNotNull($url);
        $this->assertStringContainsString('/admin/workspaces/printing-intelligence/analysis', $url);
        $this->assertStringContainsString('tab=material-intelligence', $url);
    }

    public function test_material_intelligence_navigation_resolves_analysis_parent(): void
    {
        $context = app(\App\Support\Navigation\WorkspaceNavigationResolver::class)
            ->resolve('admin.printing-intelligence.material', __('Material Intelligence'));

        $this->assertNotNull($context);
        $this->assertSame('Analysis', $context['parent_workspace_title']);
        $this->assertStringContainsString('/admin/workspaces/printing-intelligence/analysis', $context['parent_url'] ?? '');
    }

    public function test_legacy_nav_hidden_in_embedded_workspace_context(): void
    {
        [$company, $branch, $user] = $this->userWith(['printing.intelligence.view']);

        $this->actingAs($user)
            ->withSession(['active_company_id' => $company->id, 'active_branch_id' => $branch->id])
            ->withHeaders(['Turbo-Frame' => 'module-workspace-content'])
            ->get(route('admin.printing-intelligence.overview', ['embedded' => '1']))
            ->assertOk()
            ->assertDontSee(__('Cost Accuracy Governance'), false);
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
