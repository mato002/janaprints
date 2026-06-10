<?php

namespace Tests\Feature\Navigation;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Support\Discovery\FeatureRegistry;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CompactWorkspaceNavigationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_hr_people_dashboard_uses_compact_workspace_shell(): void
    {
        $user = $this->hrUser();

        $this->actingAs($user)
            ->get(route('admin.workspaces.hr.section', [
                'section' => 'people',
                'tab' => 'dashboard',
            ]))
            ->assertOk()
            ->assertSee('compact-workspace-header', false)
            ->assertSee('workspace-pill-tabs', false)
            ->assertSee('module-workspace-content', false)
            ->assertSee(route('admin.hr.dashboard', ['embedded' => '1']), false)
            ->assertDontSee('workspace-card-grid', false)
            ->assertDontSee('module-workspace-card', false);
    }

    public function test_administration_configuration_renders_form_controls_in_compact_workspace(): void
    {
        $user = $this->companyAdmin();

        $this->actingAs($user)
            ->get(route('admin.workspaces.administration.section', [
                'section' => 'configuration',
                'tab' => 'form-controls',
            ]))
            ->assertOk()
            ->assertSee('compact-workspace-header', false)
            ->assertSee(__('Form Controls'))
            ->assertSee(route('admin.settings.forms.index', ['embedded' => '1']), false);
    }

    public function test_active_workspace_tab_persists_after_refresh(): void
    {
        $user = $this->companyAdmin();

        $url = route('admin.workspaces.administration.section', [
            'section' => 'configuration',
        ]);

        $this->actingAs($user)
            ->get($url)
            ->assertOk()
            ->assertSee('/admin/workspaces/administration/configuration', false)
            ->assertSee('workspace-pill--active', false)
            ->assertSee(__('Configuration'));
    }

    public function test_active_sub_tab_persists_after_refresh(): void
    {
        $user = $this->companyAdmin();

        $url = route('admin.workspaces.administration.section', [
            'section' => 'configuration',
            'tab' => 'form-controls',
        ]);

        $html = $this->actingAs($user)
            ->get($url)
            ->assertOk()
            ->assertSee(route('admin.settings.forms.index', ['embedded' => '1']), false)
            ->getContent();

        preg_match('/module-workspace-switcher--secondary.*?<\/nav>/s', $html, $matches);
        $secondaryNav = $matches[0] ?? '';

        $this->assertStringContainsString(__('Form Controls'), $secondaryNav);
        $this->assertMatchesRegularExpression(
            '/workspace-pill--active[^>]*>[\s\S]*?'.preg_quote(__('Form Controls'), '/').'/s',
            $secondaryNav,
        );
    }

    public function test_search_finds_hidden_workspace_targets(): void
    {
        $this->actingAs($this->companyAdmin());

        $match = collect(app(FeatureRegistry::class)->search('form controls', 'administration'))
            ->first(fn (array $entry) => $entry['label'] === 'Form Controls');

        $this->assertNotNull($match);
        $this->assertStringContainsString('embedded=1', $match['url']);
        $this->assertStringContainsString('/admin/settings/forms', $match['url']);
    }

    public function test_workspace_tabs_use_horizontal_scroll_track(): void
    {
        $user = $this->companyAdmin();

        $this->actingAs($user)
            ->get(route('admin.workspaces.administration.section', ['section' => 'configuration']))
            ->assertOk()
            ->assertSee('workspace-pill-tabs__track', false)
            ->assertSee('module-workspace-switcher__track', false);
    }

    public function test_module_desk_does_not_render_workspace_card_grid_above_content(): void
    {
        $modules = [
            ['commercial', 'crm'],
            ['production', 'operations'],
            ['supply-chain', 'catalogue'],
            ['accounting', 'ledger'],
            ['assets', 'registry'],
            ['reports', 'executive'],
        ];

        $user = $this->companyAdmin();
        $this->actingAs($user);

        foreach ($modules as [$module, $section]) {
            $response = $this->get(route("admin.workspaces.{$module}.section", ['section' => $section]));

            if ($response->status() === 200) {
                $response
                    ->assertDontSee('workspace-card-grid', false)
                    ->assertDontSee('settings-tile', false);
            }
        }
    }

    public function test_commercial_workspace_renders_compact_primary_tabs(): void
    {
        $user = $this->companyAdmin();

        $this->actingAs($user)
            ->get(route('admin.workspaces.commercial.section', ['section' => 'crm']))
            ->assertOk()
            ->assertSee('module-workspace-switcher--primary', false)
            ->assertSee('module-workspace-switcher--secondary', false)
            ->assertSee('workspace-pill', false);
    }

    public function test_communications_sms_workspace_hides_legacy_channel_nav_in_embedded_content(): void
    {
        $user = $this->companyAdmin();

        $this->actingAs($user)
            ->get(route('admin.workspaces.communications.section', [
                'section' => 'sms',
                'tab' => 'sms-dashboard',
            ]))
            ->assertOk()
            ->assertSee(__('SMS Dashboard'))
            ->assertSee(route('admin.communications.sms.dashboard', ['embedded' => '1']), false);

        $this->actingAs($user)
            ->get('/admin/communications/sms/dashboard?embedded=1', [
                'Turbo-Frame' => 'module-workspace-content',
            ]);

        $embeddedNav = view('admin.communications.sms.partials.nav')->render();
        $this->assertStringNotContainsString(__('Credit ledger'), $embeddedNav);
        $this->assertStringNotContainsString(__('Provider logs'), $embeddedNav);

        $this->get('/admin/communications/sms/dashboard');
        $standaloneNav = view('admin.communications.sms.partials.nav')->render();
        $this->assertStringContainsString(__('Credit ledger'), $standaloneNav);
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
