<?php

namespace Tests\Feature\Production;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Support\Production\ProductionOperatorMode;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductionOperatorModeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_production_role_user_prefers_operator_mode_but_admins_do_not(): void
    {
        $operator = $this->userWithRole('Production');
        $admin = $this->userWithRole('Company Admin');

        $this->assertTrue(ProductionOperatorMode::enabledFor($operator));
        $this->assertTrue($operator->prefersProductionOperatorMode());
        $this->assertFalse(ProductionOperatorMode::enabledFor($admin));
        $this->assertFalse($admin->prefersProductionOperatorMode());
    }

    public function test_operator_sidebar_shows_permission_visible_workspaces(): void
    {
        $operator = $this->userWithRole('Production');

        $html = $this->actingAs($operator)
            ->get(ProductionOperatorMode::homeUrl($operator))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('title="Production Floor"', $html);
        $this->assertStringContainsString('title="Production"', $html);
        $this->assertStringContainsString('title="Inventory"', $html);
        // Production role has view access to these modules — sidebar must not hide them.
        $this->assertStringContainsString('title="Sales"', $html);
        $this->assertStringContainsString('title="Assets"', $html);
        $this->assertStringContainsString('title="Printing Intelligence"', $html);
        // No accounting / HR / administration view permissions on Production.
        $this->assertStringNotContainsString('title="Accounting"', $html);
        $this->assertStringNotContainsString('title="HR"', $html);
        $this->assertStringNotContainsString('title="Administration"', $html);
    }

    public function test_operator_floor_exposes_request_materials_action(): void
    {
        $operator = $this->userWithRole('Production');

        $this->actingAs($operator)
            ->get(ProductionOperatorMode::homeUrl($operator))
            ->assertOk()
            ->assertSee(__('Request materials'), false)
            ->assertSee(route('admin.procurement.requests.create', ['from' => 'production-floor'], false), false)
            ->assertSee('>Request materials<', false);
    }

    public function test_operator_can_open_inventory_workspace(): void
    {
        $operator = $this->userWithRole('Production');

        $this->actingAs($operator)
            ->get(route('admin.workspaces.supply-chain'))
            ->assertRedirect();

        $this->actingAs($operator)
            ->followingRedirects()
            ->get(route('admin.workspaces.supply-chain'))
            ->assertOk()
            ->assertSee(__('Inventory'), false);
    }

    public function test_operator_can_open_procurement_buy_desk_with_requests_view(): void
    {
        $operator = $this->userWithRole('Production');

        $this->actingAs($operator)
            ->get(route('admin.procurement.desk', ['view' => 'requests']))
            ->assertOk();

        $this->actingAs($operator)
            ->followingRedirects()
            ->get(route('admin.workspaces.supply-chain.section', [
                'section' => 'procurement',
                'tab' => 'buy-desk',
            ]))
            ->assertOk();
    }

    public function test_production_operator_login_lands_on_floor(): void
    {
        $operator = $this->userWithRole('Production');

        $response = $this->post(route('admin.login'), [
            'email' => $operator->email,
            'password' => 'password',
        ]);

        $response->assertRedirect();
        $this->assertStringContainsString('/admin/production/floor', $response->headers->get('Location') ?? '');
    }

    public function test_company_admin_login_still_lands_on_dashboard(): void
    {
        $admin = $this->userWithRole('Company Admin');

        $this->post(route('admin.login'), [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard', absolute: false));
    }

    public function test_operator_floor_skips_module_desk_wrapper(): void
    {
        $operator = $this->userWithRole('Production');
        $floorHome = ProductionOperatorMode::homeUrl($operator);

        $this->actingAs($operator)
            ->get($floorHome)
            ->assertOk()
            ->assertSee(__('Operator Floor'));
    }

    public function test_admin_floor_keeps_full_workspace_desk_redirect(): void
    {
        $admin = $this->userWithRole('Company Admin');

        $this->actingAs($admin)
            ->get(route('admin.production.floor'))
            ->assertRedirect();

        $this->actingAs($admin)
            ->get(route('admin.workspaces.production.section', [
                'section' => 'operations',
                'tab' => 'production-floor',
            ]))
            ->assertOk()
            ->assertDontSee(__('Operator mode'));
    }

    public function test_admin_floor_desk_flag_opens_standalone_register(): void
    {
        $admin = $this->userWithRole('Company Admin');

        $this->actingAs($admin)
            ->get(route('admin.production.floor', ['desk' => 1]))
            ->assertOk()
            ->assertSee(__('Production Floor'), false);
    }

    public function test_operator_production_workspace_redirects_to_floor_without_loop(): void
    {
        $operator = $this->userWithRole('Production');
        $floorHome = ProductionOperatorMode::homeUrl($operator);

        $this->actingAs($operator)
            ->get(route('admin.workspaces.production.section', [
                'section' => 'operations',
                'tab' => 'production-floor',
            ]))
            ->assertRedirect($floorHome);

        // Critical: floor must not bounce operators back into the workspace shell.
        $this->actingAs($operator)
            ->get($floorHome)
            ->assertOk();

        // Multi-tab Production desk remains available with ?desk=1.
        $this->actingAs($operator)
            ->get(route('admin.workspaces.production', ['desk' => 1]))
            ->assertRedirect();
    }

    public function test_operator_floor_panel_links_include_modal_context(): void
    {
        $operator = $this->userWithRole('Production');
        $companyId = $operator->company_id;
        $branchId = $operator->default_branch_id;
        session(['active_company_id' => $companyId, 'active_branch_id' => $branchId]);

        $customer = \App\Models\Crm\Customer::factory()->create([
            'company_id' => $companyId,
            'branch_id' => $branchId,
        ]);

        $jobCard = \App\Models\Production\ProductionJobCard::factory()->create([
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'customer_id' => $customer->id,
            'status' => \App\Enums\ProductionJobCardStatus::Draft,
            'created_by' => $operator->id,
        ]);

        $this->actingAs($operator)
            ->get(ProductionOperatorMode::homeUrl($operator))
            ->assertOk()
            ->assertSee(__('Request materials'), false)
            ->assertSee('data-erp-modal-open', false);

        $this->actingAs($operator)
            ->getJson(route('admin.production.floor.panel', $jobCard))
            ->assertOk()
            ->assertJsonPath('links.job', route('admin.production.job-cards.show', [
                'jobCard' => $jobCard,
                'from' => 'production-floor',
            ]));

        $this->actingAs($operator)
            ->withHeaders(['Turbo-Frame' => 'erp-form-modal'])
            ->get(route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'from' => 'production-floor']))
            ->assertOk()
            ->assertSee('data-erp-form-modal-panel', false)
            ->assertSee($jobCard->job_card_number, false);
    }

    protected function userWithRole(string $role): User
    {
        $user = User::factory()->create([
            'company_id' => Company::query()->where('code', 'JANA')->value('id'),
            'default_branch_id' => Branch::query()->where('code', 'HQ')->value('id'),
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $user->assignRole(Role::findByName($role, 'web'));

        return $user;
    }
}
