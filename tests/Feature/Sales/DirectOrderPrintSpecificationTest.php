<?php

namespace Tests\Feature\Sales;

use App\Enums\CustomerArtworkStatus;
use App\Enums\CustomerArtworkType;
use App\Enums\CustomerPrintSpecificationStatus;
use App\Enums\CustomerStatus;
use App\Enums\DomainCommunicationEvent;
use App\Enums\InventoryStockRole;
use App\Enums\SalesOrderBillingType;
use App\Enums\SalesOrderStatus;
use App\Events\Communications\DomainCommunicationEventRaised;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Crm\CustomerArtwork;
use App\Models\Crm\CustomerPrintSpecification;
use App\Models\Inventory\InventoryItem;
use App\Models\Production\ProductionJobCard;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use App\Support\ProductionJobCardValidator;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DirectOrderPrintSpecificationTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected Branch $branch;

    protected Customer $customer;

    protected User $user;

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
            'status' => CustomerStatus::Active,
        ]);

        $this->product = InventoryItem::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'item_name' => 'Receipt Book',
            'stock_role' => InventoryStockRole::FinishedGood,
            'is_active' => true,
        ]);

        session(['active_company_id' => $this->company->id, 'active_branch_id' => $this->branch->id]);
    }

    public function test_customer_360_create_order_opens_direct_mode(): void
    {
        $this->actingAs($this->user)
            ->withHeader('Turbo-Frame', 'erp-form-modal')
            ->get(route('admin.sales-orders.create', [
                'customer_id' => $this->customer->id,
                'tab' => 'direct',
            ]))
            ->assertOk()
            ->assertSee(__('Direct Order'), false)
            ->assertSee(__('Customer Print Specifications'), false);
    }

    public function test_direct_order_requires_customer_and_specification(): void
    {
        $this->actingAs($this->user)
            ->post(route('admin.sales-orders.store'), [
                'entry_mode' => 'direct',
                'customer_id' => $this->customer->id,
                'quantity' => 100,
            ])
            ->assertSessionHasErrors('customer_print_specification_id');
    }

    public function test_specification_selection_snapshots_artwork_and_product(): void
    {
        Event::fake([DomainCommunicationEventRaised::class]);

        [$spec, $artwork] = $this->activeSpecificationWithArtwork();

        $this->actingAs($this->user)
            ->post(route('admin.sales-orders.store'), [
                'entry_mode' => 'direct',
                'customer_id' => $this->customer->id,
                'customer_print_specification_id' => $spec->id,
                'quantity' => 500,
                'unit_price' => 12.5,
                'required_date' => now()->addWeek()->toDateString(),
            ])
            ->assertRedirect();

        $order = SalesOrder::query()->latest('id')->firstOrFail();
        $item = $order->items->first();

        $this->assertSame($spec->id, $order->customer_print_specification_id);
        $this->assertSame($this->product->id, $order->inventory_item_id);
        $this->assertSame($artwork->id, $order->customer_artwork_id);
        $this->assertTrue($order->uses_existing_artwork);
        $this->assertSame($spec->specification_code, $item->specification_code);
        $this->assertSame($artwork->version_number, $item->artwork_version_number);
        $this->assertSame($spec->production_notes, $item->production_notes_snapshot);
    }

    public function test_direct_order_does_not_create_quotation(): void
    {
        Event::fake([DomainCommunicationEventRaised::class]);

        [$spec] = $this->activeSpecificationWithArtwork();

        $this->actingAs($this->user)
            ->post(route('admin.sales-orders.store'), [
                'entry_mode' => 'direct',
                'customer_id' => $this->customer->id,
                'customer_print_specification_id' => $spec->id,
                'quantity' => 10,
                'unit_price' => 5,
            ])
            ->assertRedirect();

        $order = SalesOrder::query()->latest('id')->firstOrFail();

        $this->assertNull($order->quotation_id);
        $this->assertTrue($order->is_direct_order);
        $this->assertEquals(SalesOrderStatus::Confirmed, $order->status);
    }

    public function test_send_to_production_checkbox_creates_job_card(): void
    {
        Event::fake([DomainCommunicationEventRaised::class]);

        [$spec] = $this->activeSpecificationWithArtwork();

        $response = $this->actingAs($this->user)
            ->post(route('admin.sales-orders.store'), [
                'entry_mode' => 'direct',
                'customer_id' => $this->customer->id,
                'customer_print_specification_id' => $spec->id,
                'quantity' => 10,
                'unit_price' => 5,
                'send_to_production' => true,
            ])
            ->assertRedirect();

        $order = SalesOrder::query()->latest('id')->firstOrFail();

        $this->assertNotNull($order->jobCard);
        $response->assertRedirect(route('admin.production.job-cards.show', $order->jobCard));
        $this->assertEquals(SalesOrderStatus::ReadyForProduction, $order->fresh()->status);
        $this->assertEquals(1, ProductionJobCard::query()->where('sales_order_id', $order->id)->count());
    }

    public function test_archived_specification_cannot_be_selected_for_order(): void
    {
        $spec = CustomerPrintSpecification::query()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'inventory_item_id' => $this->product->id,
            'specification_code' => 'CPS-ARCH-01',
            'name' => 'Archived Spec',
            'status' => CustomerPrintSpecificationStatus::Archived,
            'created_by' => $this->user->id,
        ]);

        $this->actingAs($this->user)
            ->post(route('admin.sales-orders.store'), [
                'entry_mode' => 'direct',
                'customer_id' => $this->customer->id,
                'customer_print_specification_id' => $spec->id,
                'quantity' => 10,
            ])
            ->assertSessionHasErrors('customer_print_specification_id');
    }

    public function test_missing_artwork_blocks_when_artwork_required(): void
    {
        $spec = CustomerPrintSpecification::query()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'inventory_item_id' => $this->product->id,
            'specification_code' => 'CPS-NOART-01',
            'name' => 'No Artwork Spec',
            'status' => CustomerPrintSpecificationStatus::Active,
            'created_by' => $this->user->id,
        ]);

        $this->actingAs($this->user)
            ->post(route('admin.sales-orders.store'), [
                'entry_mode' => 'direct',
                'customer_id' => $this->customer->id,
                'customer_print_specification_id' => $spec->id,
                'quantity' => 10,
            ])
            ->assertSessionHasErrors('customer_print_specification_id');
    }

    public function test_missing_artwork_allowed_for_non_finished_product(): void
    {
        Event::fake([DomainCommunicationEventRaised::class]);

        $consumable = InventoryItem::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'stock_role' => InventoryStockRole::Consumable,
            'is_active' => true,
        ]);

        $spec = CustomerPrintSpecification::query()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'inventory_item_id' => $consumable->id,
            'specification_code' => 'CPS-CONS-01',
            'name' => 'Consumable Spec',
            'status' => CustomerPrintSpecificationStatus::Active,
            'created_by' => $this->user->id,
        ]);

        $this->actingAs($this->user)
            ->post(route('admin.sales-orders.store'), [
                'entry_mode' => 'direct',
                'customer_id' => $this->customer->id,
                'customer_print_specification_id' => $spec->id,
                'quantity' => 10,
                'unit_price' => 2,
            ])
            ->assertRedirect();

        $order = SalesOrder::query()->latest('id')->firstOrFail();
        ProductionJobCardValidator::assertCanCreateFromSalesOrder($order->load('inventoryItem'));
    }

    public function test_sales_order_created_notification_dispatched(): void
    {
        Event::fake([DomainCommunicationEventRaised::class]);

        [$spec] = $this->activeSpecificationWithArtwork();

        $this->actingAs($this->user)
            ->post(route('admin.sales-orders.store'), [
                'entry_mode' => 'direct',
                'customer_id' => $this->customer->id,
                'customer_print_specification_id' => $spec->id,
                'quantity' => 10,
                'unit_price' => 1,
            ])
            ->assertRedirect();

        Event::assertDispatched(DomainCommunicationEventRaised::class, function (DomainCommunicationEventRaised $event) {
            return $event->event === DomainCommunicationEvent::SalesOrderCreated;
        });
    }

    public function test_tenant_isolation_blocks_other_company_specification(): void
    {
        $otherCompany = Company::factory()->create();
        $otherBranch = Branch::factory()->create(['company_id' => $otherCompany->id]);
        $otherCustomer = Customer::factory()->create([
            'company_id' => $otherCompany->id,
            'branch_id' => $otherBranch->id,
        ]);
        $otherProduct = InventoryItem::factory()->create([
            'company_id' => $otherCompany->id,
            'branch_id' => $otherBranch->id,
        ]);

        $otherSpec = CustomerPrintSpecification::query()->create([
            'company_id' => $otherCompany->id,
            'branch_id' => $otherBranch->id,
            'customer_id' => $otherCustomer->id,
            'inventory_item_id' => $otherProduct->id,
            'specification_code' => 'CPS-OTHER-01',
            'name' => 'Other tenant spec',
            'status' => CustomerPrintSpecificationStatus::Active,
            'created_by' => $this->user->id,
        ]);

        $this->actingAs($this->user)
            ->post(route('admin.sales-orders.store'), [
                'entry_mode' => 'direct',
                'customer_id' => $this->customer->id,
                'customer_print_specification_id' => $otherSpec->id,
                'quantity' => 10,
            ])
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
            'specification_code' => 'CPS-BR-01',
            'name' => 'Other branch spec',
            'status' => CustomerPrintSpecificationStatus::Active,
            'created_by' => $this->user->id,
        ]);

        $this->actingAs($this->user)
            ->post(route('admin.sales-orders.store'), [
                'entry_mode' => 'direct',
                'customer_id' => $this->customer->id,
                'customer_print_specification_id' => $spec->id,
                'quantity' => 10,
            ])
            ->assertNotFound();
    }

    public function test_repeat_order_uses_print_specification(): void
    {
        Event::fake([DomainCommunicationEventRaised::class]);

        [$spec, $artwork] = $this->activeSpecificationWithArtwork();

        $this->actingAs($this->user)
            ->post(route('admin.sales-orders.store'), [
                'entry_mode' => 'direct',
                'customer_id' => $this->customer->id,
                'customer_print_specification_id' => $spec->id,
                'quantity' => 100,
                'unit_price' => 10,
            ])
            ->assertRedirect();

        $source = SalesOrder::query()->latest('id')->firstOrFail();

        $this->actingAs($this->user)
            ->post(route('admin.sales-orders.store'), [
                'entry_mode' => 'direct',
                'customer_id' => $this->customer->id,
                'customer_print_specification_id' => $spec->id,
                'quantity' => 250,
                'required_date' => now()->addDays(3)->toDateString(),
                'notes' => 'Repeat run',
                'repeat_source_sales_order_id' => $source->id,
            ])
            ->assertRedirect();

        $repeat = SalesOrder::query()->where('repeat_source_sales_order_id', $source->id)->firstOrFail();

        $this->assertSame($spec->id, $repeat->customer_print_specification_id);
        $this->assertSame($artwork->id, $repeat->customer_artwork_id);
        $this->assertEquals(250, (float) $repeat->items->first()->quantity);
        $this->assertNull($repeat->quotation_id);
    }

    /**
     * @return array{0: CustomerPrintSpecification, 1: CustomerArtwork}
     */
    protected function activeSpecificationWithArtwork(): array
    {
        Storage::fake('local');

        $spec = CustomerPrintSpecification::query()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'inventory_item_id' => $this->product->id,
            'specification_code' => 'CPS-'.fake()->unique()->numerify('######'),
            'name' => 'Fortress Receipt Book',
            'status' => CustomerPrintSpecificationStatus::Active,
            'production_notes' => 'Perforate top edge',
            'commercial_notes' => 'Net 30 preferred',
            'default_unit_price' => 15,
            'created_by' => $this->user->id,
        ]);

        $artwork = CustomerArtwork::query()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'customer_print_specification_id' => $spec->id,
            'artwork_name' => $spec->name,
            'artwork_type' => CustomerArtworkType::Layout,
            'version_number' => 1,
            'is_active_version' => true,
            'file_path' => 'customer-artworks/test/layout.png',
            'file_name' => 'layout.png',
            'original_file_name' => 'layout.png',
            'status' => CustomerArtworkStatus::Active,
            'uploaded_by' => $this->user->id,
            'uploaded_at' => now(),
        ]);

        return [$spec, $artwork];
    }
}
