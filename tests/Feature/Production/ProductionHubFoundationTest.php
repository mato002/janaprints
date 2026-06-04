<?php

namespace Tests\Feature\Production;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Support\Navigation\WorkspacePresenter;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductionHubFoundationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var list<string>
     */
    private array $moduleRoutes = [
        'admin.production.queue.index',
        'admin.production.scheduling.index',
        'admin.production.quality.index',
    ];

    /**
     * @var list<string>
     */
    private array $modulePermissions = [
        'production.queue.view',
        'production.scheduling.view',
        'production.quality.view',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_production_module_placeholder_routes_are_registered(): void
    {
        foreach ($this->moduleRoutes as $routeName) {
            $this->assertTrue(Route::has($routeName), "Missing route: {$routeName}");
        }
    }

    public function test_production_hub_permissions_exist_after_seeder(): void
    {
        foreach ($this->modulePermissions as $permission) {
            $this->assertNotNull(
                Permission::findByName($permission, 'web'),
                "Missing permission: {$permission}",
            );
        }
    }

    public function test_workspace_config_wires_production_module_features(): void
    {
        $items = collect(config('workspaces.production.groups.0.items', []))
            ->keyBy('label');

        foreach ([
            'Scheduling' => ['route' => 'admin.production.scheduling.index', 'permission' => 'production.scheduling.view'],
            'Production Queue' => ['route' => 'admin.production.queue.index', 'permission' => 'production.queue.view'],
            'Quality Control' => ['route' => 'admin.production.quality.index', 'permission' => 'production.quality.view'],
        ] as $label => $expected) {
            $item = $items->get($label);
            $this->assertNotNull($item, "Missing workspace item: {$label}");
            $this->assertSame($expected['route'], $item['route'] ?? null);
            $this->assertSame($expected['permission'], $item['permission'] ?? null);
            $this->assertArrayNotHasKey('coming_soon', $item);
            $this->assertArrayNotHasKey('foundation', $item);
        }
    }

    public function test_production_workspace_shows_module_cards_for_authorized_user(): void
    {
        $user = $this->productionHubUser($this->modulePermissions);

        $response = $this->actingAs($user)->get(route('admin.workspaces.production'));

        $response->assertOk();
        $response->assertSee(route('admin.production.queue.index'), false);
        $response->assertSee(route('admin.production.scheduling.index'), false);
        $response->assertSee(route('admin.production.quality.index'), false);
        $response->assertSee(__('Active'), false);
    }

    public function test_production_workspace_hides_module_cards_without_module_permissions(): void
    {
        $user = $this->productionHubUser(['production.view']);

        $response = $this->actingAs($user)->get(route('admin.workspaces.production'));

        $response->assertOk();
        $response->assertDontSee(route('admin.production.queue.index'), false);
        $response->assertDontSee(route('admin.production.scheduling.index'), false);
        $response->assertDontSee(route('admin.production.quality.index'), false);
        $response->assertSee(route('admin.production.job-cards.index'), false);
    }

    public function test_queue_route_serves_workspace_not_placeholder(): void
    {
        $user = $this->productionHubUser(['production.queue.view']);

        $this->actingAs($user)
            ->get(route('admin.production.queue.index'))
            ->assertOk()
            ->assertSee(__('Production Queue'), false);
    }

    public function test_scheduling_route_serves_workspace_not_placeholder(): void
    {
        $user = $this->productionHubUser(['production.scheduling.view']);

        $this->actingAs($user)
            ->get(route('admin.production.scheduling.index'))
            ->assertOk()
            ->assertSee(__('Scheduling'), false);
    }

    public function test_quality_route_serves_workspace_not_placeholder(): void
    {
        $user = $this->productionHubUser(['production.quality.view']);

        $this->actingAs($user)
            ->get(route('admin.production.quality.index'))
            ->assertOk()
            ->assertSee(__('Quality Control'), false);
    }

    public function test_module_routes_require_module_permissions(): void
    {
        $user = $this->productionHubUser(['production.view']);

        $this->actingAs($user)->get(route('admin.production.queue.index'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.production.scheduling.index'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.production.quality.index'))->assertForbidden();
    }

    public function test_active_route_patterns_include_production_module_routes(): void
    {
        $patterns = app(WorkspacePresenter::class)->collectActiveRoutes('production');

        $this->assertContains('admin.production.queue.*', $patterns);
        $this->assertContains('admin.production.scheduling.*', $patterns);
        $this->assertContains('admin.production.quality.*', $patterns);
    }

    /**
     * @param  list<string>  $permissions
     */
    protected function productionHubUser(array $permissions): User
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);
        $role = Role::findByName('Company Admin', 'web');
        $role->syncPermissions(array_values(array_unique(array_merge(['production.view'], $permissions))));
        $user->assignRole('Company Admin');

        return $user;
    }
}
