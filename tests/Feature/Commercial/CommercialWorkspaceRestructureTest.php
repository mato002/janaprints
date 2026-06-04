<?php

namespace Tests\Feature\Commercial;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Support\Navigation\CommercialWorkspacePresenter;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CommercialWorkspaceRestructureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_commercial_hub_shows_five_workspace_cards_only(): void
    {
        [$company, $branch, $user] = $this->tenantUser([
            'crm.customers.view', 'quotations.view', 'pos.view',
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $response = $this->actingAs($user)->get(route('admin.workspaces.commercial'));

        $response->assertOk();
        $response->assertSee(__('CRM'), false);
        $response->assertSee(__('Sales'), false);
        $response->assertSee(__('Customer Service'), false);
        $response->assertSee(__('Point Of Sale'), false);
        $response->assertSee(__('Reports'), false);
        $response->assertDontSee(route('admin.crm.customers.index'), false);
        $response->assertDontSee(route('admin.quotations.dashboard'), false);
        $response->assertDontSee(route('admin.commercial.pos.dashboard'), false);
    }

    public function test_crm_section_lists_crm_features(): void
    {
        [$company, $branch, $user] = $this->tenantUser([
            'crm.customers.view', 'crm.leads.view', 'crm.activities.view',
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $response = $this->actingAs($user)->get(route('admin.workspaces.commercial.section', ['section' => 'crm']));

        $response->assertOk();
        $response->assertSee(route('admin.crm.customers.index'), false);
        $response->assertSee(route('admin.crm.leads.index'), false);
        $response->assertSee(route('admin.commercial.activities.index'), false);
    }

    public function test_point_of_sale_section_lists_pos_features(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['pos.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $response = $this->actingAs($user)->get(route('admin.workspaces.commercial.section', ['section' => 'point-of-sale']));

        $response->assertOk();
        $response->assertSee(__('Counter Sales'), false);
        $response->assertSee(route('admin.commercial.pos.dashboard'), false);
    }

    public function test_user_without_pos_permission_cannot_see_pos_workspace_card(): void
    {
        [$company, $branch, $user] = $this->tenantUser([
            'crm.activities.view', 'crm.customers.view',
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $response = $this->actingAs($user)->get(route('admin.workspaces.commercial'));

        $response->assertOk();
        $response->assertSee(__('CRM'), false);
        $response->assertDontSee(route('admin.workspaces.commercial.section', ['section' => 'point-of-sale']), false);
    }

    public function test_existing_commercial_routes_remain_registered(): void
    {
        $this->assertTrue(Route::has('admin.crm.customers.index'));
        $this->assertTrue(Route::has('admin.quotations.dashboard'));
        $this->assertTrue(Route::has('admin.commercial.pos.dashboard'));
        $this->assertTrue(Route::has('admin.workspaces.commercial.section'));
    }

    public function test_commercial_active_routes_include_child_modules(): void
    {
        $patterns = app(CommercialWorkspacePresenter::class)->collectActiveRoutes();

        $this->assertContains('admin.workspaces.commercial', $patterns);
        $this->assertContains('admin.crm.customers.*', $patterns);
        $this->assertContains('admin.commercial.pos.*', $patterns);
    }

    /**
     * @param  list<string>  $permissions
     * @return array{0: Company, 1: Branch, 2: User}
     */
    protected function tenantUser(array $permissions): array
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);

        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $role = Role::findByName('Sales', 'web');
        $role->syncPermissions($permissions);
        $user->assignRole('Sales');

        return [$company, $branch, $user];
    }
}
