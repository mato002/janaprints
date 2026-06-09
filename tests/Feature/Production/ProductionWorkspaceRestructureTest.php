<?php

namespace Tests\Feature\Production;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Support\Navigation\ProductionWorkspacePresenter;
use App\Support\Navigation\WorkspacePresenter;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductionWorkspaceRestructureTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var array<string, string>
     */
    private array $hubCards = [
        'Production Command Center' => 'admin.production.dashboard',
        'Job Cards' => 'admin.production.job-cards.index',
        'Production Queue' => 'admin.production.queue.index',
        'Scheduling' => 'admin.production.scheduling.index',
        'Quality Control' => 'admin.production.quality.index',
        'Work Centers' => 'admin.production.work-centers.index',
        'Job Costing & Profitability' => 'admin.production.costing.dashboard',
        'Dispatch Workspace' => 'admin.workspaces.dispatch',
        'Production 360' => 'admin.reports.production360',
        'Production Reports' => 'admin.reports.production',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_production_hub_redirects_to_default_workspace_desk(): void
    {
        $user = $this->userWithFullProductionAccess();
        $this->actingAs($user);

        $response = $this->get(route('admin.workspaces.production'));

        $response->assertRedirect();
        $this->get($response->headers->get('Location'))->assertOk();
    }

    public function test_operations_section_renders_workspace_switchers(): void
    {
        $user = $this->userWithFullProductionAccess();
        $this->actingAs($user);

        $response = $this->get(route('admin.workspaces.production.section', ['section' => 'operations']));

        $response->assertOk();
        $response->assertSee('module-workspace-switcher--primary', false);
        $response->assertSee(__('Job Cards'), false);
        $response->assertSee(route('admin.production.job-cards.index', ['embedded' => '1']), false);
    }

    public function test_removed_legacy_dispatch_card_is_not_in_catalog(): void
    {
        $labels = collect(app(ProductionWorkspacePresenter::class)->sectionDefinitions())
            ->flatMap(fn (array $section) => collect($section['groups'] ?? [])
                ->flatMap(fn (array $group) => collect($group['items'] ?? [])->pluck('label')))
            ->all();

        $this->assertNotContains('Dispatch (legacy)', $labels);
        $this->assertContains('Dispatch Workspace', $labels);
        $this->assertNotContains('Production Dashboard', $labels);
        $this->assertContains('Production Command Center', $labels);
    }

    public function test_user_without_dispatch_permission_does_not_see_dispatch_tab(): void
    {
        $user = $this->userWithPermissions([
            'production.view',
            'production.queue.view',
            'production.scheduling.view',
            'production.quality.view',
            'production.work-centers.view',
        ]);

        $this->actingAs($user)
            ->get(route('admin.workspaces.production.section', ['section' => 'operations']))
            ->assertOk()
            ->assertDontSee(__('Dispatch'), false)
            ->assertDontSee(route('admin.workspaces.dispatch'), false);
    }

    public function test_user_without_reports_permission_does_not_see_reports_tab(): void
    {
        $user = $this->userWithPermissions([
            'production.view',
            'production.queue.view',
        ]);

        $this->actingAs($user)
            ->get(route('admin.workspaces.production.section', ['section' => 'operations']))
            ->assertOk()
            ->assertDontSee(__('Production 360'), false)
            ->assertDontSee(__('Production Reports'), false);
    }

    public function test_workspace_presenter_collects_production_active_routes(): void
    {
        $this->actingAs($this->userWithFullProductionAccess());

        $patterns = app(WorkspacePresenter::class)->collectActiveRoutes('production');

        $this->assertContains('admin.production.dashboard', $patterns);
        $this->assertContains('admin.production.job-cards.*', $patterns);
        $this->assertContains('admin.reports.production360', $patterns);
        $this->assertContains('admin.dispatch.*', $patterns);
    }

    public function test_search_index_includes_production_hub_features(): void
    {
        $this->actingAs($this->userWithFullProductionAccess());

        $index = app(WorkspacePresenter::class)->flattenForSearch('production');
        $routes = collect($index)->pluck('route')->filter()->all();

        foreach ($this->hubCards as $routeName) {
            $this->assertContains($routeName, $routes, "Search index missing route: {$routeName}");
        }
    }

    public function test_production_breadcrumbs_point_to_workspace_hub(): void
    {
        $user = $this->userWithFullProductionAccess();
        $this->actingAs($user);

        $hubUrl = route('admin.workspaces.production');

        session([
            'active_company_id' => Company::query()->where('code', 'JANA')->firstOrFail()->id,
            'active_branch_id' => Branch::query()->where('code', 'HQ')->firstOrFail()->id,
        ]);

        $this->get(route('admin.production.work-centers.index'))
            ->assertOk()
            ->assertSee($hubUrl, false);

        $this->get(route('admin.production.job-cards.index'))
            ->assertOk()
            ->assertSee($hubUrl, false);
    }

    public function test_all_hub_routes_are_registered(): void
    {
        foreach ($this->hubCards as $routeName) {
            $this->assertTrue(Route::has($routeName), "Missing route: {$routeName}");
        }
    }

    protected function userWithFullProductionAccess(): User
    {
        return $this->userWithPermissions([
            'production.view',
            'production.queue.view',
            'production.scheduling.view',
            'production.quality.view',
            'production.work-centers.view',
            'production.costing.view',
            'dispatch.view',
            'reports.view',
            'intelligence.production.view',
        ]);
    }

    /**
     * @param  list<string>  $permissions
     */
    protected function userWithPermissions(array $permissions): User
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);
        $role = Role::findByName('Company Admin', 'web');
        $role->syncPermissions($permissions);
        $user->assignRole('Company Admin');

        return $user;
    }
}
