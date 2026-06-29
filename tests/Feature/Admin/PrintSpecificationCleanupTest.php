<?php

namespace Tests\Feature\Admin;

use App\Enums\ArtworkApprovalDecision;
use App\Enums\ArtworkRequestStatus;
use App\Enums\CustomerArtworkStatus;
use App\Enums\CustomerArtworkType;
use App\Enums\CustomerPrintSpecificationStatus;
use App\Enums\CustomerStatus;
use App\Enums\DomainCommunicationEvent;
use App\Enums\InventoryStockRole;
use App\Enums\QuotationStatus;
use App\Events\Communications\DomainCommunicationEventRaised;
use App\Models\Artwork\ArtworkApproval;
use App\Models\Artwork\ArtworkRequest;
use App\Models\Artwork\ArtworkVersion;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Crm\CustomerArtwork;
use App\Models\Crm\CustomerPrintSpecification;
use App\Models\Inventory\InventoryItem;
use App\Models\Sales\Quotation;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PrintSpecificationCleanupTest extends TestCase
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

    public function test_customer_360_create_order_opens_direct_tab(): void
    {
        $this->actingAs($this->user)
            ->withHeader('Turbo-Frame', 'erp-form-modal')
            ->get(route('admin.sales-orders.create', [
                'customer_id' => $this->customer->id,
                'tab' => 'direct',
            ]))
            ->assertOk()
            ->assertSee(__('Direct Order'), false)
            ->assertSee(__('Customer Print Specifications'), false)
            ->assertSee('erp-form-modal__actions--sticky', false);
    }

    public function test_quotation_conversion_still_works(): void
    {
        Event::fake([DomainCommunicationEventRaised::class]);

        $quotation = Quotation::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'prepared_by' => $this->user->id,
            'status' => QuotationStatus::Accepted,
        ]);

        $quotation->items()->create([
            'item_type' => 'product',
            'item_name' => 'Banner',
            'quantity' => 1,
            'unit_price' => 100,
            'discount' => 0,
            'tax_rate' => 0,
            'line_total' => 100,
            'sort_order' => 0,
        ]);

        $artworkRequest = ArtworkRequest::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'quotation_id' => $quotation->id,
            'requested_by' => $this->user->id,
            'status' => ArtworkRequestStatus::Approved,
            'current_version' => 1,
        ]);

        $version = ArtworkVersion::query()->create([
            'artwork_request_id' => $artworkRequest->id,
            'version_number' => 1,
            'file_path' => 'artwork/test.pdf',
            'original_name' => 'test.pdf',
            'uploaded_by' => $this->user->id,
        ]);

        ArtworkApproval::query()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'artwork_request_id' => $artworkRequest->id,
            'artwork_version_id' => $version->id,
            'approved_by' => $this->user->id,
            'decision' => ArtworkApprovalDecision::Approved,
        ]);

        $this->actingAs($this->user)
            ->post(route('admin.sales-orders.store'), [
                'entry_mode' => 'quotation',
                'quotation_id' => $quotation->id,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('sales_orders', [
            'quotation_id' => $quotation->id,
            'customer_id' => $this->customer->id,
        ]);
    }

    public function test_print_specifications_tab_renders(): void
    {
        [$spec] = $this->activeSpecificationWithArtwork();

        $this->actingAs($this->user)
            ->get(route('admin.crm.customers.show', [
                'customer' => $this->customer,
                'tab' => 'print-specifications',
            ]))
            ->assertOk()
            ->assertSee(__('Print Specifications'), false)
            ->assertSee($spec->name, false)
            ->assertSee(__('Create Order'), false);
    }

    public function test_archived_specs_hidden_from_direct_order_context(): void
    {
        CustomerPrintSpecification::query()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'inventory_item_id' => $this->product->id,
            'specification_code' => 'CPS-ARCH-CLEAN',
            'name' => 'Archived Spec',
            'status' => CustomerPrintSpecificationStatus::Archived,
            'created_by' => $this->user->id,
        ]);

        [$activeSpec] = $this->activeSpecificationWithArtwork();

        $response = $this->actingAs($this->user)
            ->getJson(route('admin.sales-orders.customer-order-context', [
                'customer' => $this->customer,
                'scope' => 'direct-order',
            ]));

        $response->assertOk();
        $ids = collect($response->json('print_specifications'))->pluck('id')->all();

        $this->assertContains($activeSpec->id, $ids);
        $this->assertNotContains(
            CustomerPrintSpecification::query()->where('specification_code', 'CPS-ARCH-CLEAN')->value('id'),
            $ids,
        );
    }

    public function test_route_references_exist_for_touched_views(): void
    {
        $this->assertTrue(Route::has('admin.sales-orders.customer-order-context'));
        $this->assertTrue(Route::has('admin.production.boms.destroy'));
        $this->assertFalse(Route::has('admin.crm.customers.order-context'));
    }

    public function test_sales_order_create_modal_has_sticky_action_footer(): void
    {
        $this->actingAs($this->user)
            ->withHeader('Turbo-Frame', 'erp-form-modal')
            ->get(route('admin.sales-orders.create'))
            ->assertOk()
            ->assertSee('erp-form-modal__actions--sticky', false);
    }

    public function test_tenant_isolation_on_order_context(): void
    {
        $otherCompany = Company::factory()->create();
        $otherBranch = Branch::factory()->create(['company_id' => $otherCompany->id]);
        $otherCustomer = Customer::factory()->create([
            'company_id' => $otherCompany->id,
            'branch_id' => $otherBranch->id,
        ]);

        $this->actingAs($this->user)
            ->getJson(route('admin.sales-orders.customer-order-context', [
                'customer' => $otherCustomer,
                'scope' => 'direct-order',
            ]))
            ->assertForbidden();
    }

    public function test_branch_isolation_on_order_context(): void
    {
        $otherBranch = Branch::factory()->create(['company_id' => $this->company->id]);
        [$spec] = $this->activeSpecificationWithArtwork();
        $spec->update(['branch_id' => $otherBranch->id]);

        $response = $this->actingAs($this->user)
            ->getJson(route('admin.sales-orders.customer-order-context', [
                'customer' => $this->customer,
                'scope' => 'direct-order',
            ]));

        $response->assertOk();
        $this->assertEmpty($response->json('print_specifications'));
    }

    public function test_bom_destroy_route_is_registered(): void
    {
        $paper = InventoryItem::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'stock_role' => InventoryStockRole::RawMaterial,
            'is_active' => true,
        ]);

        $bom = app(\App\Support\Production\ProductBomService::class)->create(
            $this->company->id,
            $this->branch->id,
            $this->user->id,
            ['finished_item_id' => $this->product->id, 'name' => 'Test BOM'],
            [['inventory_item_id' => $paper->id, 'quantity_per_unit' => 0.01, 'sort_order' => 1]],
        );

        $this->actingAs($this->user)
            ->delete(route('admin.production.boms.destroy', $bom))
            ->assertRedirect(route('admin.production.boms.index'));

        $this->assertDatabaseMissing('product_boms', ['id' => $bom->id]);
    }

    public function test_direct_order_context_is_slim_payload(): void
    {
        $this->activeSpecificationWithArtwork();

        $response = $this->actingAs($this->user)
            ->getJson(route('admin.sales-orders.customer-order-context', [
                'customer' => $this->customer,
                'scope' => 'direct-order',
            ]))
            ->assertOk();

        $payload = $response->json();

        $this->assertArrayHasKey('print_specifications', $payload);
        $this->assertArrayHasKey('billing_defaults', $payload);
        $this->assertArrayNotHasKey('previous_orders', $payload);
        $this->assertArrayNotHasKey('artwork_library', $payload);
    }

    /**
     * @return array{0: CustomerPrintSpecification}
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

        CustomerArtwork::query()->create([
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

        return [$spec];
    }
}
