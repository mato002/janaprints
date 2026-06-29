<?php

namespace Tests\Feature\Production;

use App\Enums\CustomerArtworkStatus;
use App\Enums\CustomerArtworkType;
use App\Enums\CustomerPrintSpecificationStatus;
use App\Enums\CustomerStatus;
use App\Enums\DomainCommunicationEvent;
use App\Enums\InventoryStockRole;
use App\Enums\ProductionJobCardStatus;
use App\Enums\SalesOrderStatus;
use App\Events\Communications\DomainCommunicationEventRaised;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Crm\CustomerArtwork;
use App\Models\Crm\CustomerPrintSpecification;
use App\Models\Crm\CustomerProductSerialProfile;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\ProductProductionRouteStep;
use App\Models\Production\JobCardRouteStep;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionMaterialRequirement;
use App\Models\Production\ProductionSpecification;
use App\Models\Production\WorkCenter;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use App\Support\Production\ProductQcChecklistService;
use App\Support\Production\ProductBomService;
use App\Support\ProductionJobCardService;
use App\Support\ProductionJobCardValidator;
use App\Support\Sales\DirectCustomerSalesOrderService;
use App\Support\TenantContext;
use Database\Seeders\InventoryFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class JobCardPrintSpecificationTest extends TestCase
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
            'uses_serial_numbers' => true,
            'serial_prefix' => 'RB',
            'serial_padding_length' => 4,
            'is_active' => true,
        ]);

        session(['active_company_id' => $this->company->id, 'active_branch_id' => $this->branch->id]);
        app()->instance(TenantContext::class, new TenantContext($this->company, $this->branch));
    }

    public function test_direct_order_job_card_inherits_specification(): void
    {
        Event::fake([DomainCommunicationEventRaised::class]);

        [$spec, $artwork, $order] = $this->directOrderFromSpecification();

        $jobCard = ProductionJobCardService::createFromSalesOrder($order, $this->user->id);

        $this->assertSame($spec->id, $jobCard->customer_print_specification_id);
        $this->assertSame('direct', $jobCard->order_source);
        $this->assertSame($spec->specification_code, $jobCard->specification_code);
        $this->assertSame($spec->name, $jobCard->specification_name);
        $this->assertSame($this->product->id, $jobCard->inventory_item_id);
        $this->assertSame($artwork->id, $jobCard->customer_artwork_id);
        $this->assertSame('Perforate top edge', $jobCard->production_notes_snapshot);
        $this->assertSame('Net 30 preferred', $jobCard->commercial_notes_snapshot);
        $this->assertSame('Leave at reception', $jobCard->customer_instructions_snapshot);
    }

    public function test_job_card_snapshots_artwork_version(): void
    {
        Event::fake([DomainCommunicationEventRaised::class]);

        [, $artwork, $order] = $this->directOrderFromSpecification(artworkVersion: 4);

        $jobCard = ProductionJobCardService::createFromSalesOrder($order, $this->user->id);

        $this->assertSame(4, $jobCard->artwork_version_number);
        $this->assertSame($artwork->id, $jobCard->customer_artwork_id);

        $artwork->update(['version_number' => 99, 'is_active_version' => false]);

        $this->assertSame(4, $jobCard->fresh()->artwork_version_number);
    }

    public function test_job_card_does_not_require_quotation_artwork_request(): void
    {
        Event::fake([DomainCommunicationEventRaised::class]);

        [, , $order] = $this->directOrderFromSpecification();

        $this->assertNull($order->quotation_id);
        $this->assertNull($order->artwork_request_id);
        $this->assertTrue($order->uses_existing_artwork);

        ProductionJobCardValidator::assertCanCreateFromSalesOrder($order->load('inventoryItem'));

        $jobCard = ProductionJobCardService::createFromSalesOrder($order, $this->user->id);

        $this->assertNull($jobCard->artwork_request_id);
        $this->assertNotNull($jobCard->customer_artwork_id);
    }

    public function test_missing_artwork_blocks_only_when_required(): void
    {
        $spec = CustomerPrintSpecification::query()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'inventory_item_id' => $this->product->id,
            'specification_code' => 'CPS-NOART-JC',
            'name' => 'No Artwork Spec',
            'status' => CustomerPrintSpecificationStatus::Active,
            'created_by' => $this->user->id,
        ]);

        $order = SalesOrder::query()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'customer_print_specification_id' => $spec->id,
            'inventory_item_id' => $this->product->id,
            'uses_existing_artwork' => false,
            'customer_artwork_id' => null,
            'order_number' => 'SO-TEST-001',
            'order_date' => now()->toDateString(),
            'status' => SalesOrderStatus::Confirmed,
            'subtotal' => 100,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => 100,
            'is_direct_order' => true,
            'created_by' => $this->user->id,
        ]);

        $order->items()->create([
            'customer_print_specification_id' => $spec->id,
            'inventory_item_id' => $this->product->id,
            'specification_code' => $spec->specification_code,
            'specification_name' => $spec->name,
            'item_name' => $this->product->item_name,
            'quantity' => 10,
            'unit_price' => 10,
            'line_total' => 100,
        ]);

        $this->expectException(ValidationException::class);

        try {
            ProductionJobCardValidator::assertCanCreateFromSalesOrder($order->load('inventoryItem'));
        } catch (ValidationException $e) {
            $this->assertStringContainsString(
                'active artwork version',
                strtolower($e->errors()['artwork'][0] ?? ''),
            );
            throw $e;
        }
    }

    public function test_missing_artwork_allowed_for_non_finished_product_job_card(): void
    {
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
            'specification_code' => 'CPS-CONS-JC',
            'name' => 'Consumable Spec',
            'status' => CustomerPrintSpecificationStatus::Active,
            'created_by' => $this->user->id,
        ]);

        $order = SalesOrder::query()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'customer_print_specification_id' => $spec->id,
            'inventory_item_id' => $consumable->id,
            'uses_existing_artwork' => false,
            'customer_artwork_id' => null,
            'order_number' => 'SO-CONS-001',
            'order_date' => now()->toDateString(),
            'status' => SalesOrderStatus::Confirmed,
            'subtotal' => 50,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => 50,
            'is_direct_order' => true,
            'created_by' => $this->user->id,
        ]);

        $order->items()->create([
            'customer_print_specification_id' => $spec->id,
            'inventory_item_id' => $consumable->id,
            'specification_code' => $spec->specification_code,
            'specification_name' => $spec->name,
            'item_name' => $consumable->item_name,
            'quantity' => 5,
            'unit_price' => 10,
            'line_total' => 50,
        ]);

        ProductionJobCardValidator::assertCanCreateFromSalesOrder($order->load('inventoryItem'));

        $jobCard = ProductionJobCardService::createFromSalesOrder($order, $this->user->id);

        $this->assertSame($spec->id, $jobCard->customer_print_specification_id);
        $this->assertNull($jobCard->customer_artwork_id);
    }

    public function test_serial_allocation_uses_customer_product_settings(): void
    {
        Event::fake([DomainCommunicationEventRaised::class]);

        CustomerProductSerialProfile::query()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'inventory_item_id' => $this->product->id,
            'serial_prefix' => 'FT',
            'serial_padding_length' => 5,
        ]);

        [, , $order] = $this->directOrderFromSpecification(quantity: 25);

        $jobCard = ProductionJobCardService::createFromSalesOrder($order, $this->user->id);
        $allocation = $jobCard->serialAllocation;

        $this->assertNotNull($allocation);
        $this->assertSame('FT', $allocation->serial_prefix);
        $this->assertSame(5, $allocation->serial_padding_length);
        $this->assertSame(25, $allocation->allocatedQuantity());
        $this->assertSame('FT00001', $allocation->formatSerial($allocation->serial_start));
        $this->assertSame('FT00025', $allocation->formatSerial($allocation->serial_end));
    }

    public function test_route_bom_qc_snapshots_still_work(): void
    {
        $this->seed(InventoryFoundationSeeder::class);
        Event::fake([DomainCommunicationEventRaised::class]);

        $workCenter = WorkCenter::query()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'code' => 'WC-PRINT',
            'name' => 'Printing',
            'is_active' => true,
        ]);

        ProductProductionRouteStep::query()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'inventory_item_id' => $this->product->id,
            'work_center_id' => $workCenter->id,
            'step_name' => 'Printing',
            'sequence' => 1,
            'is_active' => true,
        ]);

        $paper = InventoryItem::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'stock_role' => InventoryStockRole::RawMaterial,
            'is_active' => true,
        ]);

        app(ProductBomService::class)->create(
            $this->company->id,
            $this->branch->id,
            $this->user->id,
            ['finished_item_id' => $this->product->id, 'name' => 'Receipt BOM'],
            [['inventory_item_id' => $paper->id, 'quantity_per_unit' => 0.01, 'sort_order' => 1]],
        );

        [, , $order] = $this->directOrderFromSpecification(quantity: 100);

        $jobCard = ProductionJobCardService::createFromSalesOrder($order, $this->user->id);

        $this->assertGreaterThanOrEqual(
            1,
            JobCardRouteStep::query()->where('production_job_card_id', $jobCard->id)->count(),
        );

        $this->assertDatabaseHas('production_material_requirements', [
            'production_job_card_id' => $jobCard->id,
            'inventory_item_id' => $paper->id,
        ]);

        $jobCard->update(['status' => ProductionJobCardStatus::QualityCheck]);
        app(ProductQcChecklistService::class)->snapshotForJobCard($jobCard);

        $this->assertDatabaseHas('job_card_qc_snapshots', [
            'production_job_card_id' => $jobCard->id,
        ]);

        $this->assertNotNull(
            ProductionSpecification::query()->where('production_job_card_id', $jobCard->id)->first(),
        );
    }

    public function test_tenant_isolation_blocks_cross_company_job_card_from_spec_order(): void
    {
        Event::fake([DomainCommunicationEventRaised::class]);

        [, , $order] = $this->directOrderFromSpecification();

        $otherCompany = Company::factory()->create();
        $otherBranch = Branch::factory()->create(['company_id' => $otherCompany->id]);
        $otherUser = User::factory()->create([
            'company_id' => $otherCompany->id,
            'default_branch_id' => $otherBranch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $otherUser->assignRole('Company Admin');

        session(['active_company_id' => $otherCompany->id, 'active_branch_id' => $otherBranch->id]);
        app()->instance(TenantContext::class, new TenantContext($otherCompany, $otherBranch));

        $this->actingAs($otherUser)
            ->post(route('admin.production.job-cards.store'), [
                'sales_order_id' => $order->id,
                'production_type' => 'digital',
                'priority' => 'normal',
            ])
            ->assertNotFound();
    }

    public function test_branch_isolation_on_job_card_snapshot(): void
    {
        Event::fake([DomainCommunicationEventRaised::class]);

        $otherBranch = Branch::factory()->create(['company_id' => $this->company->id]);

        [, , $order] = $this->directOrderFromSpecification();

        session(['active_company_id' => $this->company->id, 'active_branch_id' => $otherBranch->id]);
        app()->instance(TenantContext::class, new TenantContext($this->company, $otherBranch));

        $this->actingAs($this->user)
            ->post(route('admin.production.job-cards.store'), [
                'sales_order_id' => $order->id,
                'production_type' => 'digital',
                'priority' => 'normal',
            ])
            ->assertNotFound();
    }

    /**
     * @return array{0: CustomerPrintSpecification, 1: CustomerArtwork, 2: SalesOrder}
     */
    protected function directOrderFromSpecification(
        int $artworkVersion = 1,
        float $quantity = 500,
    ): array {
        Storage::fake('local');

        $spec = CustomerPrintSpecification::query()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'inventory_item_id' => $this->product->id,
            'specification_code' => 'SPEC-'.fake()->unique()->numerify('######'),
            'name' => 'Fortress Receipt Book',
            'status' => CustomerPrintSpecificationStatus::Active,
            'production_notes' => 'Perforate top edge',
            'commercial_notes' => 'Net 30 preferred',
            'customer_instructions' => 'Leave at reception',
            'created_by' => $this->user->id,
        ]);

        $artwork = CustomerArtwork::query()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'customer_print_specification_id' => $spec->id,
            'artwork_name' => $spec->name,
            'artwork_type' => CustomerArtworkType::Layout,
            'version_number' => $artworkVersion,
            'is_active_version' => true,
            'file_path' => 'customer-artworks/test/layout.png',
            'file_name' => 'layout.png',
            'original_file_name' => 'layout.png',
            'status' => CustomerArtworkStatus::Active,
            'uploaded_by' => $this->user->id,
            'uploaded_at' => now(),
        ]);

        $order = app(DirectCustomerSalesOrderService::class)->createFromPrintSpecification(
            $spec,
            ['quantity' => $quantity, 'unit_price' => 12.5],
            $this->user->id,
        );

        $order->update(['status' => SalesOrderStatus::Confirmed]);

        return [$spec, $artwork, $order->fresh(['items', 'inventoryItem'])];
    }
}
