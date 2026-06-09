<?php

namespace Tests\Feature\Admin;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Platform\SystemSetting;
use App\Models\User;
use App\Support\Platform\SettingsRegistry;
use App\Support\Platform\SystemSettingsManager;
use App\Support\Platform\SystemSettingsService;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\PlatformConfigurationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SettingsRegistryUnificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        $this->seed(PlatformConfigurationSeeder::class);
    }

    public function test_registry_includes_unified_domain_sections(): void
    {
        $registry = app(SettingsRegistry::class);

        foreach (['accounting', 'procurement', 'hr', 'tax', 'communications', 'operations'] as $slug) {
            $this->assertTrue($registry->hasSection($slug), "Missing settings section [{$slug}]");
        }
    }

    public function test_registry_definitions_are_structurally_valid(): void
    {
        $registry = app(SettingsRegistry::class);

        foreach (['accounting', 'procurement', 'hr', 'tax', 'communications', 'operations'] as $slug) {
            $section = $registry->section($slug);

            $this->assertNotEmpty($section['label']);
            $this->assertNotEmpty($section['settings'], "Section [{$slug}] must define settings");

            foreach ($section['settings'] as $key => $definition) {
                $this->assertIsString($key);
                $this->assertNotEmpty($definition['label'], "Setting [{$key}] requires a label");
                $this->assertContains($definition['type'], ['string', 'integer', 'float', 'boolean'], "Setting [{$key}] has invalid type");
                $this->assertNotEmpty($definition['scopes'], "Setting [{$key}] requires scopes");
                $this->assertArrayHasKey('default', $definition, "Setting [{$key}] requires a default");
            }
        }
    }

    public function test_registry_exposes_expected_setting_groups(): void
    {
        $registry = app(SettingsRegistry::class);

        $this->assertArrayHasKey('accounting_fiscal_year_start_month', $registry->section('accounting')['settings']);
        $this->assertArrayHasKey('accounting_posting_rules_enforced', $registry->section('accounting')['settings']);
        $this->assertArrayHasKey('accounting_default_receivables_account', $registry->section('accounting')['settings']);
        $this->assertArrayHasKey('accounting_period_close_locks_gl', $registry->section('accounting')['settings']);

        $this->assertArrayHasKey('procurement_approval_threshold_amount', $registry->section('procurement')['settings']);
        $this->assertArrayHasKey('procurement_supplier_onboarding_approval', $registry->section('procurement')['settings']);
        $this->assertArrayHasKey('procurement_po_default_delivery_days', $registry->section('procurement')['settings']);

        $this->assertArrayHasKey('hr_attendance_grace_minutes', $registry->section('hr')['settings']);
        $this->assertArrayHasKey('hr_leave_require_manager_approval', $registry->section('hr')['settings']);
        $this->assertArrayHasKey('hr_payroll_default_frequency', $registry->section('hr')['settings']);
        $this->assertArrayHasKey('hr_training_renewal_reminder_days', $registry->section('hr')['settings']);

        $this->assertArrayHasKey('tax_vat_enabled', $registry->section('tax')['settings']);
        $this->assertArrayHasKey('tax_withholding_enabled', $registry->section('tax')['settings']);
        $this->assertArrayHasKey('tax_default_code', $registry->section('tax')['settings']);

        $this->assertArrayHasKey('communications_email_notifications_enabled', $registry->section('communications')['settings']);
        $this->assertArrayHasKey('operations_audit_retention_days', $registry->section('operations')['settings']);
    }

    public function test_unknown_registry_section_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);

        app(SettingsRegistry::class)->section('nonexistent-section');
    }

    public function test_unified_sections_render_in_settings_ui(): void
    {
        $user = $this->userWithPermissions(['settings.view']);

        foreach (['accounting', 'procurement', 'hr', 'tax', 'communications', 'operations'] as $section) {
            $this->actingAs($user)
                ->get(route('admin.settings.show', $section))
                ->assertOk()
                ->assertSee($this->registry()->section($section)['label']);
        }
    }

    public function test_unknown_settings_section_returns_not_found(): void
    {
        $user = $this->userWithPermissions(['settings.view']);

        $this->actingAs($user)
            ->get(route('admin.settings.show', 'nonexistent-section'))
            ->assertNotFound();
    }

    public function test_company_override_persists_for_accounting_section(): void
    {
        $user = $this->userWithPermissions(['settings.view', 'settings.manage']);
        $company = Company::query()->where('code', 'JANA')->firstOrFail();

        $this->actingAs($user)
            ->put(route('admin.settings.update', 'accounting'), [
                'settings' => [
                    'accounting_fiscal_periods_per_year' => ['company' => 13],
                    'accounting_default_receivables_account' => ['company' => '1210'],
                ],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('system_settings', [
            'company_id' => $company->id,
            'branch_id' => null,
            'key' => 'accounting_fiscal_periods_per_year',
        ]);

        $setting = SystemSetting::query()
            ->where('company_id', $company->id)
            ->whereNull('branch_id')
            ->where('key', 'accounting_fiscal_periods_per_year')
            ->first();

        $this->assertSame(13, $setting->value['data']);
    }

    public function test_branch_override_takes_precedence_for_procurement_section(): void
    {
        $user = $this->userWithPermissions(['settings.view', 'settings.manage']);
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();

        SystemSetting::query()->updateOrCreate(
            ['company_id' => $company->id, 'branch_id' => null, 'key' => 'procurement_approval_threshold_amount'],
            ['value' => ['data' => 50000], 'value_type' => 'integer'],
        );

        $this->actingAs($user)
            ->put(route('admin.settings.update', 'procurement'), [
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'settings' => [
                    'procurement_approval_threshold_amount' => ['branch' => 25000],
                ],
            ])
            ->assertRedirect();

        $rows = app(SystemSettingsManager::class)->rowsForSection('procurement', $company->id, $branch->id);
        $threshold = $rows->firstWhere('key', 'procurement_approval_threshold_amount');

        $this->assertSame(50000, $threshold['company_value']);
        $this->assertSame(25000, $threshold['branch_value']);
        $this->assertSame(25000, $threshold['effective_value']);
    }

    public function test_branch_inherits_company_value_when_no_branch_override(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();

        SystemSetting::query()->updateOrCreate(
            ['company_id' => $company->id, 'branch_id' => null, 'key' => 'tax_vat_default_rate'],
            ['value' => ['data' => 18], 'value_type' => 'integer'],
        );

        $effective = app(SystemSettingsService::class)->get(
            'tax_vat_default_rate',
            null,
            $company->id,
            $branch->id,
        );

        $this->assertSame(18, $effective);

        $rows = app(SystemSettingsManager::class)->rowsForSection('tax', $company->id, $branch->id);
        $vatRate = $rows->firstWhere('key', 'tax_vat_default_rate');

        $this->assertSame(18, $vatRate['company_value']);
        $this->assertNull($vatRate['branch_value']);
        $this->assertSame(18, $vatRate['effective_value']);
        $this->assertFalse($vatRate['has_branch_override']);
    }

    public function test_registry_defaults_apply_without_overrides(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();

        $rows = app(SystemSettingsManager::class)->rowsForSection('operations', $company->id, null);
        $retention = $rows->firstWhere('key', 'operations_audit_retention_days');

        $this->assertSame(365, $retention['effective_value']);
        $this->assertFalse($retention['has_company_override']);
    }

    protected function registry(): SettingsRegistry
    {
        return app(SettingsRegistry::class);
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

        $role = Role::create(['name' => 'Registry Tester', 'guard_name' => 'web']);
        $role->syncPermissions($permissions);
        $user->assignRole($role);

        return $user;
    }
}
