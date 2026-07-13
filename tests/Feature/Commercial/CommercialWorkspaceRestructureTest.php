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

    public function test_commercial_hub_redirects_to_default_workspace_desk(): void
    {
        [$company, $branch, $user] = $this->tenantUser([
            'crm.customers.view', 'quotations.view', 'pos.view',
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $response = $this->actingAs($user)->get(route('admin.workspaces.commercial'));

        $response->assertRedirect();
        $this->actingAs($user)->get($response->headers->get('Location'))->assertOk();
    }

    public function test_crm_section_renders_workspace_switchers_and_embedded_content(): void
    {
        [$company, $branch, $user] = $this->tenantUser([
            'crm.customers.view', 'crm.leads.view', 'commercial.activities.view',
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $response = $this->actingAs($user)->get(route('admin.workspaces.commercial.section', ['section' => 'crm']));

        $response->assertOk();
        $response->assertSee('module-workspace-switcher--primary', false);
        $response->assertSee('module-workspace-switcher--secondary', false);
        $response->assertSee(__('Customers'), false);
        $response->assertSee(__('Leads'), false);
        $response->assertSee(route('admin.crm.customers.index', ['embedded' => '1']), false);
    }

    public function test_point_of_sale_section_lists_pos_tabs(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['pos.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $response = $this->actingAs($user)->get(route('admin.workspaces.commercial.section', ['section' => 'point-of-sale']));

        $response->assertOk();
        $response->assertSee(__('Counter Sales'), false);
        $response->assertSee(route('admin.commercial.pos.dashboard', ['embedded' => '1']), false);
    }

    public function test_user_without_pos_permission_cannot_see_pos_workspace_tab(): void
    {
        [$company, $branch, $user] = $this->tenantUser([
            'commercial.activities.view', 'crm.customers.view',
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $response = $this->actingAs($user)->get(route('admin.workspaces.commercial.section', ['section' => 'crm']));

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
