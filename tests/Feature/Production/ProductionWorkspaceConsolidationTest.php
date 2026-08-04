<?php

namespace Tests\Feature\Production;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Support\Navigation\ProductionWorkspacePresenter;
use App\Support\Production\ProductionFloorDeskViews;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductionWorkspaceConsolidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_operations_section_lists_floor_as_sole_ops_desk(): void
    {
        $labels = collect(app(ProductionWorkspacePresenter::class)->sectionDefinitions()['operations']['groups'] ?? [])
            ->flatMap(fn (array $group) => collect($group['items'] ?? [])->pluck('label'))
            ->all();

        $this->assertSame(['Production Floor'], $labels);
        $this->assertNotContains('All Job Cards', $labels);
        $this->assertNotContains('Work Center Queue', $labels);
        $this->assertNotContains('Finished Goods Outputs', $labels);
    }

    public function test_production_catalog_has_no_reports_or_intelligence_section(): void
    {
        $sections = app(ProductionWorkspacePresenter::class)->sectionDefinitions();
        $hubLabels = collect(app(ProductionWorkspacePresenter::class)->hubDefinitions())
            ->pluck('label')
            ->all();

        $this->assertArrayNotHasKey('reports', $sections);
        $this->assertNotContains('Intelligence', $hubLabels);
        $this->assertNotContains('Reports', $hubLabels);

        $labels = collect($sections)
            ->flatMap(fn (array $section) => collect($section['groups'] ?? [])
                ->flatMap(fn (array $group) => collect($group['items'] ?? [])->pluck('label')))
            ->all();

        $this->assertNotContains('Operations Intelligence', $labels);
        $this->assertNotContains('Job Costing & Profitability', $labels);
        $this->assertNotContains('Production Reports', $labels);
    }

    public function test_legacy_list_routes_redirect_into_floor_modes(): void
    {
        $user = $this->companyAdmin();

        $this->actingAs($user)
            ->get(route('admin.production.job-cards.index'))
            ->assertRedirect(ProductionFloorDeskViews::registerIndexUrl());

        $this->actingAs($user)
            ->get(route('admin.production.queue.index'))
            ->assertRedirect(ProductionFloorDeskViews::queueIndexUrl());

        $this->actingAs($user)
            ->get(route('admin.production.queue.department', 'digital'))
            ->assertRedirect(ProductionFloorDeskViews::queueIndexUrl('digital'));

        $this->actingAs($user)
            ->get(route('admin.production.outputs.index'))
            ->assertRedirect(ProductionFloorDeskViews::outputsIndexUrl());
    }

    public function test_floor_modes_render_on_single_desk(): void
    {
        $user = $this->companyAdmin();

        $this->actingAs($user)
            ->get(route('admin.production.floor'))
            ->assertOk()
            ->assertSee(__('Run'), false)
            ->assertSee(__('Register'), false)
            ->assertSee(__('By department'), false)
            ->assertSee(__('Outputs'), false);

        $this->actingAs($user)
            ->get(ProductionFloorDeskViews::registerIndexUrl())
            ->assertOk()
            ->assertSee(__('Job register'), false);

        $this->actingAs($user)
            ->get(ProductionFloorDeskViews::queueIndexUrl())
            ->assertOk()
            ->assertSee(__('By department'), false)
            ->assertSee(__('More filters'), false);

        $this->actingAs($user)
            ->get(ProductionFloorDeskViews::outputsIndexUrl())
            ->assertOk()
            ->assertSee(__('Finished goods outputs'), false);
    }

    public function test_legacy_routes_remain_registered(): void
    {
        $this->assertTrue(Route::has('admin.production.floor'));
        $this->assertTrue(Route::has('admin.production.job-cards.index'));
        $this->assertTrue(Route::has('admin.production.queue.index'));
        $this->assertTrue(Route::has('admin.production.queue.department'));
        $this->assertTrue(Route::has('admin.production.outputs.index'));
        $this->assertTrue(Route::has('admin.production.dashboard'));
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
