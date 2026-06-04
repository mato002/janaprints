<?php

namespace Tests\Feature\Navigation;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Support\Navigation\WorkspacePresenter;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WorkspaceNavigationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_sidebar_renders_only_root_workspaces(): void
    {
        $user = $this->companyAdmin();

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Commercial', false);
        $response->assertSee('Administration', false);
        $response->assertDontSee('data-nav-depth="child"', false);
    }

    public function test_administration_feature_links_are_not_duplicated_in_sidebar(): void
    {
        $user = $this->companyAdmin();

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertOk();
        $sidebar = $this->extractSidebar($response->getContent());

        foreach (['Users', 'Roles', 'Permissions', 'System Settings', 'Form Controls', 'Approval Rules', 'Numbering Rules', 'Audit Logs'] as $label) {
            $this->assertStringNotContainsString(
                '>'.$label.'<',
                $sidebar,
                "Sidebar should not expose duplicated Administration link: {$label}",
            );
        }
    }

    public function test_user_without_administration_access_gets_forbidden_on_workspace(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);
        $role = Role::findByName('Company Admin', 'web');
        $role->syncPermissions(['production.view']);
        $user->assignRole('Company Admin');

        $this->actingAs($user)->get(route('admin.workspaces.administration'))->assertForbidden();
    }

    public function test_user_without_permission_does_not_see_restricted_workspace_cards(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);
        $role = Role::findByName('Company Admin', 'web');
        $role->syncPermissions(['quotations.view']);
        $user->assignRole('Company Admin');

        $response = $this->actingAs($user)->get(route('admin.workspaces.commercial'));

        $response->assertOk();
        $response->assertSee(route('admin.workspaces.commercial.section', ['section' => 'sales']), false);
        $response->assertDontSee(route('admin.workspaces.commercial.section', ['section' => 'crm']), false);

        $this->actingAs($user)
            ->get(route('admin.workspaces.commercial.section', ['section' => 'sales']))
            ->assertOk()
            ->assertSee(route('admin.quotations.dashboard'), false)
            ->assertDontSee(route('admin.crm.customers.index'), false);
    }

    public function test_active_workspace_route_patterns_include_child_routes(): void
    {
        $this->actingAs($this->companyAdmin());

        $patterns = app(\App\Support\Navigation\CommercialWorkspacePresenter::class)->collectActiveRoutes();

        $this->assertContains('admin.workspaces.commercial', $patterns);
        $this->assertContains('admin.crm.customers.*', $patterns);
        $this->assertContains('admin.quotations.*', $patterns);
        $this->assertContains('admin.commercial.activities.*', $patterns);
        $this->assertContains('admin.commercial.pos.*', $patterns);
    }

    public function test_commercial_activities_and_pos_sections_are_active(): void
    {
        $user = $this->companyAdmin();

        $crm = $this->actingAs($user)->get(route('admin.workspaces.commercial.section', ['section' => 'crm']));
        $crm->assertOk();
        $crm->assertSee(route('admin.commercial.activities.index'), false);

        $pos = $this->actingAs($user)->get(route('admin.workspaces.commercial.section', ['section' => 'point-of-sale']));
        $pos->assertOk();
        $pos->assertSee(route('admin.commercial.pos.dashboard'), false);
    }

    public function test_existing_feature_routes_still_work(): void
    {
        $user = $this->companyAdmin();

        $this->actingAs($user)->get(route('admin.crm.customers.index'))->assertOk();
        $this->actingAs($user)->get(route('admin.settings.forms.index'))->assertOk();
        $this->actingAs($user)->get(route('admin.inventory.dashboard'))->assertOk();
    }

    public function test_navigation_config_has_no_duplicate_route_names(): void
    {
        $routes = collect(config('navigation'))
            ->pluck('route')
            ->filter()
            ->values();

        $this->assertSame($routes->count(), $routes->unique()->count());
    }

    public function test_workspace_hub_pages_render_grouped_cards(): void
    {
        $user = $this->companyAdmin();

        $response = $this->actingAs($user)->get(route('admin.workspaces.administration'));

        $response->assertOk();
        $response->assertSee('Access Control', false);
        $response->assertSee('Settings Hub', false);
        $response->assertSee('Audit Center', false);
        $response->assertSee(route('admin.users.index'), false);
    }

    public function test_all_workspace_routes_are_registered(): void
    {
        foreach (array_keys(config('workspaces', [])) as $workspace) {
            $this->assertTrue(Route::has("admin.workspaces.{$workspace}"));
        }
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
