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

class PrintingIntelligenceWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_overview_loads_with_permission(): void
    {
        [$company, $branch, $user] = $this->userWith(['printing.intelligence.view']);

        $this->actingAs($user)
            ->withSession(['active_company_id' => $company->id, 'active_branch_id' => $branch->id])
            ->withHeaders(['Turbo-Frame' => 'module-workspace-content'])
            ->get(route('admin.printing-intelligence.overview', ['embedded' => '1']))
            ->assertOk()
            ->assertSee(__('Printing Intelligence'));
    }

    public function test_sidebar_shows_printing_intelligence_workspace(): void
    {
        [$company, $branch, $user] = $this->userWith(['printing.intelligence.view']);

        $this->actingAs($user)
            ->withSession(['active_company_id' => $company->id, 'active_branch_id' => $branch->id])
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee(__('Printing Intelligence'), false)
            ->assertSee(route('admin.workspaces.printing-intelligence'), false);
    }

    public function test_workspace_is_visible_via_presenter(): void
    {
        [$company, $branch, $user] = $this->userWith(['printing.intelligence.view']);
        $this->actingAs($user);

        $this->assertTrue(app(\App\Support\Navigation\WorkspacePresenter::class)->isVisible('printing-intelligence'));
    }

    public function test_printing_intelligence_routes_do_not_activate_production_sidebar(): void
    {
        [$company, $branch, $user] = $this->userWith(['printing.intelligence.view', 'production.view']);
        $this->actingAs($user);

        $presenter = app(\App\Support\Navigation\WorkspacePresenter::class);

        $this->assertSame(
            'printing-intelligence',
            $presenter->resolveWorkspaceForRoute('admin.printing-intelligence.overview'),
        );

        $productionRoutes = $presenter->collectActiveRoutes('production');

        $this->assertNotContains('admin.printing-intelligence.*', $productionRoutes);
        $this->assertNotContains('admin.workspaces.printing-intelligence', $productionRoutes);
    }

    public function test_sections_load(): void
    {
        [$company, $branch, $user] = $this->userWith(['printing.intelligence.view']);

        foreach ([
            'admin.printing-intelligence.material',
            'admin.printing-intelligence.machines',
            'admin.printing-intelligence.ink',
            'admin.printing-intelligence.cost',
            'admin.printing-intelligence.quotations',
        ] as $route) {
            $this->actingAs($user)
                ->withSession(['active_company_id' => $company->id, 'active_branch_id' => $branch->id])
                ->withHeaders(['Turbo-Frame' => 'module-workspace-content'])
                ->get(route($route, ['embedded' => '1']))
                ->assertOk()
                ->assertDontSee(__('Coming soon'));
        }

        [, , $estimateActualUser] = $this->userWith(['printing.estimate-actual.view']);
        $this->actingAs($estimateActualUser)
            ->withSession(['active_company_id' => $company->id, 'active_branch_id' => $branch->id])
            ->withHeaders(['Turbo-Frame' => 'module-workspace-content'])
            ->get(route('admin.printing-intelligence.estimate-vs-actual', ['embedded' => '1']))
            ->assertOk();
    }

    public function test_configuration_requires_configure_permission(): void
    {
        [$company, $branch, $user] = $this->userWith(['printing.intelligence.view']);

        $this->actingAs($user)
            ->withSession(['active_company_id' => $company->id, 'active_branch_id' => $branch->id])
            ->withHeaders(['Turbo-Frame' => 'module-workspace-content'])
            ->get(route('admin.printing-intelligence.configuration', ['embedded' => '1']))
            ->assertForbidden();
    }

    public function test_permissions_enforced_on_overview(): void
    {
        [$company, $branch, $user] = $this->userWith(['inventory.view']);

        $this->actingAs($user)
            ->withSession(['active_company_id' => $company->id, 'active_branch_id' => $branch->id])
            ->withHeaders(['Turbo-Frame' => 'module-workspace-content'])
            ->get(route('admin.printing-intelligence.overview', ['embedded' => '1']))
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
