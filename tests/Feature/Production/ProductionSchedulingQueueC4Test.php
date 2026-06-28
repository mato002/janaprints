<?php

namespace Tests\Feature\Production;

use App\Enums\ArtworkApprovalDecision;
use App\Enums\ArtworkRequestStatus;
use App\Enums\CustomerStatus;
use App\Enums\DomainCommunicationEvent;
use App\Enums\ProductionJobCardStatus;
use App\Enums\ProductionPriority;
use App\Enums\ProductionQueueStatus;
use App\Enums\QuotationStatus;
use App\Enums\SalesOrderStatus;
use App\Events\Communications\DomainCommunicationEventRaised;
use App\Events\Production\JobCardStatusChanged;
use App\Listeners\Production\DispatchProductionCommunication;
use App\Models\Artwork\ArtworkApproval;
use App\Models\Artwork\ArtworkRequest;
use App\Models\Artwork\ArtworkVersion;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Crm\CustomerArtwork;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\ProductProductionRouteStep;
use App\Models\Production\JobCardRouteStep;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionQueue;
use App\Models\Production\WorkCenter;
use App\Models\Sales\Quotation;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use App\Support\Production\ProductionQueueOrderingService;
use App\Support\Platform\SystemSettingsService;
use App\Enums\CustomerArtworkStatus;
use App\Enums\CustomerArtworkType;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductionSchedulingQueueC4Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_queue_orders_by_priority_then_due_date_then_created(): void
    {
        [$company, $branch, , $user, $workCenter] = $this->c4Context();

        $jobs = [
            ['priority' => ProductionPriority::Low, 'required_date' => now()->addDays(5), 'created' => now()->subDays(2)],
            ['priority' => ProductionPriority::Urgent, 'required_date' => now()->addDays(10), 'created' => now()->subDay()],
            ['priority' => ProductionPriority::High, 'required_date' => now()->addDays(2), 'created' => now()],
        ];

        foreach ($jobs as $index => $meta) {
            $job = ProductionJobCard::factory()->create([
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'priority' => $meta['priority'],
                'required_date' => $meta['required_date'],
                'created_at' => $meta['created'],
                'status' => ProductionJobCardStatus::Draft,
            ]);

            ProductionQueue::query()->create([
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'production_job_card_id' => $job->id,
                'work_center_id' => $workCenter->id,
                'queue_position' => $index + 1,
                'status' => ProductionQueueStatus::Waiting,
            ]);
        }

        $orderedIds = ProductionQueue::query()
            ->where('work_center_id', $workCenter->id)
            ->tap(fn ($q) => app(ProductionQueueOrderingService::class)->applyPriorityOrdering($q))
            ->pluck('production_job_card_id')
            ->all();

        $urgentId = ProductionJobCard::query()->where('priority', ProductionPriority::Urgent)->value('id');
        $highId = ProductionJobCard::query()->where('priority', ProductionPriority::High)->value('id');
        $lowId = ProductionJobCard::query()->where('priority', ProductionPriority::Low)->value('id');

        $this->assertSame([$urgentId, $highId, $lowId], $orderedIds);
    }

    public function test_route_steps_inherit_work_center_from_catalog(): void
    {
        [$company, $branch, $customer, $user, $workCenter, $item] = $this->c4Context(withItem: true);

        ProductProductionRouteStep::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'inventory_item_id' => $item->id,
            'work_center_id' => $workCenter->id,
            'step_name' => 'Printing',
            'sequence' => 1,
            'is_active' => true,
        ]);

        $salesOrder = $this->directSalesOrder($company, $branch, $customer, $user, $item);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);
        app()->instance(\App\Support\TenantContext::class, new \App\Support\TenantContext($company, $branch));

        $this->actingAs($user)->post(route('admin.production.job-cards.store'), [
            'sales_order_id' => $salesOrder->id,
            'production_type' => 'digital',
            'priority' => 'normal',
        ])->assertRedirect();

        $jobCard = ProductionJobCard::query()->where('sales_order_id', $salesOrder->id)->firstOrFail();
        $step = JobCardRouteStep::query()->where('production_job_card_id', $jobCard->id)->first();

        $this->assertNotNull($step);
        $this->assertEquals($workCenter->id, $step->work_center_id);
        $this->assertDatabaseHas('production_queues', [
            'production_job_card_id' => $jobCard->id,
            'work_center_id' => $workCenter->id,
            'status' => ProductionQueueStatus::Waiting->value,
        ]);
    }

    public function test_scheduling_sets_planned_dates_when_auto_schedule_enabled(): void
    {
        [$company, $branch, $customer, $user, $workCenter, $item] = $this->c4Context(withItem: true);

        $this->enableAutoSchedule($company->id, $branch->id);

        ProductProductionRouteStep::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'inventory_item_id' => $item->id,
            'work_center_id' => $workCenter->id,
            'step_name' => 'Binding',
            'sequence' => 1,
            'is_active' => true,
        ]);

        $salesOrder = $this->directSalesOrder($company, $branch, $customer, $user, $item, [
            'required_date' => now()->addDays(3)->toDateString(),
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);
        app()->instance(\App\Support\TenantContext::class, new \App\Support\TenantContext($company, $branch));

        $this->actingAs($user)->post(route('admin.production.job-cards.store'), [
            'sales_order_id' => $salesOrder->id,
            'production_type' => 'digital',
            'priority' => 'high',
            'estimated_duration_minutes' => 120,
        ])->assertRedirect();

        $jobCard = ProductionJobCard::query()->where('sales_order_id', $salesOrder->id)->firstOrFail();

        $this->assertNotNull($jobCard->planned_start_date);
        $this->assertNotNull($jobCard->planned_end_date);
        $this->assertEquals($salesOrder->required_date->toDateString(), $jobCard->required_date?->toDateString());
    }

    public function test_repeat_customer_order_creates_direct_sales_order(): void
    {
        Event::fake([DomainCommunicationEventRaised::class]);

        [$company, $branch, $customer, $user, , $source] = $this->c4Context(withSalesOrder: true);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);
        app()->instance(\App\Support\TenantContext::class, new \App\Support\TenantContext($company, $branch));

        $this->actingAs($user)
            ->post(route('admin.crm.customers.repeat-order', [$customer, $source]))
            ->assertRedirect();

        $repeat = SalesOrder::query()
            ->where('repeat_source_sales_order_id', $source->id)
            ->first();

        $this->assertNotNull($repeat);
        $this->assertTrue($repeat->is_direct_order);
        $this->assertEquals(SalesOrderStatus::Confirmed, $repeat->status);
    }

    public function test_job_queued_status_dispatches_communication_event(): void
    {
        Event::fake([DomainCommunicationEventRaised::class]);

        [$company, $branch] = $this->c4Context();
        $jobCard = ProductionJobCard::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'status' => ProductionJobCardStatus::Draft,
        ]);

        app(DispatchProductionCommunication::class)->handle(
            new JobCardStatusChanged($jobCard, ProductionJobCardStatus::Queued),
        );

        Event::assertDispatched(DomainCommunicationEventRaised::class, function (DomainCommunicationEventRaised $event) {
            return $event->event === DomainCommunicationEvent::JobQueued;
        });
    }

    public function test_queue_entries_are_tenant_isolated(): void
    {
        $companyA = Company::factory()->create();
        $branchA = Branch::factory()->create(['company_id' => $companyA->id]);
        $companyB = Company::factory()->create();
        $branchB = Branch::factory()->create(['company_id' => $companyB->id]);

        $wcA = WorkCenter::query()->create([
            'company_id' => $companyA->id,
            'branch_id' => $branchA->id,
            'name' => 'WC A',
            'code' => 'WCA',
            'is_active' => true,
        ]);
        $wcB = WorkCenter::query()->create([
            'company_id' => $companyB->id,
            'branch_id' => $branchB->id,
            'name' => 'WC B',
            'code' => 'WCB',
            'is_active' => true,
        ]);

        $jobA = ProductionJobCard::factory()->create([
            'company_id' => $companyA->id,
            'branch_id' => $branchA->id,
        ]);
        $jobB = ProductionJobCard::factory()->create([
            'company_id' => $companyB->id,
            'branch_id' => $branchB->id,
        ]);

        ProductionQueue::query()->create([
            'company_id' => $companyA->id,
            'branch_id' => $branchA->id,
            'production_job_card_id' => $jobA->id,
            'work_center_id' => $wcA->id,
            'queue_position' => 1,
            'status' => ProductionQueueStatus::Waiting,
        ]);
        ProductionQueue::query()->create([
            'company_id' => $companyB->id,
            'branch_id' => $branchB->id,
            'production_job_card_id' => $jobB->id,
            'work_center_id' => $wcB->id,
            'queue_position' => 1,
            'status' => ProductionQueueStatus::Waiting,
        ]);

        session(['active_company_id' => $companyA->id, 'active_branch_id' => $branchA->id]);
        app()->instance(\App\Support\TenantContext::class, new \App\Support\TenantContext($companyA, $branchA));

        $count = ProductionQueue::query()->forTenant()->count();
        $this->assertEquals(1, $count);
    }

    public function test_work_center_crud_is_branch_scoped(): void
    {
        [$company, $branch, , $user] = $this->c4Context();

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);
        app()->instance(\App\Support\TenantContext::class, new \App\Support\TenantContext($company, $branch));

        $this->actingAs($user)
            ->post(route('admin.production.work-centers.store'), [
                'name' => 'Numbering',
                'code' => 'NUM',
                'description' => 'Serial numbering',
                'is_active' => 1,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('work_centers', [
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'code' => 'NUM',
        ]);
    }

    /**
     * @return array{0: Company, 1: Branch, 2: Customer, 3: User, 4: WorkCenter, 5?: InventoryItem, 6?: SalesOrder}
     */
    protected function c4Context(bool $withItem = false, bool $withSalesOrder = false): array
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $customer = Customer::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'status' => CustomerStatus::Active,
        ]);
        $user = $this->productionUser($company, $branch, [
            'production.view', 'production.create', 'production.edit',
            'sales_orders.create', 'crm.customers.view',
        ]);

        $workCenter = WorkCenter::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'name' => 'Digital Printing',
            'code' => 'DIGITAL',
            'is_active' => true,
        ]);

        $result = [$company, $branch, $customer, $user, $workCenter];

        if ($withItem) {
            $item = InventoryItem::factory()->create([
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'is_active' => true,
            ]);
            $result[] = $item;
        }

        if ($withSalesOrder) {
            $quotation = Quotation::factory()->create([
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'customer_id' => $customer->id,
                'prepared_by' => $user->id,
                'status' => QuotationStatus::Converted,
            ]);
            $artwork = ArtworkRequest::factory()->create([
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'customer_id' => $customer->id,
                'quotation_id' => $quotation->id,
                'requested_by' => $user->id,
                'status' => ArtworkRequestStatus::Approved,
            ]);
            $version = ArtworkVersion::query()->create([
                'artwork_request_id' => $artwork->id,
                'version_number' => 1,
                'file_path' => 'test.pdf',
                'original_name' => 'test.pdf',
                'uploaded_by' => $user->id,
            ]);
            ArtworkApproval::query()->create([
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'artwork_request_id' => $artwork->id,
                'artwork_version_id' => $version->id,
                'approved_by' => $user->id,
                'decision' => ArtworkApprovalDecision::Approved,
            ]);
            $salesOrder = SalesOrder::factory()->create([
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'customer_id' => $customer->id,
                'quotation_id' => $quotation->id,
                'artwork_request_id' => $artwork->id,
                'status' => SalesOrderStatus::Confirmed,
                'created_by' => $user->id,
            ]);
            $result[] = $salesOrder;
        }

        return $result;
    }

    protected function directSalesOrder(
        Company $company,
        Branch $branch,
        Customer $customer,
        User $user,
        InventoryItem $item,
        array $overrides = [],
    ): SalesOrder {
        $artwork = CustomerArtwork::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'artwork_name' => 'Repeat Logo',
            'artwork_type' => CustomerArtworkType::Logo,
            'version_number' => 1,
            'is_active_version' => true,
            'file_path' => 'artworks/logo.pdf',
            'file_name' => 'logo.pdf',
            'status' => CustomerArtworkStatus::Active,
            'uploaded_by' => $user->id,
            'uploaded_at' => now(),
        ]);

        return SalesOrder::factory()->create(array_merge([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'inventory_item_id' => $item->id,
            'uses_existing_artwork' => true,
            'customer_artwork_id' => $artwork->id,
            'is_direct_order' => true,
            'status' => SalesOrderStatus::Confirmed,
            'created_by' => $user->id,
            'required_date' => now()->addWeek()->toDateString(),
        ], $overrides));
    }

    protected function productionUser(Company $company, Branch $branch, array $permissions): User
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

    protected function enableAutoSchedule(int $companyId, int $branchId): void
    {
        app(SystemSettingsService::class)->set(
            'production_auto_schedule_on_create',
            true,
            $companyId,
            $branchId,
            'boolean',
        );
    }
}
