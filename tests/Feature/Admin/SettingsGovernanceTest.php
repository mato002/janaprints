<?php

namespace Tests\Feature\Admin;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Platform\SystemSetting;
use App\Models\User;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\PlatformConfigurationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SettingsGovernanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        $this->seed(PlatformConfigurationSeeder::class);
    }

    public function test_guest_cannot_access_settings(): void
    {
        $this->get(route('admin.settings.index'))->assertRedirect(route('login'));
    }

    public function test_user_without_permission_cannot_view_settings(): void
    {
        $user = $this->userWithPermissions(['quotations.view']);

        $this->actingAs($user)
            ->get(route('admin.settings.show', 'quotation'))
            ->assertForbidden();
    }

    public function test_company_admin_can_view_settings_section(): void
    {
        $user = $this->userWithPermissions(['settings.view']);

        $this->actingAs($user)
            ->get(route('admin.settings.show', 'quotation'))
            ->assertOk()
            ->assertSee(__('Quotation validity (days)'));
    }

    public function test_settings_control_center_hub_uses_business_domains(): void
    {
        $user = $this->userWithPermissions(['settings.view']);

        $response = $this->actingAs($user)
            ->get(route('admin.settings.show', 'hub'));

        $response->assertOk()
            ->assertSee(__('Settings Control Center'))
            ->assertSee(__('Areas'))
            ->assertSee(__('All'))
            ->assertSee(__('Organization'))
            ->assertSee(__('Sales'))
            ->assertSee(__('Production'))
            ->assertSee(__('Inventory'))
            ->assertSee(__('Procurement'))
            ->assertSee(__('Finance'))
            ->assertSee(__('System'))
            ->assertSee(__('Grid'))
            ->assertSee(__('List'))
            ->assertSee(__('Roles & Access'))
            ->assertSee(__('Configured'))
            ->assertDontSee(__('items completed'));

        $this->assertDoesNotMatchRegularExpression('/\d+\s+keys/i', $response->getContent());
    }

    public function test_settings_hub_includes_finance_and_system_cards(): void
    {
        $user = $this->userWithPermissions(['settings.view']);

        $this->actingAs($user)
            ->get(route('admin.settings.show', 'hub'))
            ->assertOk()
            ->assertSee(__('Taxes'))
            ->assertSee(__('Production Workflow'))
            ->assertSee(__('Numbering'))
            ->assertSee(__('Pending Setup'));
    }

    public function test_viewer_cannot_update_settings(): void
    {
        $user = $this->userWithPermissions(['settings.view']);

        $this->actingAs($user)
            ->put(route('admin.settings.update', 'quotation'), [
                'settings' => [
                    'quotation_validity_days' => ['company' => 45],
                ],
            ])
            ->assertForbidden();
    }

    public function test_company_admin_can_save_company_override(): void
    {
        $user = $this->userWithPermissions(['settings.view', 'settings.manage']);
        $company = Company::query()->where('code', 'JANA')->firstOrFail();

        $this->actingAs($user)
            ->put(route('admin.settings.update', 'quotation'), [
                'settings' => [
                    'quotation_validity_days' => ['company' => 45],
                ],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('system_settings', [
            'company_id' => $company->id,
            'branch_id' => null,
            'key' => 'quotation_validity_days',
        ]);

        $setting = SystemSetting::query()
            ->where('company_id', $company->id)
            ->whereNull('branch_id')
            ->where('key', 'quotation_validity_days')
            ->first();

        $this->assertSame(45, $setting->value['data']);
    }

    public function test_branch_override_takes_precedence_in_effective_value(): void
    {
        $user = $this->userWithPermissions(['settings.view', 'settings.manage']);
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();

        SystemSetting::query()->updateOrCreate(
            ['company_id' => $company->id, 'branch_id' => null, 'key' => 'quotation_validity_days'],
            ['value' => ['data' => 30], 'value_type' => 'integer'],
        );

        $this->actingAs($user)
            ->put(route('admin.settings.update', 'quotation'), [
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'settings' => [
                    'quotation_validity_days' => ['branch' => 14],
                ],
            ])
            ->assertRedirect();

        $this->actingAs($user)
            ->get(route('admin.settings.show', ['section' => 'quotation', 'company_id' => $company->id, 'branch_id' => $branch->id]))
            ->assertOk()
            ->assertSee('14');
    }

    public function test_settings_permissions_exist(): void
    {
        $this->assertNotNull(Role::findByName('Super Admin', 'web')->permissions->firstWhere('name', 'settings.view'));
        $this->assertNotNull(Role::findByName('Super Admin', 'web')->permissions->firstWhere('name', 'settings.manage'));
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
            'is_active' => true,
        ]);

        $role = Role::create(['name' => 'Settings Tester', 'guard_name' => 'web']);
        $role->syncPermissions($permissions);
        $user->assignRole($role);

        return $user;
    }
}
