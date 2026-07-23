<?php

namespace Tests\Feature\Crm;

use App\Enums\CustomerArtworkStatus;
use App\Enums\CustomerPrintSpecificationStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Crm\CustomerArtwork;
use App\Models\Crm\CustomerPrintSpecification;
use App\Models\Crm\CustomerProductSerialProfile;
use App\Models\Inventory\InventoryItem;
use App\Models\User;
use App\Support\Crm\CustomerArtworkService;
use App\Support\Crm\CustomerPrintSpecificationService;
use App\Support\Sales\CustomerOrderContextService;
use App\Support\TenantContext;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CustomerPrintSpecificationTest extends TestCase
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
            'uses_serial_numbers' => true,
            'serial_prefix' => 'RB-',
            'serial_padding_length' => 6,
            'is_active' => true,
        ]);

        app()->instance(TenantContext::class, new TenantContext($this->company, $this->branch, false));
        session(['active_company_id' => $this->company->id, 'active_branch_id' => $this->branch->id]);
    }

    public function test_store_from_sales_order_returns_to_direct_order_modal(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('admin.crm.customers.print-specifications.store', $this->customer), [
                'from' => 'sales-order',
                'inventory_item_id' => $this->product->id,
                'name' => 'Walk-in Spec',
                'status' => CustomerPrintSpecificationStatus::Active->value,
            ]);

        $spec = CustomerPrintSpecification::query()->where('name', 'Walk-in Spec')->firstOrFail();

        $response->assertRedirect(route('admin.sales-orders.create', [
            'tab' => 'direct',
            'customer_id' => $this->customer->id,
            'print_specification_id' => $spec->id,
        ]));
    }

    public function test_can_create_print_specification_with_required_product(): void
    {
        $this->actingAs($this->user)
            ->post(route('admin.crm.customers.print-specifications.store', $this->customer), [
                'inventory_item_id' => $this->product->id,
                'name' => 'Fortress Receipt Book',
                'status' => CustomerPrintSpecificationStatus::Active->value,
                'production_notes' => 'Use perforation',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('customer_print_specifications', [
            'customer_id' => $this->customer->id,
            'inventory_item_id' => $this->product->id,
            'name' => 'Fortress Receipt Book',
            'status' => CustomerPrintSpecificationStatus::Active->value,
        ]);
    }

    public function test_create_form_route_is_not_captured_by_show_binding(): void
    {
        $this->actingAs($this->user)
            ->get(route('admin.crm.customers.print-specifications.create', $this->customer))
            ->assertOk()
            ->assertSee('Create print specification', false)
            ->assertSee('Save specification', false);
    }

    public function test_product_link_is_required(): void
    {
        $this->actingAs($this->user)
            ->post(route('admin.crm.customers.print-specifications.store', $this->customer), [
                'name' => 'Missing product',
                'status' => CustomerPrintSpecificationStatus::Draft->value,
            ])
            ->assertSessionHasErrors('inventory_item_id');
    }

    public function test_artwork_upload_creates_new_version_under_specification(): void
    {
        Storage::fake('local');

        $spec = app(CustomerPrintSpecificationService::class)->create($this->customer, [
            'inventory_item_id' => $this->product->id,
            'name' => 'Spec A',
            'status' => CustomerPrintSpecificationStatus::Active->value,
        ], $this->user->id);

        $file = UploadedFile::fake()->image('layout-v1.png');

        app(CustomerArtworkService::class)->uploadVersionForSpecification(
            $spec,
            $file,
            $this->user->id,
            'Initial layout',
        );

        $file2 = UploadedFile::fake()->image('layout-v2.png');

        app(CustomerArtworkService::class)->uploadVersionForSpecification(
            $spec,
            $file2,
            $this->user->id,
            'Updated logo',
        );

        $this->assertDatabaseCount('customer_artworks', 2);
        $this->assertDatabaseHas('customer_artworks', [
            'customer_print_specification_id' => $spec->id,
            'version_number' => 2,
            'is_active_version' => true,
            'change_notes' => 'Updated logo',
        ]);
        $this->assertDatabaseHas('customer_artworks', [
            'customer_print_specification_id' => $spec->id,
            'version_number' => 1,
            'is_active_version' => false,
            'status' => CustomerArtworkStatus::Superseded->value,
        ]);
    }

    public function test_only_one_active_artwork_version_per_specification(): void
    {
        Storage::fake('local');

        $spec = app(CustomerPrintSpecificationService::class)->create($this->customer, [
            'inventory_item_id' => $this->product->id,
            'name' => 'Spec B',
            'status' => CustomerPrintSpecificationStatus::Active->value,
        ], $this->user->id);

        $service = app(CustomerArtworkService::class);
        $service->uploadVersionForSpecification($spec, UploadedFile::fake()->image('a.png'), $this->user->id);
        $service->uploadVersionForSpecification($spec, UploadedFile::fake()->image('b.png'), $this->user->id);
        $service->uploadVersionForSpecification($spec, UploadedFile::fake()->image('c.png'), $this->user->id);

        $activeCount = CustomerArtwork::query()
            ->where('customer_print_specification_id', $spec->id)
            ->where('is_active_version', true)
            ->count();

        $this->assertSame(1, $activeCount);
    }

    public function test_archived_specifications_excluded_from_order_context(): void
    {
        $service = app(CustomerPrintSpecificationService::class);

        $active = $service->create($this->customer, [
            'inventory_item_id' => $this->product->id,
            'name' => 'Active Spec',
            'status' => CustomerPrintSpecificationStatus::Active->value,
        ], $this->user->id);

        $service->create($this->customer, [
            'inventory_item_id' => $this->product->id,
            'name' => 'Archived Spec',
            'status' => CustomerPrintSpecificationStatus::Archived->value,
        ], $this->user->id);

        $context = app(CustomerOrderContextService::class)->build($this->customer->fresh());

        $this->assertCount(1, $context['print_specifications']);
        $this->assertSame($active->id, $context['print_specifications'][0]['id']);
    }

    public function test_serial_profile_resolves_under_specification(): void
    {
        CustomerProductSerialProfile::query()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'inventory_item_id' => $this->product->id,
            'serial_prefix' => 'FOR-',
            'serial_padding_length' => 5,
        ]);

        $spec = app(CustomerPrintSpecificationService::class)->create($this->customer, [
            'inventory_item_id' => $this->product->id,
            'name' => 'Serial Spec',
            'status' => CustomerPrintSpecificationStatus::Active->value,
        ], $this->user->id);

        $summary = app(CustomerPrintSpecificationService::class)->serialSummary($spec->fresh(['inventoryItem', 'customer']));

        $this->assertTrue($summary['uses_serial_numbers']);
        $this->assertSame('FOR-', $summary['resolved_prefix']);
        $this->assertSame(5, $summary['resolved_padding']);
    }

    public function test_customer_isolation_on_edit(): void
    {
        $otherCustomer = Customer::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
        ]);

        $spec = app(CustomerPrintSpecificationService::class)->create($this->customer, [
            'inventory_item_id' => $this->product->id,
            'name' => 'Owned Spec',
            'status' => CustomerPrintSpecificationStatus::Active->value,
        ], $this->user->id);

        $this->actingAs($this->user)
            ->get(route('admin.crm.customers.print-specifications.edit', [$otherCustomer, $spec]))
            ->assertNotFound();
    }

    public function test_branch_isolation_blocks_cross_branch_specification(): void
    {
        $otherBranch = Branch::factory()->create(['company_id' => $this->company->id]);

        $spec = CustomerPrintSpecification::query()->create([
            'company_id' => $this->company->id,
            'branch_id' => $otherBranch->id,
            'customer_id' => $this->customer->id,
            'inventory_item_id' => $this->product->id,
            'specification_code' => 'CPS-999999',
            'name' => 'Other branch spec',
            'status' => CustomerPrintSpecificationStatus::Active,
            'created_by' => $this->user->id,
        ]);

        $this->actingAs($this->user)
            ->get(route('admin.crm.customers.print-specifications.edit', [$this->customer, $spec]))
            ->assertNotFound();
    }

    public function test_order_context_returns_print_specifications(): void
    {
        app(CustomerPrintSpecificationService::class)->create($this->customer, [
            'inventory_item_id' => $this->product->id,
            'name' => 'Context Spec',
            'status' => CustomerPrintSpecificationStatus::Active->value,
            'default_unit_price' => 15.5,
            'production_notes' => 'Rush ok',
        ], $this->user->id);

        $response = $this->actingAs($this->user)
            ->getJson(route('admin.sales-orders.customer-order-context', $this->customer));

        $response->assertOk()
            ->assertJsonPath('print_specifications.0.name', 'Context Spec')
            ->assertJsonPath('print_specifications.0.product_name', 'Receipt Book')
            ->assertJsonPath('print_specifications.0.default_unit_price', 15.5)
            ->assertJsonPath('print_specifications.0.production_notes', 'Rush ok');
    }

    public function test_customer_360_print_specifications_tab_is_paginated(): void
    {
        $service = app(CustomerPrintSpecificationService::class);

        for ($i = 1; $i <= 16; $i++) {
            $service->create($this->customer, [
                'inventory_item_id' => $this->product->id,
                'name' => 'Spec '.$i,
                'status' => CustomerPrintSpecificationStatus::Active->value,
            ], $this->user->id);
        }

        $paginator = $service->paginateForCustomer($this->customer, 15);

        $this->assertSame(16, $paginator->total());
        $this->assertCount(15, $paginator->items());

        $this->actingAs($this->user)
            ->get(route('admin.crm.customers.show', ['customer' => $this->customer, 'tab' => 'print-specifications']))
            ->assertOk()
            ->assertSee('Print Specifications', false)
            ->assertSee('Spec 1', false);
    }

    public function test_artwork_links_to_print_specification(): void
    {
        $spec = app(CustomerPrintSpecificationService::class)->create($this->customer, [
            'inventory_item_id' => $this->product->id,
            'name' => 'Linked Spec',
            'status' => CustomerPrintSpecificationStatus::Active->value,
        ], $this->user->id);

        Storage::fake('local');

        app(CustomerArtworkService::class)->uploadVersionForSpecification(
            $spec,
            UploadedFile::fake()->image('linked.png'),
            $this->user->id,
        );

        $this->assertDatabaseHas('customer_artworks', [
            'customer_print_specification_id' => $spec->id,
            'customer_id' => $this->customer->id,
            'is_active_version' => true,
        ]);
    }

    public function test_specification_code_unique_per_company(): void
    {
        $service = app(CustomerPrintSpecificationService::class);

        $first = $service->create($this->customer, [
            'inventory_item_id' => $this->product->id,
            'name' => 'One',
            'status' => CustomerPrintSpecificationStatus::Active->value,
        ], $this->user->id);

        $second = $service->create($this->customer, [
            'inventory_item_id' => $this->product->id,
            'name' => 'Two',
            'status' => CustomerPrintSpecificationStatus::Active->value,
        ], $this->user->id);

        $this->assertNotSame($first->specification_code, $second->specification_code);
        $this->assertSame($this->company->id, $first->company_id);
    }
}
