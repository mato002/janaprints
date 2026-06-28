<?php

namespace Tests\Feature\Production;

use App\Enums\CustomerStatus;
use App\Enums\ProductionType;
use App\Enums\QuotationStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Accounting\Journal;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Inventory\InventoryMovement;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionSpecification;
use App\Models\Sales\SalesOrder;
use App\Models\Sales\SalesOrderItem;
use App\Models\User;
use App\Services\Production\Job360WorkspaceService;
use App\Support\Production\ProductionSpecificationService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductionSpecificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_specification_can_be_created_for_sales_order_item(): void
    {
        [$company, $branch, , $user, $salesOrder, $item] = $this->context(['sales_orders.view', 'sales_orders.edit']);

        $this->actingAs($user)
            ->post(route('admin.sales-orders.items.specification.store', [$salesOrder, $item]), [
                'production_type' => ProductionType::Offset->value,
                'product_description' => 'Invoice books A5',
                'quantity' => 600,
                'unit' => 'copies',
                'size' => 'A5',
                'paper_inventory_item_id' => null,
                'ups' => 2,
                'estimated_sheets' => 300,
                'binding_type' => 'top',
            ])
            ->assertRedirect(route('admin.sales-orders.show', $salesOrder))
            ->assertSessionHas('status');

        $spec = ProductionSpecification::query()->where('sales_order_item_id', $item->id)->first();

        $this->assertNotNull($spec);
        $this->assertSame(ProductionType::Offset, $spec->production_type);
        $this->assertSame('Invoice books A5', $spec->product_description);
        $this->assertSame(600.0, (float) $spec->quantity);
        $this->assertSame('top', $spec->binding_type);
        $this->assertSame($salesOrder->customer_id, $spec->customer_id);
        $this->assertSame($salesOrder->id, $spec->sales_order_id);
    }

    public function test_specification_links_to_customer_order_and_item(): void
    {
        [, , $customer, $user, $salesOrder, $item] = $this->context(['sales_orders.view', 'sales_orders.edit']);

        $spec = app(ProductionSpecificationService::class)->createForSalesOrderItem($item, [
            'production_type' => ProductionType::Digital->value,
            'size' => 'A4',
        ], $user);

        $this->assertSame($customer->id, $spec->customer_id);
        $this->assertSame($salesOrder->id, $spec->sales_order_id);
        $this->assertSame($item->id, $spec->sales_order_item_id);
        $this->assertNotNull($spec->snapshot_payload);
    }

    public function test_specification_renders_in_job_360(): void
    {
        [, , , $user, $salesOrder, $item] = $this->context(['production.view', 'production.create', 'sales_orders.edit']);

        $spec = app(ProductionSpecificationService::class)->createForSalesOrderItem($item, [
            'production_type' => ProductionType::Digital->value,
            'product_description' => 'Flyers A6',
            'ups' => 4,
            'estimated_sheets' => 125,
        ], $user);

        $jobCard = $this->createJobCard($salesOrder, $user);
        app(ProductionSpecificationService::class)->linkToJobCard($spec, $jobCard);

        $this->actingAs($user)
            ->get(route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'specification']))
            ->assertOk()
            ->assertSee(__('Specification'), false)
            ->assertSee('Flyers A6')
            ->assertSee('125');

        $payload = app(Job360WorkspaceService::class)->build($jobCard, 'specification');
        $this->assertTrue($payload['tab_data']['specification']['has_specification']);
    }

    public function test_missing_optional_fields_do_not_break_view(): void
    {
        [, , , $user, $salesOrder, $item] = $this->context(['production.view', 'production.create', 'sales_orders.edit']);

        $spec = app(ProductionSpecificationService::class)->createForSalesOrderItem($item, [], $user);
        $jobCard = $this->createJobCard($salesOrder, $user);
        app(ProductionSpecificationService::class)->linkToJobCard($spec, $jobCard);

        $presented = app(ProductionSpecificationService::class)->present($spec);

        $this->assertTrue($presented['has_specification']);
        $this->assertArrayHasKey('sections', $presented);

        $this->actingAs($user)
            ->get(route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'specification']))
            ->assertOk()
            ->assertSee(__('Specification'), false);
    }

    public function test_specification_does_not_create_inventory_or_accounting_records(): void
    {
        [, , , $user, , $item] = $this->context(['sales_orders.edit']);

        $movementsBefore = InventoryMovement::query()->count();
        $journalsBefore = Journal::query()->count();

        app(ProductionSpecificationService::class)->createForSalesOrderItem($item, [
            'production_type' => ProductionType::Offset->value,
            'estimated_sheets' => 500,
        ], $user);

        $this->assertSame($movementsBefore, InventoryMovement::query()->count());
        $this->assertSame($journalsBefore, Journal::query()->count());
    }

    public function test_cross_tenant_access_blocked(): void
    {
        [$companyA, $branchA, , $userA, $salesOrder, $item] = $this->context(['sales_orders.view', 'sales_orders.edit']);

        $spec = app(ProductionSpecificationService::class)->createForSalesOrderItem($item, [
            'production_type' => ProductionType::Digital->value,
        ], $userA);

        $companyB = Company::factory()->create();
        $branchB = Branch::factory()->create(['company_id' => $companyB->id]);
        $userB = User::factory()->create([
            'company_id' => $companyB->id,
            'default_branch_id' => $branchB->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $role = Role::findByName('Production', 'web');
        $role->syncPermissions(['sales_orders.view', 'sales_orders.edit']);
        $userB->assignRole('Production');
        session(['active_company_id' => $companyB->id, 'active_branch_id' => $branchB->id]);

        $this->actingAs($userB)
            ->get(route('admin.sales-orders.items.specification.edit', [$salesOrder, $item, $spec]))
            ->assertForbidden();

        unset($companyA, $branchA);
    }

    public function test_legacy_job_cards_without_specification_still_render(): void
    {
        [, , , $user, $salesOrder] = $this->context(['production.view', 'production.create']);

        $jobCard = $this->createJobCard($salesOrder, $user);

        $this->actingAs($user)
            ->get(route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'specification']))
            ->assertOk()
            ->assertSee(__('No structured production specification yet.'), false);
    }

    /**
     * @return array{0: Company, 1: Branch, 2: Customer, 3: User, 4: SalesOrder, 5: SalesOrderItem}
     */
    protected function context(array $permissions): array
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $customer = Customer::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_code' => 'C-SPEC',
            'company_name' => 'Spec Customer',
            'status' => CustomerStatus::Active,
        ]);
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $role = Role::findByName('Production', 'web');
        $role->syncPermissions($permissions);
        $user->assignRole('Production');

        $salesOrder = SalesOrder::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'status' => SalesOrderStatus::Confirmed,
            'created_by' => $user->id,
        ]);

        $item = SalesOrderItem::query()->create([
            'sales_order_id' => $salesOrder->id,
            'item_name' => 'Business Cards',
            'description' => 'Premium business cards',
            'quantity' => 500,
            'unit_price' => 3.5,
            'line_total' => 1750,
            'sort_order' => 1,
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        return [$company, $branch, $customer, $user, $salesOrder, $item];
    }

    protected function createJobCard(SalesOrder $salesOrder, User $user): ProductionJobCard
    {
        return ProductionJobCard::factory()->create([
            'company_id' => $salesOrder->company_id,
            'branch_id' => $salesOrder->branch_id,
            'sales_order_id' => $salesOrder->id,
            'customer_id' => $salesOrder->customer_id,
            'quotation_id' => $salesOrder->quotation_id,
            'artwork_request_id' => $salesOrder->artwork_request_id,
            'created_by' => $user->id,
        ]);
    }
}
