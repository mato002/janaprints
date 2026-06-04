<?php

namespace Tests\Feature\Administration;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Support\Navigation\AdministrationWorkspacePresenter;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AdministrationWorkspaceRestructureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_administration_hub_shows_six_workspace_cards_only(): void
    {
        $user = $this->companyAdmin();

        $response = $this->actingAs($user)->get(route('admin.workspaces.administration'));

        $response->assertOk();
        $response->assertSeeText(__('Security & Access'));
        $response->assertSeeText(__('Organization'));
        $response->assertSeeText(__('Configuration'));
        $response->assertSeeText(__('Workflow & Governance'));
        $response->assertSeeText(__('Integrations'));
        $response->assertSeeText(__('System Operations'));
        $response->assertDontSee('User accounts, branches, and role assignment', false);
        $response->assertDontSee(__('Access Control'), false);
        $response->assertDontSee(__('Settings Hub'), false);
    }

    public function test_security_access_section_lists_identity_features(): void
    {
        $user = $this->companyAdmin();

        $response = $this->actingAs($user)->get(route('admin.workspaces.administration.section', ['section' => 'security-access']));

        $response->assertOk();
        $response->assertSee(route('admin.users.index'), false);
        $response->assertSee(route('admin.access-control.roles'), false);
        $response->assertSee(route('admin.access-control.matrix'), false);
    }

    public function test_configuration_section_lists_settings_features(): void
    {
        $user = $this->companyAdmin();

        $response = $this->actingAs($user)->get(route('admin.workspaces.administration.section', ['section' => 'configuration']));

        $response->assertOk();
        $response->assertSee(route('admin.settings.index'), false);
        $response->assertSee(route('admin.settings.numbering.index'), false);
        $response->assertSee(route('admin.settings.forms.index'), false);
    }

    public function test_existing_administration_feature_routes_remain_registered(): void
    {
        $this->assertTrue(Route::has('admin.users.index'));
        $this->assertTrue(Route::has('admin.settings.approvals.index'));
        $this->assertTrue(Route::has('admin.activity-logs.index'));
    }

    public function test_administration_active_routes_include_child_modules(): void
    {
        $patterns = app(AdministrationWorkspacePresenter::class)->collectActiveRoutes();

        $this->assertContains('admin.workspaces.administration', $patterns);
        $this->assertContains('admin.users.*', $patterns);
        $this->assertContains('admin.settings.forms.*', $patterns);
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
