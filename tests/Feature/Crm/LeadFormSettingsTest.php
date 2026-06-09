<?php

namespace Tests\Feature\Crm;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Support\Platform\FormSettingsManager;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\PlatformConfigurationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LeadFormSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        $this->seed(PlatformConfigurationSeeder::class);
    }

    public function test_lead_create_form_reads_visibility_from_form_settings(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();
        $user = $this->userWithPermissions(['crm.leads.view', 'crm.leads.create'], $company, $branch);

        app(FormSettingsManager::class)->save($company->id, $branch->id, [
            'lead' => [
                'is_active' => true,
                'fields' => [
                    'company_name' => [
                        'visibility' => 'hidden',
                        'requirement' => 'optional',
                        'read_only' => '0',
                        'default_value' => '',
                    ],
                    'notes' => [
                        'visibility' => 'visible',
                        'requirement' => 'optional',
                        'read_only' => '0',
                        'default_value' => '',
                    ],
                ],
            ],
        ]);

        $this->actingAs($user)
            ->get(route('admin.crm.leads.create'))
            ->assertOk()
            ->assertSee('id="notes"', false)
            ->assertSee('id="expected_close_date"', false)
            ->assertDontSee('id="company_name"', false);
    }

    public function test_lead_create_form_shows_required_marker_for_configured_fields(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();
        $user = $this->userWithPermissions(['crm.leads.view', 'crm.leads.create'], $company, $branch);

        $this->actingAs($user)
            ->get(route('admin.crm.leads.create'))
            ->assertOk()
            ->assertSee('for="estimated_value"', false)
            ->assertSee('class="block text-sm font-medium text-erp-primary required"', false);
    }

    /**
     * @param  list<string>  $permissions
     */
    protected function userWithPermissions(array $permissions, Company $company, Branch $branch): User
    {
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $role = Role::create(['name' => 'Lead Form Tester', 'guard_name' => 'web']);
        $role->syncPermissions($permissions);
        $user->assignRole($role);

        return $user;
    }
}
