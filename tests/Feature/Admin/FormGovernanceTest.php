<?php

namespace Tests\Feature\Admin;

use App\Models\Branch;
use App\Models\Company;
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
