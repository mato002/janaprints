<?php

namespace Tests\Feature\Commercial;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use Database\Seeders\CrmFoundationSeeder;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\PlatformConfigurationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CommercialFormLookupTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        $this->seed(PlatformConfigurationSeeder::class);
        $this->seed(CrmFoundationSeeder::class);
    }

    public function test_customer_create_has_company_plus_button_for_super_admin(): void
    {
        $user = $this->superAdmin();

        $this->actingAs($user)
            ->withHeader('Turbo-Frame', 'erp-form-modal')
            ->get(route('admin.crm.customers.create'))
            ->assertOk()
            ->assertSee('erp-lookup-select__add', false)
            ->assertSee('erpLookupCreate', false);
    }

    public function test_quotation_create_has_customer_plus_button(): void
    {
        [$company, $branch, $user] = $this->salesUser();

        $this->actingAs($user)
            ->withHeader('Turbo-Frame', 'erp-form-modal')
            ->get(route('admin.quotations.create'))
            ->assertOk()
            ->assertSee('erp-lookup-select__add', false)
            ->assertSee('erpLookupCreate', false);
    }

    public function test_customer_quick_create_form_renders_in_lookup_panel(): void
    {
        [$company, $branch, $user] = $this->salesUser();

        $this->actingAs($user)
            ->withHeader('X-Erp-Lookup-Create', '1')
            ->get(route('admin.crm.customers.quick-create'))
            ->assertOk()
            ->assertSee('data-erp-lookup-modal-panel', false)
            ->assertSee('data-erp-lookup-form', false)
            ->assertSee('name="kra_pin"', false)
            ->assertSee('name="physical_address"', false)
            ->assertSee('name="payment_terms"', false);
    }

    public function test_quotation_quick_create_form_renders_full_quotation_fields(): void
    {
        [$company, $branch, $user] = $this->salesUser();

        $this->actingAs($user)
            ->withHeader('X-Erp-Lookup-Create', '1')
            ->get(route('admin.quotations.quick-create'))
            ->assertOk()
            ->assertSee('data-erp-lookup-modal-panel', false)
            ->assertSee('name="quotation_date"', false)
            ->assertSee('name="currency"', false)
            ->assertSee('Line items', false)
            ->assertSee('Add line', false);
    }

    public function test_segment_create_has_company_plus_button_for_super_admin(): void
    {
        $user = $this->superAdmin();

        $this->actingAs($user)
            ->withHeader('Turbo-Frame', 'erp-form-modal')
            ->get(route('admin.crm.segments.create'))
            ->assertOk()
            ->assertSee('erp-lookup-select__add', false)
            ->assertSee('erpLookupCreate', false);
    }

    public function test_company_quick_create_form_renders_in_lookup_panel(): void
    {
        $user = $this->superAdmin();

        $this->actingAs($user)
            ->withHeader('X-Erp-Lookup-Create', '1')
            ->get(route('admin.companies.quick-create'))
            ->assertOk()
            ->assertSee('data-erp-lookup-modal-panel', false)
            ->assertSee('data-erp-lookup-form', false);
    }

    public function test_validation_errors_remain_inside_nested_modal(): void
    {
        $user = $this->superAdmin();

        $this->actingAs($user)
            ->withHeader('X-Erp-Lookup-Create', '1')
            ->post(route('admin.companies.quick-store'), [
                '_erp_lookup_create' => 1,
                'name' => '',
            ])
            ->assertStatus(422)
            ->assertSee('data-erp-lookup-modal-panel', false)
            ->assertSee('data-erp-lookup-form', false);
    }

    public function test_lead_create_has_lead_source_plus_button(): void
    {
        [, , $user] = $this->salesUser();

        $this->actingAs($user)
            ->withHeader('Turbo-Frame', 'erp-form-modal')
            ->get(route('admin.crm.leads.create'))
            ->assertOk()
            ->assertSee('erp-lookup-select__add', false)
            ->assertSee('name: \'lead_source_id\'', false);
    }

    public function test_activity_create_has_customer_plus_button(): void
    {
        [$company, $branch, $user] = $this->salesUser();
        $user->givePermissionTo('crm.activities.create');

        $this->actingAs($user)
            ->withHeader('Turbo-Frame', 'erp-form-modal')
            ->get(route('admin.commercial.activities.create'))
            ->assertOk()
            ->assertSee('erp-lookup-select__add', false)
            ->assertSee('name: \'customer_id\'', false);
    }

    protected function superAdmin(): User
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);

        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $role = Role::findByName('Super Admin', 'web');
        $role->syncPermissions([
            'companies.manage',
            'branches.manage',
            'crm.customers.view',
            'crm.customers.create',
        ]);
        $user->assignRole('Super Admin');

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        return $user;
    }

    /**
     * @return array{0: Company, 1: Branch, 2: User}
     */
    protected function salesUser(): array
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);

        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $role = Role::findByName('Sales', 'web');
        $role->syncPermissions([
            'crm.customers.view',
            'crm.customers.create',
            'crm.leads.create',
            'quotations.view',
            'quotations.create',
        ]);
        $user->assignRole('Sales');

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        return [$company, $branch, $user];
    }
}
