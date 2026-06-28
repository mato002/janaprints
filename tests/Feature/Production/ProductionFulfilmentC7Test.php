<?php

namespace Tests\Feature\Production;

use App\Enums\ArtworkApprovalDecision;
use App\Enums\ArtworkRequestStatus;
use App\Enums\CustomerStatus;
use App\Enums\DomainCommunicationEvent;
use App\Enums\FulfilmentMethod;
use App\Enums\FulfilmentStatus;
use App\Enums\ProductionJobCardStatus;
use App\Enums\QuotationStatus;
use App\Events\Communications\DomainCommunicationEventRaised;
use App\Models\Artwork\ArtworkApproval;
use App\Models\Artwork\ArtworkRequest;
use App\Models\Artwork\ArtworkVersion;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Inventory\InventoryItem;
use App\Models\Production\ProductionFulfilment;
use App\Models\Production\ProductionJobCard;
use App\Models\Sales\Quotation;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use App\Services\Crm\CustomerTimelineService;
use App\Support\Production\ProductionFulfilmentService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Spatie\Permission\Models\Role;
use Tests\Feature\Dispatch\Concerns\InteractsWithDispatchInventory;
use Tests\TestCase;

class ProductionFulfilmentC7Test extends TestCase
{
    use InteractsWithDispatchInventory;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_mark_ready_for_collection(): void
    {
        [$company, $branch, , $user, $jobCard] = $this->fulfilmentContext(FulfilmentMethod::Collection);
        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);
        $jobCard->update(['status' => ProductionJobCardStatus::ReadyForDispatch]);

        $this->actingAs($user)
            ->post(route('admin.production.job-cards.fulfilment.ready-for-collection', $jobCard), [
                'collection_notes' => 'Counter A',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('production_fulfilments', [
            'production_job_card_id' => $jobCard->id,
            'status' => FulfilmentStatus::ReadyForCollection->value,
            'collection_notes' => 'Counter A',
        ]);
    }

    public function test_collection_confirmation_sets_invoice_ready(): void
    {
        [$company, $branch, , $user, $jobCard] = $this->fulfilmentContext(FulfilmentMethod::Collection);
        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);
        $jobCard->update(['status' => ProductionJobCardStatus::ReadyForDispatch]);

        $service = app(ProductionFulfilmentService::class);
        $fulfilment = $service->markReadyForCollection($jobCard, $user->id);

        $this->actingAs($user)
            ->post(route('admin.production.job-cards.fulfilment.confirm-collection', [$jobCard, $fulfilment]), [
                'collected_by_name' => 'John Collector',
                'collector_phone' => '0712345678',
            ])
            ->assertRedirect();

        $fulfilment->refresh();
        $this->assertSame(FulfilmentStatus::Collected, $fulfilment->status);
        $this->assertTrue($fulfilment->invoice_ready);
    }

    public function test_delivery_creation_and_dispatch(): void
    {
        $this->seedDispatchInventoryEnvironment();
        [$jobCard, $user] = $this->createEligibleDispatchJobWithFg();
        Role::findByName('Production', 'web')->givePermissionTo('production.complete');
        $jobCard->salesOrder->update(['fulfilment_method' => FulfilmentMethod::Delivery]);

        $this->actingAs($user)
            ->post(route('admin.production.job-cards.fulfilment.create-delivery', $jobCard), [
                'recipient_name' => 'Jane Recipient',
                'recipient_phone' => '0700111222',
                'delivery_address' => '123 Main St',
                'dispatch_date' => now()->toDateString(),
            ])
            ->assertRedirect();

        $fulfilment = ProductionFulfilment::query()->where('production_job_card_id', $jobCard->id)->first();
        $this->assertNotNull($fulfilment);
        $this->assertSame(FulfilmentStatus::Dispatched, $fulfilment->status);
        $this->assertSame('Jane Recipient', $fulfilment->recipient_name);
    }

    public function test_delivery_confirmation_sets_invoice_ready(): void
    {
        Event::fake([DomainCommunicationEventRaised::class]);

        [$company, $branch, , $user, $jobCard] = $this->fulfilmentContext(FulfilmentMethod::Delivery);
        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);
        $jobCard->update(['status' => ProductionJobCardStatus::ReadyForDispatch]);

