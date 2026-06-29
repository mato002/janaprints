<?php

namespace Tests\Feature\Crm;

use App\Enums\CustomerPrintSpecificationStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Crm\CustomerPrintSpecification;
use App\Models\Inventory\InventoryItem;
use App\Models\Production\ProductionJobCard;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use App\Support\Crm\CustomerPrintSpecificationLifecycleService;
use App\Support\Crm\CustomerPrintSpecificationService;
use App\Support\Crm\CustomerPrintSpecificationUsageService;
use App\Support\Crm\CustomerPrintSpecificationWorkspaceService;
use App\Support\Sales\DirectCustomerSalesOrderService;
use App\Support\TenantContext;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerPrintSpecificationLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected Branch $branch;

    protected User $user;

    protected Customer $customer;

    protected InventoryItem $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->company = Company::factory()->create();
        $this->branch = Branch::factory()->create(['company_id' => $this->company->id]);
        $this->user = User::factory()->create([
            'company_id' => $this->company->id,
            'default_branch_id' => $this->branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $this->user->assignRole('Company Admin');

        $this->customer = Customer::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
        ]);

        $this->product = InventoryItem::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'item_name' => 'Receipt Book',
            'is_active' => true,
        ]);

        session(['active_company_id' => $this->company->id, 'active_branch_id' => $this->branch->id]);
        app()->instance(TenantContext::class, new TenantContext($this->company, $this->branch, false));
    }

    protected function makeSpec(CustomerPrintSpecificationStatus $status = CustomerPrintSpecificationStatus::Draft): CustomerPrintSpecification
    {
        return app(CustomerPrintSpecificationService::class)->create($this->customer, [
            'inventory_item_id' => $this->product->id,
            'name' => 'Test Spec',
            'status' => $status,
        ], $this->user->id);
    }

    public function test_lifecycle_transitions_follow_allowed_paths(): void
    {
        $lifecycle = app(CustomerPrintSpecificationLifecycleService::class);
        $spec = $this->makeSpec();

        $spec = $lifecycle->transition($spec, CustomerPrintSpecificationStatus::Active, $this->user->id);
        $this->assertSame(CustomerPrintSpecificationStatus::Active, $spec->status);

        $spec = $lifecycle->transition($spec, CustomerPrintSpecificationStatus::Superseded, $this->user->id);
        $this->assertSame(CustomerPrintSpecificationStatus::Superseded, $spec->status);

        $spec = $lifecycle->transition($spec, CustomerPrintSpecificationStatus::Archived, $this->user->id);
        $this->assertSame(CustomerPrintSpecificationStatus::Archived, $spec->status);
    }

    public function test_draft_specification_cannot_create_order(): void
    {
        $spec = $this->makeSpec(CustomerPrintSpecificationStatus::Draft);

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        app(DirectCustomerSalesOrderService::class)->createFromPrintSpecification($spec, [], $this->user->id);
    }

    public function test_archived_specifications_hidden_from_order_context(): void
    {
        $spec = $this->makeSpec(CustomerPrintSpecificationStatus::Active);
        app(CustomerPrintSpecificationLifecycleService::class)
            ->transition($spec, CustomerPrintSpecificationStatus::Archived, $this->user->id);

        $payload = app(\App\Support\Sales\CustomerOrderContextService::class)
            ->buildForDirectOrder($this->customer);

        $this->assertSame([], $payload['print_specifications'] ?? []);
    }

    public function test_usage_metrics_reflect_orders(): void
    {
        $spec = $this->makeSpec(CustomerPrintSpecificationStatus::Active);

        SalesOrder::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'customer_print_specification_id' => $spec->id,
            'inventory_item_id' => $this->product->id,
            'status' => SalesOrderStatus::Confirmed,
            'total_amount' => 250,
            'order_date' => now()->toDateString(),
        ]);

        $metrics = app(CustomerPrintSpecificationUsageService::class)->usageMetrics($spec->fresh());

        $this->assertSame(1, $metrics['orders_count']);
        $this->assertSame(250.0, $metrics['total_revenue']);
    }

    public function test_version_history_lists_artwork_versions(): void
    {
        $spec = $this->makeSpec(CustomerPrintSpecificationStatus::Active);

        $versions = app(CustomerPrintSpecificationWorkspaceService::class)
            ->artworkVersionHistory($spec);

        $this->assertIsArray($versions);
    }

    public function test_safe_editing_blocks_product_change_after_usage(): void
    {
        $spec = $this->makeSpec(CustomerPrintSpecificationStatus::Active);

        SalesOrder::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'customer_print_specification_id' => $spec->id,
            'inventory_item_id' => $this->product->id,
            'status' => SalesOrderStatus::Confirmed,
        ]);

        $otherProduct = InventoryItem::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
        ]);

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        app(CustomerPrintSpecificationService::class)->update($spec, [
            'inventory_item_id' => $otherProduct->id,
            'name' => 'Updated name',
            'status' => CustomerPrintSpecificationStatus::Active->value,
        ], $this->user->id);
    }

    public function test_archived_specification_is_read_only(): void
    {
        $spec = $this->makeSpec(CustomerPrintSpecificationStatus::Active);
        app(CustomerPrintSpecificationLifecycleService::class)
            ->transition($spec, CustomerPrintSpecificationStatus::Archived, $this->user->id);

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        app(CustomerPrintSpecificationService::class)->update($spec->fresh(), [
            'name' => 'Should fail',
            'status' => CustomerPrintSpecificationStatus::Archived->value,
            'inventory_item_id' => $this->product->id,
        ], $this->user->id);
    }

    public function test_search_filters_by_code(): void
    {
        $this->makeSpec();
        $target = app(CustomerPrintSpecificationService::class)->create($this->customer, [
            'inventory_item_id' => $this->product->id,
            'name' => 'Unique Alpha Spec',
            'status' => CustomerPrintSpecificationStatus::Draft,
        ], $this->user->id);

        $results = app(CustomerPrintSpecificationService::class)->searchForCustomer($this->customer, [
            'search' => $target->specification_code,
        ]);

        $this->assertTrue(
            collect($results->items())->pluck('id')->contains($target->id),
            'Expected search to find specification '.$target->specification_code,
        );
    }

    public function test_customer_isolation_on_workspace(): void
    {
        $spec = $this->makeSpec(CustomerPrintSpecificationStatus::Active);
        $otherCustomer = Customer::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
        ]);

        $this->actingAs($this->user)
            ->get(route('admin.crm.customers.print-specifications.show', [$otherCustomer, $spec]))
            ->assertNotFound();
    }

    public function test_branch_isolation_on_workspace(): void
    {
        $otherBranch = Branch::factory()->create(['company_id' => $this->company->id]);
        $spec = CustomerPrintSpecification::query()->create([
            'company_id' => $this->company->id,
            'branch_id' => $otherBranch->id,
            'customer_id' => $this->customer->id,
            'inventory_item_id' => $this->product->id,
            'specification_code' => 'CPS-999999',
            'name' => 'Branch isolated',
            'status' => CustomerPrintSpecificationStatus::Active,
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);

        $this->actingAs($this->user)
            ->get(route('admin.crm.customers.print-specifications.show', [$this->customer, $spec]))
            ->assertNotFound();
    }

    public function test_workspace_renders_with_sticky_context(): void
    {
        $spec = $this->makeSpec(CustomerPrintSpecificationStatus::Active);

        $this->actingAs($this->user)
            ->get(route('admin.crm.customers.print-specifications.show', [$this->customer, $spec]))
            ->assertOk()
            ->assertSee(__('Usage intelligence'))
            ->assertSee(__('Artwork Versions'));
    }

    public function test_workspace_loads_without_n_plus_one_queries(): void
    {
        $spec = $this->makeSpec(CustomerPrintSpecificationStatus::Active);

        SalesOrder::factory()->count(3)->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'customer_print_specification_id' => $spec->id,
            'inventory_item_id' => $this->product->id,
            'status' => SalesOrderStatus::Confirmed,
        ]);

        \DB::enableQueryLog();
        app(CustomerPrintSpecificationWorkspaceService::class)->build($spec->fresh(), 'usage-history');
        $count = count(\DB::getQueryLog());
        \DB::disableQueryLog();

        $this->assertLessThan(35, $count);
    }
}
