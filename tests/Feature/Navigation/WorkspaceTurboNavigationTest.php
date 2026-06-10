<?php

namespace Tests\Feature\Navigation;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Support\Discovery\FeatureRegistry;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkspaceTurboNavigationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_secondary_tab_links_target_module_workspace_content_frame(): void
    {
        $user = $this->companyAdmin();

        $html = $this->actingAs($user)
            ->get(route('admin.workspaces.commercial.section', ['section' => 'crm']))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('module-workspace-switcher--secondary', $html);
        $this->assertMatchesRegularExpression(
            '/module-workspace-switcher--secondary[^>]*>.*?data-turbo-frame="module-workspace-content"/s',
            $html,
        );
    }

    public function test_secondary_tab_links_use_embedded_feature_urls(): void
    {
        $user = $this->companyAdmin();

        $html = $this->actingAs($user)
            ->get(route('admin.workspaces.commercial.section', ['section' => 'crm']))
            ->assertOk()
            ->getContent();

        $this->assertMatchesRegularExpression(
            '/module-workspace-switcher--secondary.*?<\/nav>/s',
            $html,
            'Secondary workspace tab nav not found.',
        );

        preg_match('/module-workspace-switcher--secondary.*?<\/nav>/s', $html, $matches);
        $secondaryNav = $matches[0];

        $this->assertStringContainsString(
            route('admin.crm.customers.index', ['embedded' => '1']),
            $secondaryNav,
        );
        $this->assertStringContainsString(
            route('admin.crm.leads.index', ['embedded' => '1']),
            $secondaryNav,
        );
        $this->assertStringNotContainsString('tab=customers', $secondaryNav);
    }

    public function test_sidebar_module_links_target_erp_main_frame(): void
    {
        $user = $this->companyAdmin();

        $sidebar = $this->extractSidebar(
            $this->actingAs($user)->get(route('admin.dashboard'))->assertOk()->getContent(),
        );

        $this->assertStringContainsString('data-turbo-frame="erp-main"', $sidebar);
        $this->assertStringContainsString(route('admin.workspaces.commercial'), $sidebar);
    }

    public function test_workspace_search_uses_embedded_urls_for_same_module_features(): void
    {
        $this->actingAs($this->companyAdmin());

        $match = collect(app(FeatureRegistry::class)->search('form controls', 'administration'))
            ->first(fn (array $entry) => $entry['label'] === 'Form Controls');

        $this->assertNotNull($match);
        $this->assertStringContainsString('embedded=1', $match['url']);
        $this->assertStringContainsString('/admin/settings/forms', $match['url']);
        $this->assertSame('module-workspace-content', $match['turbo_frame']);
    }

    public function test_direct_feature_url_redirects_into_workspace_shell(): void
    {
        $user = $this->companyAdmin();

        $this->actingAs($user)
            ->get(route('admin.crm.customers.index'))
            ->assertRedirect();

        $this->actingAs($user)
            ->get(route('admin.settings.forms.index'))
            ->assertRedirect();
    }

    public function test_embedded_feature_url_does_not_redirect_when_loaded_in_content_frame(): void
    {
        $user = $this->companyAdmin();

        $this->actingAs($user)
            ->withHeaders(['Turbo-Frame' => 'module-workspace-content'])
            ->get(route('admin.crm.customers.index', ['embedded' => '1']))
            ->assertOk()
            ->assertSee('id="module-workspace-content"', false);

        $this->actingAs($user)
            ->withHeaders(['Turbo-Frame' => 'module-workspace-content'])
            ->get(route('admin.settings.forms.index', ['embedded' => '1']))
            ->assertOk()
            ->assertSee('id="module-workspace-content"', false);
    }

    public function test_embedded_feature_url_never_redirects_even_without_turbo_frame_header(): void
    {
        $user = $this->companyAdmin();

        $this->actingAs($user)
            ->get(route('admin.crm.customers.index', ['embedded' => '1']))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('admin.dispatch.dashboard', ['embedded' => '1']))
            ->assertOk();
    }

    public function test_modal_create_forms_still_open_in_erp_form_modal(): void
    {
        $user = $this->companyAdmin();

        $this->actingAs($user)
            ->withHeader('Turbo-Frame', 'erp-form-modal')
            ->get(route('admin.crm.customers.create', ['from' => 'commercial']))
            ->assertOk()
            ->assertSee('data-erp-form-modal-panel', false);
    }

    public function test_drawer_previews_still_open_in_erp_preview_drawer(): void
    {
        $user = $this->companyAdmin();

        $this->actingAs($user)
            ->get(route('admin.workspaces.commercial.section', ['section' => 'crm']))
            ->assertOk()
            ->assertSee('id="erp-preview-drawer"', false);

        request()->headers->set('Turbo-Frame', 'erp-preview-drawer');

        $drawerHtml = view('components.admin.drawer', ['title' => 'Preview'])
            ->with('slot', 'Drawer body')
            ->render();

        $this->assertStringContainsString('id="erp-preview-drawer"', $drawerHtml);
        $this->assertStringContainsString('data-erp-drawer-panel', $drawerHtml);
    }

    public function test_primary_workspace_tabs_still_target_erp_main_frame(): void
    {
        $user = $this->companyAdmin();

        $html = $this->actingAs($user)
            ->get(route('admin.workspaces.commercial.section', ['section' => 'crm']))
            ->assertOk()
            ->getContent();

        $this->assertMatchesRegularExpression(
            '/module-workspace-switcher--primary[^>]*>.*?data-turbo-frame="erp-main"/s',
            $html,
        );
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

    protected function extractSidebar(string $html): string
    {
        if (! preg_match('/id="erp-sidebar"[^>]*>(.*?)<\/aside>/s', $html, $matches)) {
            return $html;
        }

        return $matches[1];
    }
}
