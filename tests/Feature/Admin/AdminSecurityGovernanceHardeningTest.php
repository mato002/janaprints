<?php

namespace Tests\Feature\Admin;

use App\Models\Crm\Customer;
use App\Models\User;
use App\Support\Platform\SettingsControlCenterPresenter;
use App\Support\Platform\SettingsRegistry;
use App\Support\Platform\SystemSettingsManager;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\PlatformConfigurationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminSecurityGovernanceHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        $this->seed(PlatformConfigurationSeeder::class);
    }

    public function test_permission_routes_are_registered(): void
    {
        $this->assertTrue(Route::has('admin.access-control.matrix'));
        $this->assertTrue(Route::has('admin.roles.permissions.edit'));
        $this->assertTrue(Route::has('admin.roles.permissions.update'));
        $this->assertTrue(Route::has('admin.permissions.index'));
    }

    public function test_permission_matrix_and_role_permissions_edit_load(): void
    {
        $admin = $this->companyAdmin();

        $this->actingAs($admin)
            ->get(route('admin.access-control.matrix', ['role' => 'Sales']))
            ->assertOk()
            ->assertSee(__('Permission Matrix'));

        $role = Role::findByName('Viewer', 'web');

        $this->actingAs($admin)
            ->get(route('admin.roles.permissions.edit', $role))
            ->assertRedirect(route('admin.roles.show', $role));
    }

    public function test_permissions_index_redirects_to_permission_matrix(): void
    {
        $admin = $this->companyAdmin();

        $this->actingAs($admin)
            ->get(route('admin.permissions.index'))
            ->assertRedirect(route('admin.access-control.matrix'));
    }

    #[DataProvider('hardenedAdminRouteProvider')]
    public function test_client_session_cannot_access_hardened_admin_routes(string $routeName, array $parameters = []): void
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->create([
            'company_id' => $customer->company_id,
            'customer_id' => $customer->id,
            'employee_id' => null,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $user->assignRole('Company Admin');

        $response = $this->withSession(['auth_context' => 'client'])
            ->actingAs($user)
            ->get(route($routeName, $parameters));

        $response->assertRedirect(route('admin.login', absolute: false));
        $this->assertGuest();
    }

    /**
     * @return array<string, array{0: string, 1?: array<string, mixed>}>
     */
    public static function hardenedAdminRouteProvider(): array
    {
        return [
            'settings index' => ['admin.settings.index'],
            'settings hub' => ['admin.settings.show', ['section' => 'hub']],
            'governance delegations' => ['admin.governance.delegations.index'],
            'governance workflow rules' => ['admin.governance.workflow-rules.index'],
            'integrations email' => ['admin.integrations.email.index'],
            'integrations api keys' => ['admin.integrations.api-keys.index'],
        ];
    }

    public function test_settings_control_center_live_cards_resolve_to_routes(): void
    {
        $company = \App\Models\Company::query()->where('code', 'JANA')->firstOrFail();

        $presenter = new SettingsControlCenterPresenter(
            app(SettingsRegistry::class),
            app(SystemSettingsManager::class),
        );

        $payload = $presenter->hub($company->id, null);
        $cards = collect($payload['cards'])->keyBy('id');

        $liveCards = [
            'customers' => 'admin.crm.customers.index',
            'machine-configuration' => 'admin.assets.machines.index',
            'warehouses' => 'admin.inventory.warehouses.index',
            'inventory-categories' => 'admin.inventory.catalogue.categories.index',
            'units-of-measure' => 'admin.inventory.catalogue.units.index',
            'approval-chains' => 'admin.governance.chains.index',
            'notifications' => 'admin.communications.notifications.index',
            'audit-settings' => 'admin.operations.retention.index',
            'integrations' => 'admin.integrations.providers.index',
            'api-settings' => 'admin.integrations.api-keys.index',
        ];

        foreach ($liveCards as $cardId => $routeName) {
            $card = $cards->get($cardId);

            $this->assertNotNull($card, "Missing control center card: {$cardId}");
            $this->assertFalse($card['comingSoon'], "Card {$cardId} should not be coming soon");
            $this->assertNotEmpty($card['href'], "Card {$cardId} should have a href");
            $this->assertStringStartsWith(route($routeName), $card['href']);
        }
    }

    public function test_settings_control_center_still_marks_unbuilt_cards_as_pending(): void
    {
        $company = \App\Models\Company::query()->where('code', 'JANA')->firstOrFail();

        $presenter = new SettingsControlCenterPresenter(
            app(SettingsRegistry::class),
            app(SystemSettingsManager::class),
        );

        $payload = $presenter->hub($company->id, null);
        $cards = collect($payload['cards'])->keyBy('id');

        foreach (['cost-centers', 'vendor-evaluation'] as $cardId) {
            $card = $cards->get($cardId);

            $this->assertNotNull($card, "Missing control center card: {$cardId}");
            $this->assertTrue($card['comingSoon'], "Card {$cardId} should remain pending setup");
            $this->assertNull($card['href']);
        }
    }

    public function test_administration_workspace_integrations_section_links_to_live_routes(): void
    {
        $admin = $this->companyAdmin();

        $this->actingAs($admin)
            ->get(route('admin.workspaces.administration.section', ['section' => 'integrations']))
            ->assertOk()
            ->assertSee(route('admin.integrations.email.index', absolute: false), false)
            ->assertSee(route('admin.integrations.api-keys.index', absolute: false), false)
            ->assertSee(route('admin.integrations.providers.index', absolute: false), false);
    }

    protected function companyAdmin(): User
    {
        $user = User::factory()->create([
            'company_id' => 1,
            'default_branch_id' => 1,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $user->assignRole('Company Admin');

        return $user;
    }
}
