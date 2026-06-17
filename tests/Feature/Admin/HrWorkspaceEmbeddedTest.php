<?php

namespace Tests\Feature\Admin;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class HrWorkspaceEmbeddedTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_hr_section_renders_embedded_content_frame_src(): void
    {
        $user = $this->hrUser();

        $this->actingAs($user)
            ->get(route('admin.workspaces.hr.section', ['section' => 'people']))
            ->assertOk()
            ->assertSee(route('admin.hr.dashboard', ['embedded' => '1']), false);
    }

    public function test_hr_dashboard_full_page_embedded_query_redirects_to_people_workspace(): void
    {
        $user = $this->hrUser();

        $this->actingAs($user)
            ->get(route('admin.hr.dashboard', ['embedded' => '1']))
            ->assertRedirect();
    }

    public function test_hr_dashboard_full_page_without_embedded_redirects_to_people_workspace(): void
    {
        $user = $this->hrUser();

        $this->actingAs($user)
            ->get(route('admin.hr.dashboard'))
            ->assertRedirect();
    }

    public function test_hr_dashboard_embedded_response_includes_workspace_frame_for_turbo_frame_request(): void
    {
        $user = $this->hrUser();

        $this->actingAs($user)
            ->withHeaders(['Turbo-Frame' => 'module-workspace-content'])
            ->get(route('admin.hr.dashboard', ['embedded' => '1']))
            ->assertOk()
            ->assertSee('id="module-workspace-content"', false)
            ->assertSee(__('HR Command Center'));
    }

    public function test_employees_tab_full_page_embedded_query_redirects_to_people_workspace(): void
    {
        $user = $this->hrUser();

        $this->actingAs($user)
            ->get(route('admin.employees.index', ['embedded' => '1']))
            ->assertRedirect()
            ->assertRedirectContains('/admin/workspaces/hr/people')
            ->assertRedirectContains('tab=employees');
    }

    public function test_employees_desk_url_resolves_to_people_workspace(): void
    {
        $url = app(\App\Support\Navigation\ModuleShellPresenter::class)
            ->deskUrlForFeatureRoute('admin.employees.index');

        $this->assertNotNull($url);
        $this->assertStringContainsString('/admin/workspaces/hr/people', $url);
        $this->assertStringContainsString('tab=employees', $url);
    }

    public function test_employees_tab_embedded_response_includes_workspace_frame_for_turbo_frame_request(): void
    {
        $user = $this->hrUser();

        $this->actingAs($user)
            ->withHeaders(['Turbo-Frame' => 'module-workspace-content'])
            ->get(route('admin.employees.index', ['embedded' => '1']))
            ->assertOk()
            ->assertSee('id="module-workspace-content"', false)
            ->assertSee(__('Employees'));
    }

    public function test_performance_reviews_redirects_to_development_workspace(): void
    {
        $user = $this->hrUser();

        $this->actingAs($user)
            ->get(route('admin.hr.performance.index'))
            ->assertRedirect();
    }

    public function test_performance_reviews_desk_url_resolves_to_development_tab(): void
    {
        $url = app(\App\Support\Navigation\ModuleShellPresenter::class)
            ->deskUrlForFeatureRoute('admin.hr.performance.index');

        $this->assertNotNull($url);
        $this->assertStringContainsString('/admin/workspaces/hr/development', $url);
        $this->assertStringContainsString('tab=performance', $url);
    }

    public function test_performance_reviews_navigation_resolves_development_parent(): void
    {
        $context = app(\App\Support\Navigation\WorkspaceNavigationResolver::class)
            ->resolve('admin.hr.performance.index', __('Performance Reviews'));

        $this->assertNotNull($context);
        $this->assertSame('Development', $context['parent_workspace_title']);
        $this->assertStringContainsString('/admin/workspaces/hr/development', $context['parent_url'] ?? '');
    }

    public function test_performance_reviews_embedded_in_workspace_frame(): void
    {
        $user = $this->hrUser();

        $this->actingAs($user)
            ->withHeaders(['Turbo-Frame' => 'module-workspace-content'])
            ->get(route('admin.hr.performance.index', ['embedded' => '1']))
            ->assertOk()
            ->assertSee('id="module-workspace-content"', false)
            ->assertSee(__('Performance Reviews'));
    }

    protected function hrUser(): User
    {
        $user = User::factory()->create([
            'company_id' => Company::query()->where('code', 'JANA')->value('id'),
            'default_branch_id' => Branch::query()->where('code', 'HQ')->value('id'),
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $user->assignRole(Role::findByName('HR', 'web'));

        return $user;
    }
}
