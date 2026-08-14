<?php

namespace Tests\Feature\Sales;

use App\Enums\CustomerArtworkStatus;
use App\Enums\CustomerArtworkType;
use App\Enums\CustomerPrintSpecificationStatus;
use App\Enums\CustomerStatus;
use App\Enums\DomainCommunicationEvent;
use App\Enums\InventoryStockRole;
use App\Enums\ProductionDestination;
use App\Enums\ProductionType;
use App\Events\Communications\DomainCommunicationEventRaised;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Crm\CustomerArtwork;
use App\Models\Crm\CustomerPrintSpecification;
use App\Models\Inventory\InventoryItem;
use App\Models\Production\ProductionSpecification;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use App\Support\Production\JobCardJobSheetPresenter;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DirectOrderOffsetJobSheetTest extends TestCase
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

    public function test_direct_create_form_includes_offset_job_sheet_fields(): void
    {
        $this->actingAs($this->user)
            ->withHeader('Turbo-Frame', 'erp-form-modal')
            ->get(route('admin.sales-orders.create', [
                'customer_id' => $this->customer->id,
                'tab' => 'direct',
            ]))
            ->assertOk()
            ->assertSee(__('Offset job sheet'), false)
            ->assertSee(__('Printing specifications'), false)
            ->assertSee(__('Paper colour'), false)
            ->assertSee(__('Material requisition'), false)
            ->assertSee('job_sheet[paper_stock]', false)
            ->assertSee('job_sheet[paper_colour_orig]', false);
    }

    public function test_offset_order_requires_job_sheet_fields(): void
    {
        [$spec] = $this->activeSpecificationWithArtwork();

        $this->actingAs($this->user)
            ->post(route('admin.sales-orders.store'), [
                'entry_mode' => 'direct',
                'production_destination' => ProductionDestination::Offset->value,
                'customer_id' => $this->customer->id,
                'customer_print_specification_id' => $spec->id,
                'quantity' => 50,
                'unit_price' => 10,
            ])
            ->assertSessionHasErrors([
                'job_sheet.product_description',
                'job_sheet.paper_stock',
                'job_sheet.size',
            ]);
    }

    public function test_offset_order_stores_job_sheet_on_production_specification(): void
    {
        Event::fake([DomainCommunicationEventRaised::class]);

        [$spec] = $this->activeSpecificationWithArtwork();

        $this->actingAs($this->user)
            ->post(route('admin.sales-orders.store'), [
                'entry_mode' => 'direct',
                'production_destination' => ProductionDestination::Offset->value,
                'customer_id' => $this->customer->id,
                'customer_print_specification_id' => $spec->id,
                'quantity' => 50,
                'unit_price' => 25,
                'required_date' => now()->addDays(4)->toDateString(),
                'job_sheet' => [
                    'product_description' => 'NCR receipt books',
                    'paper_colour_orig' => 'White',
                    'paper_colour_dup' => 'Pink',
                    'paper_colour_tri' => 'Yellow',
                    'paper_colour_quad' => '',
                    'paper_stock' => 'NCR 2-part',
                    'ink' => 'Black',
                    'serial_number' => '001',
                    'pages_per_pad' => '50',
                    'size' => 'A5',
                    'ups' => 2,
                    'binding_type' => 'Top padded',
                    'production_notes' => 'Number and perforate',
                    'material_rows' => [
                        ['paper_type' => 'NCR White', 'sheets_a4_a3' => '250', 'sheets_a1' => ''],
                        ['paper_type' => '', 'sheets_a4_a3' => '', 'sheets_a1' => ''],
                    ],
                ],
            ])
            ->assertRedirect();

        $order = SalesOrder::query()->latest('id')->firstOrFail();
        $productionSpec = ProductionSpecification::query()
            ->where('sales_order_id', $order->id)
            ->first();

        $this->assertSame(ProductionDestination::Offset, $order->production_destination);
        $this->assertNotNull($productionSpec);
        $this->assertSame(ProductionType::Offset, $productionSpec->production_type);
        $this->assertSame('NCR receipt books', $productionSpec->product_description);
        $this->assertSame('A5', $productionSpec->size);
        $this->assertSame('Top padded', $productionSpec->binding_type);
        $this->assertSame(2, $productionSpec->ups);
        $this->assertSame('White', $productionSpec->job_sheet_payload['ncr_colours']['orig']);
        $this->assertSame('Pink', $productionSpec->job_sheet_payload['ncr_colours']['dup']);
        $this->assertSame('NCR 2-part', $productionSpec->job_sheet_payload['paper_stock']);
        $this->assertSame('Black', $productionSpec->job_sheet_payload['ink']);
        $this->assertSame('001', $productionSpec->job_sheet_payload['serial_number']);
        $this->assertSame('50', $productionSpec->job_sheet_payload['pages_per_pad']);
        $this->assertSame('NCR White', $productionSpec->job_sheet_payload['material_rows'][0]['paper_type']);
        $this->assertCount(1, $productionSpec->job_sheet_payload['material_rows']);
    }

    public function test_digital_order_does_not_create_offset_job_sheet_specification(): void
    {
        Event::fake([DomainCommunicationEventRaised::class]);

        [$spec] = $this->activeSpecificationWithArtwork();

        $this->actingAs($this->user)
            ->post(route('admin.sales-orders.store'), [
                'entry_mode' => 'direct',
                'production_destination' => ProductionDestination::Digital->value,
                'customer_id' => $this->customer->id,
                'customer_print_specification_id' => $spec->id,
                'quantity' => 20,
                'unit_price' => 5,
                'job_sheet' => [
                    'product_description' => 'Should be ignored',
                    'paper_stock' => 'Bond',
                    'size' => 'A4',
                ],
            ])
            ->assertRedirect();

        $order = SalesOrder::query()->latest('id')->firstOrFail();

        $this->assertSame(ProductionDestination::Digital, $order->production_destination);
        $this->assertNull(ProductionSpecification::query()->where('sales_order_id', $order->id)->first());
    }

    public function test_offset_job_sheet_prints_captured_fields_after_send_to_production(): void
    {
        Event::fake([DomainCommunicationEventRaised::class]);

        [$spec] = $this->activeSpecificationWithArtwork();

        $this->actingAs($this->user)
            ->post(route('admin.sales-orders.store'), [
                'entry_mode' => 'direct',
                'production_destination' => ProductionDestination::Offset->value,
                'customer_id' => $this->customer->id,
                'customer_print_specification_id' => $spec->id,
                'quantity' => 80,
                'unit_price' => 15,
                'send_to_production' => true,
                'job_sheet' => [
                    'product_description' => 'Invoice books',
                    'paper_colour_orig' => 'White',
                    'paper_colour_dup' => 'Blue',
                    'paper_stock' => 'NCR 3-part',
                    'ink' => 'Black',
                    'pages_per_pad' => '100',
                    'size' => 'A4',
                    'ups' => 1,
                    'binding_type' => 'Stapled',
                    'serial_number' => '5001',
                ],
            ])
            ->assertRedirect();

        $order = SalesOrder::query()->latest('id')->firstOrFail();
        $jobCard = $order->fresh('jobCard')->jobCard;

        $this->assertNotNull($jobCard);
        $this->assertSame($order->items->first()->productionSpecification?->id, $jobCard->productionSpecification?->id);

        $payload = app(JobCardJobSheetPresenter::class)->present($jobCard->fresh('productionSpecification'));

        $this->assertSame('Invoice books', $payload['printing_rows'][0]['description']);
        $this->assertSame('White', $payload['printing_rows'][0]['orig']);
        $this->assertSame('Blue', $payload['printing_rows'][0]['dup']);
        $this->assertSame('NCR 3-part', $payload['printing_rows'][0]['paper_stock']);
        $this->assertSame('Black', $payload['printing_rows'][0]['ink']);
        $this->assertSame('100', $payload['binding']['pages_per_pad']);
        $this->assertSame('A4', $payload['binding']['size']);
        $this->assertSame('Stapled', $payload['binding']['binding']);
        $this->assertSame('5001', $payload['binding']['serial_start']);

        $this->actingAs($this->user)
            ->get(route('admin.production.job-cards.job-sheet', $jobCard))
            ->assertOk()
            ->assertSee('Invoice books', false)
            ->assertSee('NCR 3-part', false)
            ->assertSee('White', false);
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
