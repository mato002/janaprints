<?php

namespace Tests\Feature\Admin;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Platform\FormFieldSetting;
use App\Models\Platform\FormSetting;
use App\Models\User;
use App\Support\Platform\FormSettingsManager;
use App\Support\Platform\FormSettingsService;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\PlatformConfigurationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FormGovernanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        $this->seed(PlatformConfigurationSeeder::class);
    }

    public function test_forms_admin_page_is_accessible(): void
    {
        $user = $this->userWithPermissions(['settings.view']);

        $this->actingAs($user)
            ->get(route('admin.settings.forms.index'))
            ->assertOk()
            ->assertSee(__('Forms Control Center'))
            ->assertSee(__('Total Forms'))
            ->assertSee(__('Configuration Health'))
            ->assertSee(__('Recently Modified'))
            ->assertSee(__('Commercial'))
            ->assertSee(__('Supply Chain'))
            ->assertSee(__('Planned Forms'))
            ->assertSee(__('Customers'));

        $this->actingAs($user)
            ->get(route('admin.settings.forms.index', ['form' => 'customer']))
            ->assertOk()
            ->assertSee(__('KRA PIN'));
    }

    public function test_seeded_customer_field_rules_apply(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $forms = app(FormSettingsService::class);

        $this->assertTrue($forms->isRequired('customer', 'kra_pin', $company->id));
        $this->assertFalse($forms->isVisible('customer', 'website', $company->id));
        $this->assertTrue($forms->isRequired('lead', 'estimated_value', $company->id));
        $this->assertTrue($forms->isRequired('artwork', 'due_date', $company->id));
    }

    public function test_view_only_user_sees_permission_notice_without_save_button(): void
    {
        $user = $this->userWithPermissions(['settings.view']);

        $this->actingAs($user)
            ->get(route('admin.settings.forms.index', ['form' => 'customer']))
            ->assertOk()
            ->assertSee(__('View only'))
            ->assertDontSee(__('Save form settings'));
    }

    public function test_company_admin_can_add_custom_field(): void
    {
        $user = $this->userWithPermissions(['settings.view', 'settings.manage']);
        $company = Company::query()->where('code', 'JANA')->firstOrFail();

        $this->actingAs($user)
            ->get(route('admin.settings.forms.index', ['form' => 'customer', 'company_id' => $company->id]))
            ->assertOk()
            ->assertSee(__('Add custom field'))
            ->assertSee('id="add-custom-field"', false);

        $this->actingAs($user)
            ->put(route('admin.settings.forms.update'), [
                'company_id' => $company->id,
                'return_form' => 'customer',
                'forms' => [
                    'customer' => [
                        'is_active' => '1',
                        'add_field' => [
                            'field_key' => 'tax_id',
                            'label' => 'Tax ID',
                            'type' => 'text',
                        ],
                    ],
                ],
            ])
            ->assertRedirect(route('admin.settings.forms.index', [
                'company_id' => $company->id,
                'branch_id' => $user->default_branch_id,
                'form' => 'customer',
            ]));

        $config = app(FormSettingsService::class)->fieldConfig('customer', 'tax_id', $company->id, null, true);

        $this->assertTrue($config['is_custom']);
        $this->assertSame('Tax ID', $config['label']);

        $resolved = app(FormSettingsService::class)->resolvedFields('customer', $company->id);
        $this->assertArrayHasKey('tax_id', $resolved);
        $this->assertTrue($resolved['tax_id']['is_custom']);
    }

    public function test_form_settings_validation_failure_keeps_active_form_redirect(): void
    {
        $user = $this->userWithPermissions(['settings.view', 'settings.manage']);
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();

        $this->actingAs($user)
            ->from(route('admin.settings.forms.index', [
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'form' => 'customer',
            ]))
            ->put(route('admin.settings.forms.update'), [
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'return_form' => 'customer',
                'forms' => 'invalid',
            ])
            ->assertRedirect(route('admin.settings.forms.index', [
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'form' => 'customer',
            ]));
    }

    public function test_form_settings_update_returns_turbo_frame_response(): void
    {
        $user = $this->userWithPermissions(['settings.view', 'settings.manage']);
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();

        $this->actingAs($user)
            ->put(route('admin.settings.forms.update'), [
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'return_form' => 'customer',
                'forms' => [
                    'customer' => [
                        'is_active' => '1',
                        'fields' => [
                            'email' => [
                                'visibility' => 'hidden',
                                'requirement' => 'optional',
                                'read_only' => '0',
                                'default_value' => '',
                            ],
                        ],
                        'add_field' => [
                            'field_key' => '',
                            'label' => '',
                            'type' => 'text',
                        ],
                    ],
                ],
            ], ['Turbo-Frame' => 'erp-main'])
            ->assertOk()
            ->assertSee('data-erp-flash-status', false)
            ->assertSee(__('Form settings updated.'))
            ->assertSee('id="erp-main"', false)
            ->assertSee(__('KRA PIN'));

        $forms = app(FormSettingsService::class);

        $this->assertFalse($forms->isVisible('customer', 'email', $company->id, $branch->id));
    }

    public function test_company_admin_can_save_form_field_settings(): void
    {
        $user = $this->userWithPermissions(['settings.view', 'settings.manage']);
        $company = Company::query()->where('code', 'JANA')->firstOrFail();

        $this->actingAs($user)
            ->put(route('admin.settings.forms.update'), [
                'company_id' => $company->id,
                'forms' => [
                    'customer' => [
                        'is_active' => '1',
                        'fields' => [
                            'kra_pin' => [
                                'visibility' => 'visible',
                                'requirement' => 'optional',
                                'read_only' => '1',
                                'default_value' => 'A0000000',
                            ],
                        ],
                    ],
                ],
            ])
            ->assertRedirect(route('admin.settings.forms.index', [
                'company_id' => $company->id,
                'branch_id' => $user->default_branch_id,
                'form' => 'customer',
            ]));

        $forms = app(FormSettingsService::class);

        $this->assertFalse($forms->isRequired('customer', 'kra_pin', $company->id));
        $this->assertTrue($forms->isReadOnly('customer', 'kra_pin', $company->id));
        $this->assertSame('A0000000', $forms->defaultValue('customer', 'kra_pin', null, $company->id));
    }

    public function test_hidden_fields_are_prohibited_in_validation(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $forms = app(FormSettingsService::class);

        $rules = $forms->mergeValidationRules('customer', [
            'website' => ['string', 'max:255'],
        ], $company->id);

        $this->assertSame(['prohibited'], $rules['website']);
    }

    public function test_registry_fallback_when_no_database_override(): void
    {
        $company = Company::query()->create([
            'code' => 'NEWCO',
            'name' => 'New Company',
            'is_active' => true,
        ]);

        $forms = app(FormSettingsService::class);

        $this->assertTrue($forms->isRequired('customer', 'company_name', $company->id));
        $this->assertTrue($forms->isVisible('customer', 'website', $company->id));
        $this->assertSame('KES', $forms->defaultValue('quotation', 'currency', null, $company->id));
    }

    public function test_branch_inherits_company_form_settings(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();
        $forms = app(FormSettingsService::class);

        $this->assertFalse($forms->isVisible('customer', 'website', $company->id, $branch->id));
        $this->assertTrue($forms->fieldConfig('customer', 'website', $company->id, $branch->id)['inherits_company']);
    }

    public function test_apply_defaults_fills_configured_values(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $forms = app(FormSettingsService::class);

        $data = $forms->applyDefaults('quotation', [
            'customer_id' => 1,
            'quotation_date' => '2026-06-01',
        ], $company->id);

        $this->assertSame('KES', $data['currency']);
    }

    public function test_required_fields_are_always_visible_in_resolved_config(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $manager = app(FormSettingsManager::class);

        $manager->save($company->id, null, [
            'customer' => [
                'is_active' => '1',
                'fields' => [
                    'phone' => [
                        'visibility' => 'hidden',
                        'requirement' => 'required',
                        'read_only' => '0',
                        'default_value' => '',
                    ],
                ],
            ],
        ]);

        $config = app(FormSettingsService::class)->fieldConfig('customer', 'phone', $company->id);

        $this->assertTrue($config['required']);
        $this->assertTrue($config['visible']);
        $this->assertFalse($config['hidden']);
    }

    public function test_registry_fields_are_never_marked_as_custom(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $companyForm = FormSetting::query()
            ->where('company_id', $company->id)
            ->whereNull('branch_id')
            ->where('form_key', 'warehouse.create')
            ->firstOrFail();

        $nameField = $companyForm->fields()->where('field_key', 'name')->firstOrFail();
        $nameField->update([
            'default_value' => [
                'data' => null,
                'read_only' => false,
                'custom' => true,
                'label' => 'name',
                'type' => 'text',
            ],
        ]);

        $config = app(FormSettingsService::class)->fieldConfig('warehouse.create', 'name', $company->id);

        $this->assertFalse($config['is_custom']);
        $this->assertSame('Warehouse name', $config['label']);
    }

    public function test_branch_registry_defaults_inherit_company_overrides(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();
        $forms = app(FormSettingsService::class);

        $companyForm = FormSetting::query()
            ->where('company_id', $company->id)
            ->whereNull('branch_id')
            ->where('form_key', 'customer')
            ->firstOrFail();

        $branchForm = FormSetting::query()->firstOrCreate(
            [
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'form_key' => 'customer',
            ],
            ['is_active' => true],
        );

        foreach (config('form_registry.forms.customer.fields', []) as $fieldKey => $registry) {
            FormFieldSetting::query()->updateOrCreate(
                [
                    'form_setting_id' => $branchForm->id,
                    'field_key' => $fieldKey,
                ],
                [
                    'is_required' => (bool) ($registry['required'] ?? false),
                    'is_visible' => (bool) ($registry['visible'] ?? true),
                    'is_hidden' => ! ($registry['visible'] ?? true),
                    'sort_order' => (int) ($registry['sort_order'] ?? 0),
                ],
            );
        }

        FormFieldSetting::query()->updateOrCreate(
            [
                'form_setting_id' => $companyForm->id,
                'field_key' => 'contact_person',
            ],
            [
                'is_required' => false,
                'is_visible' => false,
                'is_hidden' => true,
                'sort_order' => 3,
            ],
        );

        $config = $forms->fieldConfig('customer', 'contact_person', $company->id, $branch->id);

        $this->assertFalse($config['visible']);
        $this->assertTrue($config['hidden']);
        $this->assertTrue($config['inherits_company']);
    }

    public function test_registry_required_system_fields_cannot_be_saved_as_optional(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $manager = app(FormSettingsManager::class);

        $manager->save($company->id, null, [
            'customer' => [
                'is_active' => '1',
                'fields' => [
                    'company_name' => [
                        'visibility' => 'hidden',
                        'requirement' => 'optional',
                        'read_only' => '0',
                        'default_value' => '',
                    ],
                ],
            ],
        ]);

        $config = app(FormSettingsService::class)->fieldConfig('customer', 'company_name', $company->id);

        $this->assertTrue($config['required']);
        $this->assertTrue($config['visible']);
        $this->assertTrue($config['registry_required']);
    }

    public function test_ensure_forms_does_not_seed_branch_field_defaults(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();
        $manager = app(FormSettingsManager::class);

        $manager->ensureForms($company->id, $branch->id);

        $branchForm = FormSetting::query()
            ->where('company_id', $company->id)
            ->where('branch_id', $branch->id)
            ->where('form_key', 'customer')
            ->first();

        $this->assertNull($branchForm);
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

        $role = Role::create(['name' => 'Form Tester', 'guard_name' => 'web']);
        $role->syncPermissions($permissions);
        $user->assignRole($role);

        return $user;
    }
}
