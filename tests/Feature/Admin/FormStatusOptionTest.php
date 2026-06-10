<?php

namespace Tests\Feature\Admin;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Platform\FormStatusOption;
use App\Models\User;
use App\Support\Platform\FormSettingsService;
use App\Support\Platform\FormStatusOptionService;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\PlatformConfigurationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FormStatusOptionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        $this->seed(PlatformConfigurationSeeder::class);
    }

    public function test_form_settings_marks_status_field_for_registry_forms(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $forms = app(FormSettingsService::class);

        $customerStatus = $forms->fieldConfig('customer', 'status', $company->id);

        $this->assertTrue($customerStatus['is_status_field']);
        $this->assertSame('select', $customerStatus['type']);
    }

    public function test_default_status_options_are_seeded_from_enum(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $service = app(FormStatusOptionService::class);

        $options = $service->optionsFor('customer', $company->id);

        $this->assertCount(3, $options);
        $this->assertTrue($options->every(fn (FormStatusOption $option) => $option->is_system));
        $this->assertSame(
            ['active', 'inactive', 'prospect'],
            $options->pluck('value')->all(),
        );
    }

    public function test_company_admin_can_add_custom_customer_status(): void
    {
        $user = $this->userWithPermissions(['settings.view', 'settings.manage']);
        $company = Company::query()->where('code', 'JANA')->firstOrFail();

        $this->actingAs($user)
            ->put(route('admin.settings.forms.update', ['company_id' => $company->id]), [
                'return_form' => 'customer',
                'forms' => [
                    'customer' => [
                        'is_active' => 1,
                        'status_options' => [
                            ['value' => 'active', 'label' => 'Active', 'is_active' => 1],
                            ['value' => 'inactive', 'label' => 'Inactive', 'is_active' => 1],
                            ['value' => 'prospect', 'label' => 'Prospect', 'is_active' => 1],
                            ['value' => 'on_hold', 'label' => 'On hold', 'is_active' => 1],
                        ],
                    ],
                ],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('form_status_options', [
            'company_id' => $company->id,
            'form_key' => 'customer',
            'value' => 'on_hold',
            'label' => 'On hold',
            'is_system' => false,
        ]);
    }

    public function test_customer_create_page_renders_custom_status_option(): void
    {
        $user = $this->userWithPermissions(['crm.customers.view', 'crm.customers.create']);
        $company = Company::query()->where('code', 'JANA')->firstOrFail();

        app(FormStatusOptionService::class)->syncOptions('customer', $company->id, null, [
            ['value' => 'active', 'label' => 'Active', 'is_active' => 1],
            ['value' => 'inactive', 'label' => 'Inactive', 'is_active' => 1],
            ['value' => 'prospect', 'label' => 'Prospect', 'is_active' => 1],
            ['value' => 'vip', 'label' => 'VIP', 'is_active' => 1],
        ]);

        session([
            'active_company_id' => $company->id,
            'active_branch_id' => Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->value('id'),
        ]);

        $this->actingAs($user)
            ->get(route('admin.crm.customers.create'))
            ->assertOk()
            ->assertSee('value="vip"', false)
            ->assertSee('VIP', false);
    }

    public function test_sync_persists_custom_status_option(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $service = app(FormStatusOptionService::class);

        $service->syncOptions('customer', $company->id, null, [
            ['value' => 'active', 'label' => 'Active', 'is_active' => 1],
            ['value' => 'inactive', 'label' => 'Inactive', 'is_active' => 1],
            ['value' => 'prospect', 'label' => 'Prospect', 'is_active' => 1],
            ['value' => 'vip', 'label' => 'VIP', 'is_active' => 1],
        ]);

        $this->assertDatabaseHas('form_status_options', [
            'company_id' => $company->id,
            'form_key' => 'customer',
            'value' => 'vip',
            'label' => 'VIP',
        ]);

        $this->assertTrue(
            $service->optionsFor('customer', $company->id)->contains(fn (FormStatusOption $option) => $option->value === 'vip'),
        );
    }

    public function test_customer_can_be_saved_with_custom_status(): void
    {
        $user = $this->userWithPermissions(['crm.customers.view', 'crm.customers.create']);
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();

        app(FormStatusOptionService::class)->syncOptions('customer', $company->id, null, [
            ['value' => 'active', 'label' => 'Active', 'is_active' => 1],
            ['value' => 'inactive', 'label' => 'Inactive', 'is_active' => 1],
            ['value' => 'prospect', 'label' => 'Prospect', 'is_active' => 1],
            ['value' => 'vip', 'label' => 'VIP', 'is_active' => 1],
        ]);

        session([
            'active_company_id' => $company->id,
            'active_branch_id' => $branch->id,
        ]);

        $response = $this->actingAs($user)
            ->post(route('admin.crm.customers.store'), [
                'customer_type' => 'corporate',
                'company_name' => 'VIP Customer Ltd',
                'kra_pin' => 'A123456789Z',
                'status' => 'vip',
            ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $customer = Customer::query()->where('company_name', 'VIP Customer Ltd')->firstOrFail();

        $this->assertSame('vip', $customer->status);
        $this->assertSame('vip', FormStatusOptionService::valueOf($customer->status));
    }

    public function test_form_settings_page_shows_status_options_panel_for_customer_form(): void
    {
        $user = $this->userWithPermissions(['settings.view', 'settings.manage']);
        $company = Company::query()->where('code', 'JANA')->firstOrFail();

        $this->actingAs($user)
            ->get(route('admin.settings.forms.index', ['form' => 'customer', 'company_id' => $company->id]))
            ->assertOk()
            ->assertSee(__('Status dropdown options'), false)
            ->assertSee('value="active"', false);
    }

    public function test_customer_create_shows_status_plus_button_for_settings_manager(): void
    {
        $user = $this->userWithPermissions(['crm.customers.view', 'crm.customers.create', 'settings.manage']);
        $company = Company::query()->where('code', 'JANA')->firstOrFail();

        session([
            'active_company_id' => $company->id,
            'active_branch_id' => Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->value('id'),
        ]);

        $this->actingAs($user)
            ->withHeader('Turbo-Frame', 'erp-form-modal')
            ->get(route('admin.crm.customers.create'))
            ->assertOk()
            ->assertSee('erp-lookup-select__add', false)
            ->assertSee('scopeFormKey', false)
            ->assertSee('customer', false);
    }

    public function test_form_status_quick_create_form_renders_in_lookup_panel(): void
    {
        $user = $this->userWithPermissions(['settings.manage']);
        $company = Company::query()->where('code', 'JANA')->firstOrFail();

        session([
            'active_company_id' => $company->id,
            'active_branch_id' => Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->value('id'),
        ]);

        $this->actingAs($user)
            ->withHeader('X-Erp-Lookup-Create', '1')
            ->get(route('admin.form-statuses.quick-create', ['form_key' => 'customer', 'company_id' => $company->id]))
            ->assertOk()
            ->assertSee('data-erp-lookup-modal-panel', false)
            ->assertSee('data-erp-lookup-form', false)
            ->assertSee('name="value"', false)
            ->assertSee('name="label"', false);
    }

    public function test_form_status_quick_create_returns_json(): void
    {
        $user = $this->userWithPermissions(['settings.manage']);
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();

        session([
            'active_company_id' => $company->id,
            'active_branch_id' => $branch->id,
        ]);

        $response = $this->actingAs($user)
            ->withHeaders(['X-Erp-Lookup-Create' => '1', 'Accept' => 'application/json'])
            ->post(route('admin.form-statuses.quick-store'), [
                '_erp_lookup_create' => 1,
                'form_key' => 'customer',
                'company_id' => $company->id,
                'value' => 'on_hold',
                'label' => 'On hold',
            ]);

        $response->assertOk()
            ->assertJsonPath('value', 'on_hold')
            ->assertJsonPath('label', 'On hold');

        $this->assertDatabaseHas('form_status_options', [
            'company_id' => $company->id,
            'form_key' => 'customer',
            'value' => 'on_hold',
            'label' => 'On hold',
        ]);
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

        $role = Role::create(['name' => 'Form Status Tester', 'guard_name' => 'web']);
        $role->syncPermissions($permissions);
        $user->assignRole($role);

        return $user;
    }
}
