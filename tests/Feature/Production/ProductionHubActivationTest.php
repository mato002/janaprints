<?php

namespace Tests\Feature\Production;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Providers\AppServiceProvider;
use App\Support\Navigation\ProductionWorkspacePresenter;
use App\Support\Navigation\WorkspacePresenter;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductionHubActivationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var list<string>
     */
    private array $activatedCards = [
        'Production Queue' => 'admin.production.queue.index',
        'Scheduling' => 'admin.production.scheduling.index',
        'Quality Control' => 'admin.production.quality.index',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_production_module_routes_are_registered(): void
    {
        foreach ($this->activatedCards as $routeName) {
            $this->assertTrue(Route::has($routeName));
        }
    }

    public function test_workspace_presenter_marks_production_modules_active_when_routes_exist(): void
    {
        $user = $this->userWithProductionModules();
        $this->actingAs($user);

        $presented = app(ProductionWorkspacePresenter::class)->presentHub();
        $items = collect($presented['groups'])
            ->flatMap(fn (array $group) => $group['items'])
            ->keyBy('label');

        foreach ($this->activatedCards as $label => $routeName) {
            $item = $items->get($label);
            $this->assertNotNull($item, "Missing presented item: {$label}");
            $this->assertFalse($item['comingSoon'], "{$label} should not be coming soon");
            $this->assertSame(__('Active'), $item['statusLabel']);
            $this->assertSame('success', $item['statusVariant']);
            $this->assertSame(route($routeName), $item['href']);
        }
    }

    public function test_production_hub_cards_are_clickable_active_not_coming_soon(): void
    {
        $user = $this->userWithProductionModules();

        $response = $this->actingAs($user)->get(route('admin.workspaces.production'));

        $response->assertOk();
        $content = $response->getContent();

        foreach ($this->activatedCards as $label => $routeName) {
            $response->assertSee(route($routeName), false);
            $this->assertMatchesRegularExpression(
                '/'.preg_quote($label, '/').'[\s\S]*?'.preg_quote(__('Active'), '/').'/u',
                $content,
            );
            $this->assertDoesNotMatchRegularExpression(
                '/'.preg_quote($label, '/').'[\s\S]*?aria-disabled="true"/u',
                $content,
            );
        }

        $response->assertDontSee(__('Foundation'), false);
    }

    public function test_search_index_includes_activated_production_module_routes(): void
    {
        $user = $this->userWithProductionModules();
        $this->actingAs($user);

        $index = app(WorkspacePresenter::class)->flattenForSearch('production');
        $routes = collect($index)
            ->reject(fn (array $entry) => $entry['coming_soon'] || empty($entry['route']))
            ->pluck('route')
            ->all();

        foreach ($this->activatedCards as $routeName) {
            $this->assertContains($routeName, $routes, "Search index missing route: {$routeName}");
        }

        $navUrls = AppServiceProvider::buildNavRouteUrls(
            config('navigation'),
            app(WorkspacePresenter::class),
        );

        foreach ($this->activatedCards as $routeName) {
            $this->assertArrayHasKey($routeName, $navUrls);
        }
    }

    public function test_production_workspace_and_module_workspaces_load(): void
    {
        $user = $this->userWithProductionModules();
        $this->actingAs($user);

        $this->get(route('admin.workspaces.production'))->assertOk();
        $this->get(route('admin.production.queue.index'))->assertOk();
        $this->get(route('admin.production.scheduling.index'))->assertOk();
        $this->get(route('admin.production.quality.index'))->assertOk();
    }

    public function test_active_route_patterns_highlight_module_workspaces_in_sidebar_context(): void
    {
        $this->actingAs($this->userWithProductionModules());

        $patterns = app(WorkspacePresenter::class)->collectActiveRoutes('production');

        $this->assertContains('admin.production.queue.*', $patterns);
        $this->assertContains('admin.production.scheduling.*', $patterns);
        $this->assertContains('admin.production.quality.*', $patterns);
    }

    protected function userWithProductionModules(): User
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);
        $role = Role::findByName('Company Admin', 'web');
        $role->syncPermissions([
            'production.view',
            'production.queue.view',
            'production.scheduling.view',
            'production.quality.view',
        ]);
        $user->assignRole('Company Admin');

        return $user;
    }
}