        $fulfilment = ProductionFulfilment::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'sales_order_id' => $jobCard->sales_order_id,
            'production_job_card_id' => $jobCard->id,
            'fulfilment_method' => FulfilmentMethod::Delivery,
            'status' => FulfilmentStatus::Dispatched,
            'recipient_name' => 'Jane',
            'dispatched_at' => now(),
        ]);

        $this->actingAs($user)
            ->post(route('admin.production.job-cards.fulfilment.confirm-delivery', [$jobCard, $fulfilment]), [
                'received_by' => 'Jane',
                'signature_name' => 'Jane Doe',
            ])
            ->assertRedirect();

        $fulfilment->refresh();
        $this->assertSame(FulfilmentStatus::Delivered, $fulfilment->status);
        $this->assertTrue($fulfilment->invoice_ready);
    }

    public function test_ready_for_collection_notification_dispatched(): void
    {
        Event::fake([DomainCommunicationEventRaised::class]);

        [$company, $branch, , $user, $jobCard] = $this->fulfilmentContext(FulfilmentMethod::Collection);
        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);
        $jobCard->update(['status' => ProductionJobCardStatus::ReadyForDispatch]);

        $this->actingAs($user)
            ->post(route('admin.production.job-cards.fulfilment.ready-for-collection', $jobCard));

        Event::assertDispatched(DomainCommunicationEventRaised::class, function (DomainCommunicationEventRaised $event) {
            return $event->event === DomainCommunicationEvent::ReadyForCollection;
        });
    }

    public function test_customer_timeline_includes_fulfilment_history(): void
    {
        [$company, $branch, $customer, $user, $jobCard] = $this->fulfilmentContext(FulfilmentMethod::Collection);
        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);
        $jobCard->update(['status' => ProductionJobCardStatus::ReadyForDispatch]);

        $service = app(ProductionFulfilmentService::class);
        $fulfilment = $service->markReadyForCollection($jobCard, $user->id);
        $service->confirmCollection($fulfilment, $user->id, ['collected_by_name' => 'Collector']);

        $payload = app(CustomerTimelineService::class)->paginate($customer, CustomerTimelineService::FILTER_DISPATCH);
        $types = $payload['events']->getCollection()->map(fn ($e) => $e->eventType)->unique()->values()->all();

        $this->assertContains('READY_FOR_COLLECTION', $types);
        $this->assertContains('COLLECTED', $types);
    }

    public function test_tenant_isolation_on_fulfilments(): void
    {
        $companyA = Company::factory()->create();
        $branchA = Branch::factory()->create(['company_id' => $companyA->id]);
        $companyB = Company::factory()->create();
        $branchB = Branch::factory()->create(['company_id' => $companyB->id]);

        ProductionFulfilment::query()->create([
            'company_id' => $companyA->id,
            'branch_id' => $branchA->id,
            'production_job_card_id' => ProductionJobCard::factory()->create([
                'company_id' => $companyA->id,
                'branch_id' => $branchA->id,
            ])->id,
            'status' => FulfilmentStatus::ReadyForCollection,
        ]);

        app()->instance(\App\Support\TenantContext::class, new \App\Support\TenantContext($companyB, $branchB));

        $this->assertEquals(0, ProductionFulfilment::query()->forTenant()->count());
    }

    public function test_branch_isolation_on_fulfilments(): void
    {
        $company = Company::factory()->create();
        $branchA = Branch::factory()->create(['company_id' => $company->id]);
        $branchB = Branch::factory()->create(['company_id' => $company->id]);

        ProductionFulfilment::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branchA->id,
            'production_job_card_id' => ProductionJobCard::factory()->create([
                'company_id' => $company->id,
                'branch_id' => $branchA->id,
            ])->id,
            'status' => FulfilmentStatus::Dispatched,
        ]);

        app()->instance(\App\Support\TenantContext::class, new \App\Support\TenantContext($company, $branchB));

        $this->assertEquals(0, ProductionFulfilment::query()->forTenant()->count());
    }

    /**
     * @return array{0: Company, 1: Branch, 2: Customer, 3: User, 4: ProductionJobCard}
     */
    protected function fulfilmentContext(FulfilmentMethod $method): array
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $customer = Customer::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'status' => CustomerStatus::Active,
        ]);
        $user = $this->fulfilmentUser($company, $branch);

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
            'current_version' => 1,
        ]);

        ArtworkVersion::query()->create([
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
            'artwork_version_id' => $artwork->versions()->first()->id,
            'approved_by' => $user->id,
            'decision' => ArtworkApprovalDecision::Approved,
        ]);

        $salesOrder = SalesOrder::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'quotation_id' => $quotation->id,
            'artwork_request_id' => $artwork->id,
            'created_by' => $user->id,
            'fulfilment_method' => $method,
        ]);

        $jobCard = ProductionJobCard::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'sales_order_id' => $salesOrder->id,
            'customer_id' => $customer->id,
            'created_by' => $user->id,
        ]);

        return [$company, $branch, $customer, $user, $jobCard];
    }

    protected function fulfilmentUser(Company $company, Branch $branch): User
    {
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $role = Role::findByName('Production', 'web');
        $role->syncPermissions([
            'production.view', 'production.complete', 'dispatch.view', 'dispatch.create', 'dispatch.dispatch',
        ]);
        $user->assignRole('Production');

        return $user;
    }
}
