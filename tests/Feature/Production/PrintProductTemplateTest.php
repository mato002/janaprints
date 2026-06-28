<?php

namespace Tests\Feature\Production;

use App\Enums\CustomerStatus;
use App\Enums\PrintProductTemplateCategory;
use App\Enums\ProductionType;
use App\Enums\SalesOrderStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Production\PrintProductTemplate;
use App\Models\Production\ProductionSpecification;
use App\Models\Sales\SalesOrder;
use App\Models\Sales\SalesOrderItem;
use App\Models\User;
use App\Support\Production\PrintProductTemplateService;
use App\Support\Production\ProductionSpecificationService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PrintProductTemplateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_template_can_be_created(): void
    {
        [$company, $branch, $user] = $this->tenantContext(['production.bom.view', 'production.bom.create']);

        $this->actingAs($user)
            ->post(route('admin.production.print-templates.store'), [
                'code' => 'TEST-FLYER',
                'name' => 'Test Flyer',
                'category' => PrintProductTemplateCategory::Marketing->value,
                'production_type' => ProductionType::Digital->value,
                'gsm' => '150',
                'default_finished_size' => 'A5',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('print_product_templates', [
            'company_id' => $company->id,
            'code' => 'TESTFLYER',
            'name' => 'Test Flyer',
        ]);
    }

    public function test_template_can_be_edited(): void
    {
        [, , $user, $template] = $this->templateContext(['production.bom.view', 'production.bom.edit']);

        $this->actingAs($user)
            ->put(route('admin.production.print-templates.update', $template), [
                'code' => $template->code,
                'name' => 'Updated Flyer Name',
                'category' => $template->category->value,
                'default_finished_size' => 'A4',
            ])
            ->assertRedirect(route('admin.production.print-templates.show', $template));

        $this->assertSame('Updated Flyer Name', $template->fresh()->name);
        $this->assertSame('A4', $template->fresh()->default_finished_size);
    }

    public function test_template_activation_and_deactivation(): void
    {
        [, , $user, $template] = $this->templateContext(['production.bom.view', 'production.bom.edit']);

        $this->actingAs($user)
            ->post(route('admin.production.print-templates.toggle-active', $template))
            ->assertRedirect();

        $this->assertFalse($template->fresh()->is_active);

        $this->actingAs($user)
            ->post(route('admin.production.print-templates.toggle-active', $template))
            ->assertRedirect();

        $this->assertTrue($template->fresh()->is_active);
    }

    public function test_template_duplication(): void
    {
        [, , $user, $template] = $this->templateContext(['production.bom.view', 'production.bom.create', 'production.bom.edit']);

        $this->actingAs($user)
            ->post(route('admin.production.print-templates.duplicate', $template))
            ->assertRedirect();

        $copy = PrintProductTemplate::query()->where('name', 'like', '%Copy%')->first();
        $this->assertNotNull($copy);
        $this->assertFalse($copy->is_active);
        $this->assertNotSame($template->code, $copy->code);
    }

    public function test_applying_template_to_production_specification(): void
    {
        [, , , $user, $salesOrder, $item, $template] = $this->orderContext(['sales_orders.edit', 'production.bom.view']);

        $defaults = app(PrintProductTemplateService::class)->applyToSpecificationDefaults($template);

        $this->assertSame(ProductionType::Digital->value, $defaults['production_type']);
        $this->assertSame('A5', $defaults['finished_size']);

        $this->actingAs($user)
            ->post(route('admin.sales-orders.items.specification.store', [$salesOrder, $item]), [
                'print_product_template_id' => $template->id,
                'production_type' => ProductionType::Offset->value,
                'finished_size' => 'A4',
            ]);

        $spec = ProductionSpecification::query()->where('sales_order_item_id', $item->id)->firstOrFail();
        $this->assertSame($template->id, $spec->print_product_template_id);
        $this->assertSame(ProductionType::Offset, $spec->production_type);
        $this->assertSame('A4', $spec->finished_size);
    }

    public function test_user_override_preserved_when_applying_template(): void
    {
        [, , , , , $item, $template] = $this->orderContext(['sales_orders.edit']);

        $service = app(PrintProductTemplateService::class);
        $defaults = $service->applyToSpecificationDefaults($template);
        $merged = $service->mergeWithUserInput($defaults, [
            'binding_type' => 'wire_o',
            'ups' => 8,
        ]);

        $this->assertSame('wire_o', $merged['binding_type']);
        $this->assertSame(8, $merged['ups']);
        $this->assertSame($template->id, $merged['print_product_template_id']);
    }

    public function test_inactive_templates_hidden_from_selection(): void
    {
        [, , , , , , $template] = $this->orderContext(['sales_orders.edit']);

        $template->update(['is_active' => false]);

        $active = app(PrintProductTemplateService::class)->activeForSelection();
        $this->assertFalse($active->contains('id', $template->id));
    }

    public function test_legacy_specifications_unaffected(): void
    {
        [, , , $user, , $item] = $this->orderContext(['sales_orders.edit']);

        $spec = app(ProductionSpecificationService::class)->createForSalesOrderItem($item, [
            'production_type' => ProductionType::Offset->value,
            'size' => 'A3',
        ], $user);

        $this->assertNull($spec->print_product_template_id);
        $this->assertSame('A3', $spec->size);
    }

    public function test_cross_tenant_access_blocked(): void
    {
        [, , , $userA, , , $template] = $this->orderContext(['production.bom.view', 'production.bom.edit']);

        $companyB = Company::factory()->create();
        $branchB = Branch::factory()->create(['company_id' => $companyB->id]);
        $userB = User::factory()->create([
            'company_id' => $companyB->id,
            'default_branch_id' => $branchB->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $role = Role::findByName('Production', 'web');
        $role->syncPermissions(['production.bom.view', 'production.bom.edit']);
        $userB->assignRole('Production');
        session(['active_company_id' => $companyB->id, 'active_branch_id' => $branchB->id]);

        $this->actingAs($userB)
            ->get(route('admin.production.print-templates.show', $template))
            ->assertForbidden();
    }

    /**
     * @return array{0: Company, 1: Branch, 2: User}
     */
    protected function tenantContext(array $permissions): array
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $user = $this->userWithPermissions($company, $branch, $permissions);
        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        return [$company, $branch, $user];
    }

    /**
     * @return array{0: Company, 1: Branch, 2: User, 3: PrintProductTemplate}
     */
    protected function templateContext(array $permissions): array
    {
        [$company, $branch, $user] = $this->tenantContext($permissions);
        $template = PrintProductTemplate::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'code' => 'FLYER-TEST',
            'name' => 'Flyer Template',
            'category' => PrintProductTemplateCategory::Marketing,
            'production_type' => ProductionType::Digital,
            'default_finished_size' => 'A5',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        return [$company, $branch, $user, $template];
    }

    /**
     * @return array{0: Company, 1: Branch, 2: Customer, 3: User, 4: SalesOrder, 5: SalesOrderItem, 6: PrintProductTemplate}
     */
    protected function orderContext(array $permissions): array
    {
        [$company, $branch, $user, $template] = $this->templateContext(array_merge($permissions, ['production.bom.view']));

        $customer = Customer::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'status' => CustomerStatus::Active,
        ]);

        $salesOrder = SalesOrder::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'status' => SalesOrderStatus::Confirmed,
            'created_by' => $user->id,
        ]);

        $item = SalesOrderItem::query()->create([
            'sales_order_id' => $salesOrder->id,
            'item_name' => 'Flyers',
            'quantity' => 1000,
            'unit_price' => 5,
            'line_total' => 5000,
            'sort_order' => 1,
        ]);

        return [$company, $branch, $customer, $user, $salesOrder, $item, $template];
    }

    protected function userWithPermissions(Company $company, Branch $branch, array $permissions): User
    {
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $role = Role::findByName('Production', 'web');
        $role->syncPermissions($permissions);
        $user->assignRole('Production');

        return $user;
    }
}
