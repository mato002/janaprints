<?php

namespace Tests\Feature\Production;

use App\Enums\ArtworkApprovalDecision;
use App\Enums\ArtworkRequestStatus;
use App\Enums\CustomerStatus;
use App\Enums\ProductionType;
use App\Enums\SalesOrderStatus;
use App\Models\Accounting\Journal;
use App\Models\Artwork\ArtworkApproval;
use App\Models\Artwork\ArtworkRequest;
use App\Models\Artwork\ArtworkVersion;
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
use App\Services\Production\ProductionQueueWorkspaceService;
use App\Support\Production\JobCardManufacturingPresenter;
use App\Support\Production\ProductionSpecificationService;
use App\Models\Production\ProductionQueue;
use App\Models\Production\WorkCenter;
use App\Enums\ProductionQueueStatus;
use App\Support\Production\JobCardSpecificationBridgeService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class JobCardManufacturingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_job_card_auto_links_production_specification_on_create(): void
    {
        [, , , $user, $salesOrder, $item] = $this->context(['production.view', 'production.create', 'sales_orders.edit']);

        app(ProductionSpecificationService::class)->createForSalesOrderItem($item, [
            'production_type' => ProductionType::Offset->value,
            'product_description' => 'Letterheads A4',
            'estimated_sheets' => 420,
        ], $user);

        $jobCard = ProductionJobCard::factory()->create([
            'company_id' => $salesOrder->company_id,
            'branch_id' => $salesOrder->branch_id,
            'sales_order_id' => $salesOrder->id,
            'customer_id' => $salesOrder->customer_id,
            'created_by' => $user->id,
            'production_type' => ProductionType::Mixed,
        ]);

        app(JobCardSpecificationBridgeService::class)->attachOnJobCardCreated($jobCard, $salesOrder->load('items'));

        $spec = ProductionSpecification::query()->where('sales_order_item_id', $item->id)->first();

        $this->assertNotNull($spec);
        $this->assertSame($jobCard->id, $spec->production_job_card_id);
        $this->assertSame(ProductionType::Offset, $jobCard->fresh()->production_type);
    }

    public function test_manufacturing_tab_renders_structured_specification(): void
    {
        [, , , $user, $salesOrder, $item] = $this->context(['production.view', 'production.create', 'sales_orders.edit']);

        $jobCard = $this->createJobCardWithSpec($salesOrder, $user, $item, [
            'production_type' => ProductionType::Digital->value,
            'product_description' => 'Brochures DL',
            'quantity' => 1000,
            'ups' => 2,
            'estimated_sheets' => 500,
            'waste_allowance_percent' => 5,
        ]);

        $this->actingAs($user)
            ->get(route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'manufacturing']))
            ->assertOk()
            ->assertSee(__('Manufacturing'), false)
            ->assertSee('Brochures DL')
            ->assertSee('500')
            ->assertSee(__('Material summary'), false);

        $payload = app(Job360WorkspaceService::class)->build($jobCard, 'manufacturing');
        $this->assertTrue($payload['tab_data']['has_specification']);
    }

    public function test_legacy_job_card_without_specification_still_works(): void
    {
        [, , , $user, $salesOrder] = $this->context(['production.view', 'production.create']);

        $jobCard = ProductionJobCard::factory()->create([
            'company_id' => $salesOrder->company_id,
            'branch_id' => $salesOrder->branch_id,
            'sales_order_id' => $salesOrder->id,
            'customer_id' => $salesOrder->customer_id,
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->get(route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'manufacturing']))
            ->assertOk()
            ->assertSee(__('No structured Production Specification available.'), false);

        $presented = app(JobCardManufacturingPresenter::class)->present($jobCard);
        $this->assertFalse($presented['has_specification']);
    }

    public function test_manufacturing_presenter_includes_timeline_pipeline(): void
    {
        [, , , $user, $salesOrder, $item] = $this->context(['production.view', 'production.create', 'sales_orders.edit']);

        $jobCard = $this->createJobCardWithSpec($salesOrder, $user, $item, [
            'production_type' => ProductionType::Offset->value,
        ]);
        $pipeline = app(JobCardManufacturingPresenter::class)->present($jobCard)['timeline_pipeline'];

        $this->assertNotEmpty($pipeline);
        $this->assertSame(__('Created'), $pipeline[0]['label']);
    }

    public function test_job_360_overview_includes_manufacturing_summary(): void
    {
        [, , , $user, $salesOrder, $item] = $this->context(['production.view', 'production.create', 'sales_orders.edit']);

        $jobCard = $this->createJobCardWithSpec($salesOrder, $user, $item, [
            'product_description' => 'Posters A2',
            'quantity' => 200,
        ]);
        $payload = app(Job360WorkspaceService::class)->build($jobCard, 'overview');

        $this->assertTrue($payload['tab_data']['manufacturing_summary']['has_specification']);
        $this->assertSame('Posters A2', $payload['tab_data']['manufacturing_summary']['product']);
    }

    public function test_no_inventory_or_accounting_side_effects(): void
    {
        [, , , $user, $salesOrder, $item] = $this->context(['production.view', 'production.create', 'sales_orders.edit']);

        $movementsBefore = InventoryMovement::query()->count();
        $journalsBefore = Journal::query()->count();

        $this->createJobCardWithSpec($salesOrder, $user, $item, [
            'estimated_sheets' => 300,
        ]);

        $this->assertSame($movementsBefore, InventoryMovement::query()->count());
        $this->assertSame($journalsBefore, Journal::query()->count());
    }

    public function test_cross_tenant_job_manufacturing_blocked(): void
    {
        [$companyA, $branchA, , $userA, $salesOrder, $item] = $this->context(['production.view', 'production.create', 'sales_orders.edit']);

        $jobCard = $this->createJobCardWithSpec($salesOrder, $userA, $item, []);

        $companyB = Company::factory()->create();
        $branchB = Branch::factory()->create(['company_id' => $companyB->id]);
        $userB = User::factory()->create([
            'company_id' => $companyB->id,
            'default_branch_id' => $branchB->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $role = Role::findByName('Production', 'web');
        $role->syncPermissions(['production.view']);
        $userB->assignRole('Production');
        session(['active_company_id' => $companyB->id, 'active_branch_id' => $branchB->id]);

        $this->actingAs($userB)
            ->get(route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'manufacturing']))
            ->assertForbidden();
    }

    public function test_queue_presenter_includes_spec_summary(): void
    {
        [$company, $branch, , $user, $salesOrder, $item] = $this->context(['production.view', 'production.create', 'sales_orders.edit']);

        $jobCard = $this->createJobCardWithSpec($salesOrder, $user, $item, [
            'product_description' => 'Queue Product',
            'size' => 'A5',
            'ups' => 4,
        ]);

        $workCenter = WorkCenter::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'code' => 'WC-MFG',
            'name' => 'Manufacturing WC',
            'is_active' => true,
        ]);

        $queue = ProductionQueue::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'production_job_card_id' => $jobCard->id,
            'work_center_id' => $workCenter->id,
            'queue_position' => 1,
            'status' => ProductionQueueStatus::Queued,
        ]);

        $queue->load([
            'jobCard.productionSpecification.paperInventoryItem',
            'jobCard.inventoryItem',
            'workCenter',
            'assignedOperator',
        ]);

        $row = app(ProductionQueueWorkspaceService::class)->presentRow($queue, $user);

        $this->assertNotNull($row['spec_summary']);
        $this->assertSame('Queue Product', $row['spec_summary']['product']);
        $this->assertSame(4, $row['spec_summary']['ups']);
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
            'customer_code' => 'C-MFG',
            'company_name' => 'Manufacturing Customer',
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
            'item_name' => 'Flyers',
            'description' => 'Promotional flyers',
            'quantity' => 500,
            'unit_price' => 2.5,
            'line_total' => 1250,
            'sort_order' => 1,
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        return [$company, $branch, $customer, $user, $salesOrder, $item];
    }

    /**
     * @param  array<string, mixed>  $specAttributes
     */
    protected function createJobCardWithSpec(
        SalesOrder $salesOrder,
        User $user,
        SalesOrderItem $item,
        array $specAttributes,
    ): ProductionJobCard {
        app(ProductionSpecificationService::class)->createForSalesOrderItem($item, $specAttributes, $user);

        $jobCard = ProductionJobCard::factory()->create([
            'company_id' => $salesOrder->company_id,
            'branch_id' => $salesOrder->branch_id,
            'sales_order_id' => $salesOrder->id,
            'customer_id' => $salesOrder->customer_id,
            'quotation_id' => $salesOrder->quotation_id,
            'artwork_request_id' => $salesOrder->artwork_request_id,
            'created_by' => $user->id,
        ]);

        app(JobCardSpecificationBridgeService::class)->attachOnJobCardCreated(
            $jobCard,
            $salesOrder->load('items'),
        );

        return $jobCard->fresh(['productionSpecification']);
    }
}
