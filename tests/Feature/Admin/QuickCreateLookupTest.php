<?php

namespace Tests\Feature\Admin;

use App\Enums\CustomerStatus;
use App\Enums\CustomerType;
use App\Enums\VendorStatus;
use App\Enums\VendorType;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Inventory\InventoryCategory;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\UnitOfMeasure;
use App\Models\Procurement\Vendor;
use App\Models\User;
use Database\Seeders\CrmFoundationSeeder;
use Database\Seeders\InventoryFoundationSeeder;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\PlatformConfigurationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class QuickCreateLookupTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        $this->seed(PlatformConfigurationSeeder::class);
    }

    public function test_company_quick_create_returns_json(): void
    {
        $user = $this->superAdmin();

        $response = $this->actingAs($user)
            ->withHeaders(['X-Erp-Lookup-Create' => '1', 'Accept' => 'application/json'])
            ->post(route('admin.companies.quick-store'), [
                '_erp_lookup_create' => 1,
                'name' => 'Inline Company Ltd',
                'email' => 'inline@example.com',
                'is_active' => 1,
            ]);

        $response->assertOk()
            ->assertJsonStructure(['id', 'label', 'value', 'message'])
            ->assertJsonPath('label', 'Inline Company Ltd');

        $this->assertDatabaseHas('companies', ['name' => 'Inline Company Ltd']);
    }

    public function test_branch_quick_create_returns_json(): void
    {
        [$company, $branch, $user] = $this->tenantContext(['branches.manage']);

        $response = $this->actingAs($user)
            ->withHeaders(['X-Erp-Lookup-Create' => '1', 'Accept' => 'application/json'])
            ->post(route('admin.branches.quick-store'), [
                '_erp_lookup_create' => 1,
                'company_id' => $company->id,
                'name' => 'Westlands Branch',
                'code' => 'WEST',
                'is_active' => 1,
            ]);

        $response->assertOk()
            ->assertJsonPath('label', 'Westlands Branch');

        $this->assertDatabaseHas('branches', ['code' => 'WEST', 'company_id' => $company->id]);
    }

    public function test_customer_quick_create_returns_json(): void
    {
        [$company, $branch, $user] = $this->tenantContext(['crm.customers.create']);

        $response = $this->actingAs($user)
            ->withHeaders(['X-Erp-Lookup-Create' => '1', 'Accept' => 'application/json'])
            ->post(route('admin.crm.customers.quick-store'), [
                '_erp_lookup_create' => 1,
                'customer_type' => CustomerType::Corporate->value,
                'company_name' => 'Quick Customer Ltd',
                'status' => CustomerStatus::Active->value,
                'credit_limit' => 0,
            ]);

        $response->assertOk()
            ->assertJsonPath('label', 'Quick Customer Ltd');

        $this->assertDatabaseHas('customers', ['company_name' => 'Quick Customer Ltd']);
    }

    public function test_vendor_quick_create_form_renders(): void
    {
        [$company, $branch, $user] = $this->tenantContext(['procurement.vendors.create']);

        $this->actingAs($user)
            ->withHeader('X-Erp-Lookup-Create', '1')
            ->get(route('admin.procurement.vendors.quick-create'))
            ->assertOk()
            ->assertSee('data-erp-lookup-modal-panel', false)
            ->assertSee('data-erp-lookup-form', false)
            ->assertSee('name="vendor_name"', false)
            ->assertSee('name="vendor_type"', false);
    }

    public function test_vendor_quick_create_returns_json(): void
    {
        [$company, $branch, $user] = $this->tenantContext(['procurement.vendors.create']);

        $response = $this->actingAs($user)
            ->withHeaders(['X-Erp-Lookup-Create' => '1', 'Accept' => 'application/json'])
            ->post(route('admin.procurement.vendors.quick-store'), [
                '_erp_lookup_create' => 1,
                'vendor_name' => 'Quick Vendor Ltd',
                'vendor_type' => VendorType::Supplier->value,
                'status' => VendorStatus::Active->value,
            ]);

        $response->assertOk()
            ->assertJsonPath('label', 'Quick Vendor Ltd');

        $this->assertDatabaseHas('vendors', ['vendor_name' => 'Quick Vendor Ltd']);
    }

    public function test_item_quick_create_returns_json(): void
    {
        [$company, $branch, $user] = $this->inventoryContext();

        $category = InventoryCategory::query()->where('company_id', $company->id)->where('branch_id', $branch->id)->firstOrFail();
        $unit = UnitOfMeasure::query()->where('company_id', $company->id)->where('branch_id', $branch->id)->firstOrFail();

        $response = $this->actingAs($user)
            ->withHeaders(['X-Erp-Lookup-Create' => '1', 'Accept' => 'application/json'])
            ->post(route('admin.inventory.items.quick-store'), [
                '_erp_lookup_create' => 1,
                'inventory_category_id' => $category->id,
                'unit_of_measure_id' => $unit->id,
                'item_name' => 'Quick Item',
                'reorder_level' => 0,
                'reorder_quantity' => 0,
                'standard_cost' => 0,
                'is_active' => 1,
            ]);

        $response->assertOk()
            ->assertJsonStructure(['id', 'label', 'value', 'message']);

        $this->assertStringContainsString('Quick Item', $response->json('label'));

        $this->assertDatabaseHas('inventory_items', ['item_name' => 'Quick Item']);
    }

    public function test_lookup_refresh_returns_options_array(): void
    {
        [$company, $branch, $user] = $this->tenantContext(['crm.customers.view']);

        Customer::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_code' => 'C-100',
            'customer_type' => CustomerType::Corporate,
            'company_name' => 'Refresh Customer',
            'status' => CustomerStatus::Active,
            'credit_limit' => 0,
        ]);

        $this->actingAs($user)
            ->getJson(route('admin.lookups.customers'))
            ->assertOk()
            ->assertJsonFragment(['label' => 'Refresh Customer']);
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
        $role->syncPermissions(['companies.manage', 'branches.manage']);
        $user->assignRole('Super Admin');

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        return $user;
    }

    /**
     * @param  list<string>  $extraPermissions
     * @return array{0: Company, 1: Branch, 2: User}
     */
    protected function tenantContext(array $extraPermissions = []): array
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $this->seed(CrmFoundationSeeder::class);

        $permissions = array_values(array_unique([
            'crm.customers.view',
            ...$extraPermissions,
        ]));

        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $role = Role::findByName('Sales', 'web');
        $role->syncPermissions($permissions);
        $user->assignRole('Sales');

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        return [$company, $branch, $user];
    }

    /**
     * @return array{0: Company, 1: Branch, 2: User}
     */
    protected function inventoryContext(): array
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $this->seed(InventoryFoundationSeeder::class);

        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $role = Role::findByName('Storekeeper', 'web');
        $role->syncPermissions(['catalogue.create', 'catalogue.view']);
        $user->assignRole('Storekeeper');

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        return [$company, $branch, $user];
    }
}
