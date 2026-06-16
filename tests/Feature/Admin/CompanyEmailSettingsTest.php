<?php

namespace Tests\Feature\Admin;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\PlatformConfigurationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CompanyEmailSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        $this->seed(PlatformConfigurationSeeder::class);

        config([
            'mailboxes.domain' => 'janaprints.co.ke',
            'mailboxes.cpanel.host' => 'cpanel.example.com',
            'mailboxes.cpanel.username' => 'janaprints',
            'mailboxes.cpanel.api_token' => 'test-token',
            'mailboxes.cpanel.port' => 2083,
            'mailboxes.cpanel.verify_ssl' => false,
        ]);
    }

    public function test_settings_hub_shows_company_email_card(): void
    {
        $user = $this->userWithPermissions(['settings.view']);

        $this->actingAs($user)
            ->get(route('admin.settings.show', 'hub'))
            ->assertOk()
            ->assertSee(__('Company Email'))
            ->assertSee(__('Create and manage company mailboxes through cPanel from the dashboard.'));
    }

    public function test_company_email_index_lists_cpanel_mailboxes(): void
    {
        Http::fake([
            'https://cpanel.example.com:2083/execute/Email/list_pops_with_disk*' => Http::response([
                'status' => 1,
                'data' => [
                    [
                        'email' => 'info@janaprints.co.ke',
                        'login' => 'info@janaprints.co.ke',
                        'diskused' => '0.08',
                        'diskquota' => '250.00',
                        'diskusedpercent_float' => 0.03,
                        '_diskused' => '82627',
                        '_diskquota' => '262144000',
                        'suspended_login' => 0,
                    ],
                ],
            ]),
        ]);

        $user = $this->userWithPermissions(['settings.view']);

        $this->actingAs($user)
            ->get(route('admin.settings.company-email.index'))
            ->assertOk()
            ->assertSee('info@janaprints.co.ke', false)
            ->assertSee(__('Configured'))
            ->assertDontSee('test-token', false);
    }

    public function test_manager_can_create_mailbox(): void
    {
        Http::fake([
            'https://cpanel.example.com:2083/execute/Email/add_pop*' => Http::response(['status' => 1, 'data' => []]),
            'https://cpanel.example.com:2083/execute/Email/list_pops_with_disk*' => Http::response([
                'status' => 1,
                'data' => [
                    [
                        'email' => 'sales@janaprints.co.ke',
                        'login' => 'sales@janaprints.co.ke',
                        'diskused' => 0,
                        'diskquota' => 262144000,
                        'suspended_login' => 0,
                    ],
                ],
            ]),
        ]);

        $user = $this->userWithPermissions(['settings.view', 'settings.manage']);

        $this->actingAs($user)
            ->post(route('admin.settings.company-email.store'), [
                'local_part' => 'sales',
                'password' => 'SecurePass123!',
                'password_confirmation' => 'SecurePass123!',
            ])
            ->assertRedirect(route('admin.settings.company-email.index'));

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/execute/Email/add_pop')
                && $request['email'] === 'sales@janaprints.co.ke'
                && $request['password'] === 'SecurePass123!';
        });
    }

    public function test_manager_can_update_mailbox_password(): void
    {
        Http::fake([
            'https://cpanel.example.com:2083/execute/Email/passwd_pop*' => Http::response(['status' => 1, 'data' => []]),
            'https://cpanel.example.com:2083/execute/Email/list_pops_with_disk*' => Http::response([
                'status' => 1,
                'data' => [
                    [
                        'email' => 'info@janaprints.co.ke',
                        'login' => 'info@janaprints.co.ke',
                        'diskused' => '0.08',
                        'diskquota' => '250.00',
                        'suspended_login' => 0,
                    ],
                ],
            ]),
        ]);

        $user = $this->userWithPermissions(['settings.view', 'settings.manage']);

        $this->actingAs($user)
            ->from(route('admin.settings.company-email.show', ['address' => 'info@janaprints.co.ke']))
            ->put(route('admin.settings.company-email.update-password'), [
                'address' => 'info@janaprints.co.ke',
                'password' => 'NewSecure123!',
                'password_confirmation' => 'NewSecure123!',
            ])
            ->assertRedirect(route('admin.settings.company-email.show', ['address' => 'info@janaprints.co.ke']));

        Http::assertSent(fn ($request) => str_contains($request->url(), '/execute/Email/passwd_pop'));
    }

    public function test_manager_can_update_mailbox_quota(): void
    {
        Http::fake([
            'https://cpanel.example.com:2083/execute/Email/edit_pop_quota*' => Http::response(['status' => 1, 'data' => []]),
        ]);

        $user = $this->userWithPermissions(['settings.view', 'settings.manage']);

        $this->actingAs($user)
            ->put(route('admin.settings.company-email.update-quota'), [
                'address' => 'info@janaprints.co.ke',
                'quota_mb' => 512,
            ])
            ->assertRedirect(route('admin.settings.company-email.show', ['address' => 'info@janaprints.co.ke']));

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/execute/Email/edit_pop_quota')
                && $request['email'] === 'info'
                && $request['domain'] === 'janaprints.co.ke'
                && (int) $request['quota'] === 512;
        });
    }

    public function test_manager_can_set_unlimited_mailbox_quota(): void
    {
        Http::fake([
            'https://cpanel.example.com:2083/execute/Email/edit_pop_quota*' => Http::response(['status' => 1, 'data' => []]),
        ]);

        $user = $this->userWithPermissions(['settings.view', 'settings.manage']);

        $this->actingAs($user)
            ->put(route('admin.settings.company-email.update-quota'), [
                'address' => 'info@janaprints.co.ke',
                'unlimited_quota' => '1',
            ])
            ->assertRedirect(route('admin.settings.company-email.show', ['address' => 'info@janaprints.co.ke']));

        Http::assertSent(fn ($request) => str_contains($request->url(), '/execute/Email/edit_pop_quota')
            && (int) $request['quota'] === 0);
    }

    public function test_create_page_renders_modal_form_panel(): void
    {
        $user = $this->userWithPermissions(['settings.view', 'settings.manage']);

        $this->actingAs($user)
            ->withHeader('Turbo-Frame', 'erp-form-modal')
            ->get(route('admin.settings.company-email.create'))
            ->assertOk()
            ->assertSee('data-erp-form-modal-panel', false)
            ->assertSee(__('Mailbox name'), false)
            ->assertSee(__('Create mailbox'), false)
            ->assertDontSee(__('Branch scope'), false);
    }

    public function test_viewer_cannot_create_mailbox(): void
    {
        $user = $this->userWithPermissions(['settings.view']);

        $this->actingAs($user)
            ->get(route('admin.settings.company-email.create'))
            ->assertForbidden();
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

        $role = Role::create(['name' => 'Company Email Tester', 'guard_name' => 'web']);
        $role->syncPermissions($permissions);
        $user->assignRole($role);

        session([
            'active_company_id' => $company->id,
            'active_branch_id' => $branch->id,
        ]);

        return $user;
    }
}
