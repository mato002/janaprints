<?php

namespace Tests\Feature\Administration;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Support\Discovery\FeatureRegistry;
use App\Support\Navigation\ModuleShellPresenter;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdministrationWorkspaceEmbeddedTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_configuration_section_renders_embedded_system_settings_frame_src(): void
    {
        $user = $this->companyAdmin();

        $this->actingAs($user)
            ->get(route('admin.workspaces.administration.section', [
                'section' => 'configuration',
                'tab' => 'system-settings',
            ]))
            ->assertOk()
            ->assertSee(route('admin.settings.show', ['section' => 'hub', 'embedded' => '1']), false)
            ->assertSee('module-workspace-content', false);
    }

    public function test_configuration_section_renders_embedded_form_controls_frame_src(): void
    {
        $user = $this->companyAdmin();

        $this->actingAs($user)
            ->get(route('admin.workspaces.administration.section', [
                'section' => 'configuration',
                'tab' => 'form-controls',
            ]))
            ->assertOk()
            ->assertSee(route('admin.settings.forms.index', ['embedded' => '1']), false);
    }

    public function test_system_settings_embedded_response_includes_workspace_frame(): void
    {
        $user = $this->companyAdmin();

        $this->actingAs($user)
            ->withHeaders(['Turbo-Frame' => 'module-workspace-content'])
            ->get(route('admin.settings.show', ['section' => 'hub', 'embedded' => '1']))
            ->assertOk()
            ->assertSee('id="module-workspace-content"', false);
    }

    public function test_form_controls_embedded_response_renders_control_center(): void
    {
        $user = $this->companyAdmin();

        $this->actingAs($user)
            ->withHeaders(['Turbo-Frame' => 'module-workspace-content'])
            ->get(route('admin.settings.forms.index', ['embedded' => '1']))
            ->assertOk()
            ->assertSee('id="module-workspace-content"', false)
            ->assertSee(__('Total Forms'));
    }

    public function test_old_settings_forms_url_redirects_to_configuration_workspace(): void
    {
        $user = $this->companyAdmin();

        $this->actingAs($user)
            ->get(route('admin.settings.forms.index'))
            ->assertRedirect(route('admin.workspaces.administration.section', [
                'section' => 'configuration',
                'tab' => 'form-controls',
            ]));
    }

    public function test_old_settings_index_redirects_to_configuration_system_settings_workspace(): void
    {
        $user = $this->companyAdmin();

        $this->actingAs($user)
            ->get(route('admin.settings.index'))
            ->assertRedirect(route('admin.workspaces.administration.section', [
                'section' => 'configuration',
                'tab' => 'system-settings',
            ]));
    }

    public function test_old_settings_hub_url_redirects_to_configuration_system_settings_workspace(): void
    {
        $user = $this->companyAdmin();

        $this->actingAs($user)
            ->get(route('admin.settings.show', ['section' => 'hub']))
            ->assertRedirect(route('admin.workspaces.administration.section', [
                'section' => 'configuration',
                'tab' => 'system-settings',
            ]));
    }

    public function test_workspace_tab_state_survives_refresh(): void
    {
        $user = $this->companyAdmin();

        $url = route('admin.workspaces.administration.section', [
            'section' => 'configuration',
            'tab' => 'form-controls',
        ]);

        $this->actingAs($user)
            ->get($url)
            ->assertOk()
            ->assertSee('tab=form-controls', false)
            ->assertSee(__('Form Controls'));
    }

    public function test_workspace_desk_highlights_tab_when_search_uses_label_slug(): void
    {
        $this->actingAs($this->companyAdmin());

        $desk = app(ModuleShellPresenter::class)->presentDesk('administration', 'configuration', 'document types');

        $this->assertSame('document-types', $desk['active_secondary']['key'] ?? null);

        $this->actingAs($this->companyAdmin())
            ->get(route('admin.workspaces.administration.section', [
                'section' => 'configuration',
                'tab' => 'document types',
            ]))
            ->assertOk()
            ->assertSee('data-workspace-tab-key="document-types"', false)
            ->assertSee('workspace-pill--active', false);
    }

    public function test_search_finds_form_controls_with_workspace_desk_url(): void
    {
        $this->actingAs($this->companyAdmin());

        $match = collect(app(FeatureRegistry::class)->search('forms', 'administration'))
            ->first(fn (array $entry) => $entry['label'] === 'Form Controls');

        $this->assertNotNull($match);
        $this->assertStringContainsString('/admin/workspaces/administration/configuration', $match['url']);
        $this->assertStringContainsString('tab=form-controls', $match['url']);
    }

    public function test_search_finds_users_with_workspace_desk_url(): void
    {
        $this->actingAs($this->companyAdmin());

        $match = collect(app(FeatureRegistry::class)->search('users', 'administration'))
            ->first(fn (array $entry) => $entry['label'] === 'Users');

        $this->assertNotNull($match);
        $this->assertStringContainsString('/admin/workspaces/administration/security-access', $match['url']);
        $this->assertStringContainsString('tab=users', $match['url']);
    }

    public function test_embedded_settings_panel_does_not_render_duplicate_breadcrumbs(): void
    {
        $user = $this->companyAdmin();

        $this->actingAs($user)
            ->withHeaders(['Turbo-Frame' => 'module-workspace-content'])
            ->get(route('admin.settings.forms.index', ['embedded' => '1']))
            ->assertOk()
            ->assertDontSee(__('Dashboard / Administration'), false);
    }

    public function test_desk_url_resolver_maps_numbering_route(): void
    {
        $url = app(ModuleShellPresenter::class)->deskUrlForFeatureRoute('admin.settings.numbering.index');

        $this->assertNotNull($url);
        $this->assertStringContainsString('/admin/workspaces/administration/configuration', $url);
        $this->assertStringContainsString('tab=number-series', $url);
    }

    public function test_configuration_section_renders_embedded_document_types_frame_src(): void
    {
        $user = $this->companyAdmin();

        $this->actingAs($user)
            ->get(route('admin.workspaces.administration.section', [
                'section' => 'configuration',
                'tab' => 'document-types',
            ]))
            ->assertOk()
            ->assertSee(route('admin.settings.document-types.index', ['embedded' => '1']), false)
            ->assertSee('module-workspace-content', false);
    }

    public function test_document_types_embedded_response_includes_workspace_frame(): void
    {
        $user = $this->companyAdmin();

        $this->actingAs($user)
            ->withHeaders(['Turbo-Frame' => 'module-workspace-content'])
            ->get(route('admin.settings.document-types.index', ['embedded' => '1']))
            ->assertOk()
            ->assertSee('id="module-workspace-content"', false)
            ->assertSee(__('Document Types'), false);
    }

    public function test_embedded_document_types_query_without_turbo_frame_redirects_to_workspace_shell(): void
    {
        $user = $this->companyAdmin();

        $this->actingAs($user)
            ->get(route('admin.settings.document-types.index', ['embedded' => '1']))
            ->assertRedirect(route('admin.workspaces.administration.section', [
                'section' => 'configuration',
                'tab' => 'document-types',
            ]));
    }

    public function test_embedded_form_controls_without_turbo_header_redirects_to_workspace_shell(): void
    {
        $user = $this->companyAdmin();

        $this->actingAs($user)
            ->get(route('admin.settings.forms.index', ['embedded' => '1']))
            ->assertRedirect(route('admin.workspaces.administration.section', [
                'section' => 'configuration',
                'tab' => 'form-controls',
            ]));
    }

    public function test_permission_matrix_role_filter_stays_in_embedded_workspace(): void
    {
        $user = $this->superAdmin();

        $this->actingAs($user)
            ->withHeaders(['Turbo-Frame' => 'module-workspace-content'])
            ->get(route('admin.access-control.matrix', [
                'embedded' => '1',
                'role' => 'Super Admin',
            ]))
            ->assertOk()
            ->assertSee('id="module-workspace-content"', false)
            ->assertSee(__('Super Admin'), false)
            ->assertSee(__('Modules'), false);
    }

    public function test_permission_matrix_role_filter_redirect_preserves_workspace_tab(): void
    {
        $user = $this->superAdmin();

        $response = $this->actingAs($user)
            ->get(route('admin.access-control.matrix', ['role' => 'Super Admin']));

        $response->assertRedirect();
        $location = $response->headers->get('Location');
        $this->assertStringContainsString('tab=permissions', $location);
        $this->assertStringContainsString('role=Super', $location);
    }

    protected function superAdmin(): User
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);
        $user->assignRole('Super Admin');

        return $user;
    }

    protected function companyAdmin(): User
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);
        $user->assignRole('Company Admin');

        return $user;
    }
}
