<?php

namespace Tests\Feature\Dispatch;

use App\Enums\ArtworkApprovalDecision;
use App\Enums\ArtworkRequestStatus;
use App\Enums\Dispatch\DeliveryNoteStatus;
use App\Enums\DocumentType;
use App\Enums\ProductionJobCardStatus;
use App\Enums\ProductionPriority;
use App\Enums\ProductionType;
use App\Enums\SalesOrderStatus;
use App\Models\Artwork\ArtworkApproval;
use App\Models\Artwork\ArtworkRequest;
use App\Models\Artwork\ArtworkVersion;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Dispatch\DeliveryNote;
use App\Models\Production\ProductionJobCard;
use App\Models\Sales\Quotation;
use App\Models\Sales\SalesOrder;
use App\Models\Sales\SalesOrderItem;
use App\Models\User;
use App\Services\Crm\CustomerTimelineService;
use App\Services\Dispatch\DeliveryNoteService;
use Database\Seeders\InventoryVirtualWarehouseSeeder;
use Database\Seeders\JanaPrintsAccountingPeriodsSeeder;
use Database\Seeders\JanaPrintsChartOfAccountsSeeder;
use Database\Seeders\JanaPrintsPostingEngineSeeder;
use Database\Seeders\InventoryFoundationSeeder;
use Database\Seeders\GlAccountTypeSeeder;
use Tests\Feature\Dispatch\Concerns\InteractsWithDispatchInventory;
use App\Services\Production\JobProductionControlService;
use App\Services\Production\JobTimelineService;
use App\Support\Platform\NumberGenerator;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\PlatformConfigurationSeeder;
use Database\Seeders\ProductionFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DeliveryFoundationTest extends TestCase
{
    use InteractsWithDispatchInventory;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        $this->seed(PlatformConfigurationSeeder::class);
        $this->seed(ProductionFoundationSeeder::class);
        $this->seed(InventoryFoundationSeeder::class);
        $this->seed(InventoryVirtualWarehouseSeeder::class);
        $this->seed(GlAccountTypeSeeder::class);
        $this->seed(JanaPrintsChartOfAccountsSeeder::class);
        $this->seed(JanaPrintsAccountingPeriodsSeeder::class);
        $this->seed(JanaPrintsPostingEngineSeeder::class);
    }

    public function test_delivery_note_number_uses_dn_prefix(): void
    {
        [$company, $branch] = $this->tenantPair();
        $number = app(NumberGenerator::class)->generate(DocumentType::DeliveryNote, $company->id, $branch->id);

        $this->assertStringContainsString('DN', $number);
    }

    public function test_create_draft_from_ready_job(): void
    {
        [$company, $branch, $customer, $user, $job] = $this->readyJobContext();

        $note = app(DeliveryNoteService::class)->createDraftFromJobCard($job);

        $this->assertSame(DeliveryNoteStatus::Draft, $note->status);
        $this->assertSame($job->id, $note->production_job_card_id);
        $this->assertNotEmpty($note->items);
    }

    public function test_dispatch_eligibility_enforced(): void
    {
        [, , , , $job] = $this->readyJobContext();
        $job->update(['status' => ProductionJobCardStatus::Completed]);

        $this->expectException(ValidationException::class);
        app(DeliveryNoteService::class)->createDraftFromJobCard($job);
    }

    public function test_dispatch_and_deliver_lifecycle(): void
    {
        [$note, , $user] = $this->prepareDraftNoteWithFg();
        $service = app(DeliveryNoteService::class);

        $service->dispatch($note, $user->id);
        $note->refresh();
        $this->assertSame(DeliveryNoteStatus::Dispatched, $note->status);

        $service->deliver($note, $user->id, ['recipient_name' => 'Jane Doe']);
        $note->refresh();
        $this->assertSame(DeliveryNoteStatus::Delivered, $note->status);
        $this->assertTrue($note->invoice_ready);
        $this->assertTrue($note->isInvoiceable());
        $this->assertNotNull($note->posted_journal_id);

        $this->expectException(ValidationException::class);
        $service->updateDraft($note, ['recipient_name' => 'Changed']);
    }

    public function test_cancelled_note_remains_auditable(): void
    {
        [, , , $user, $job] = $this->readyJobContext();
        $note = app(DeliveryNoteService::class)->createDraftFromJobCard($job);
        app(DeliveryNoteService::class)->cancel($note, 'Test cancel');

        $this->assertSame(DeliveryNoteStatus::Cancelled, $note->fresh()->status);
        $this->assertNotNull(DeliveryNote::withTrashed()->find($note->id));
    }

    public function test_customer_timeline_includes_delivery_note_events(): void
    {
        [$note, , $user] = $this->prepareDraftNoteWithFg();
        $service = app(DeliveryNoteService::class);
        $customer = $note->customer;
        $service->dispatch($note, $user->id);
        $service->deliver($note, $user->id);

        $payload = app(CustomerTimelineService::class)->paginate($customer, CustomerTimelineService::FILTER_DISPATCH);
        $types = $payload['events']->getCollection()->map(fn ($e) => $e->eventType)->unique()->values()->all();

        $this->assertContains('DELIVERY_NOTE_CREATED', $types);
        $this->assertContains('DISPATCHED', $types);
        $this->assertContains('DELIVERED', $types);
    }

    public function test_job_timeline_includes_delivery_events(): void
    {
        [$note, , $user, $job] = $this->prepareDraftNoteWithFg();
        app(DeliveryNoteService::class)->dispatch($note, $user->id);

        $payload = app(JobTimelineService::class)->paginate($job, JobTimelineService::FILTER_DISPATCH);
        $types = $payload['events']->getCollection()->map(fn ($e) => $e->eventType)->all();

        $this->assertContains('DELIVERY_NOTE_CREATED', $types);
        $this->assertContains('DISPATCHED', $types);
    }

    public function test_customer_delivery_history_tab(): void
    {
        [, , $customer, $user, $job] = $this->readyJobContext();
        app(DeliveryNoteService::class)->createDraftFromJobCard($job);

        $this->actingAs($user)
            ->get(route('admin.crm.customers.show', ['customer' => $customer, 'tab' => 'deliveries']))
            ->assertOk()
            ->assertSee('DN', false);
    }

    public function test_authorization_for_dispatch_actions(): void
    {
        [$company, $branch, , $user, $job] = $this->readyJobContext();
        $note = app(DeliveryNoteService::class)->createDraftFromJobCard($job);

        $denied = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);
        $role = Role::create(['name' => 'Dispatch Viewer '.uniqid(), 'guard_name' => 'web']);
        $role->syncPermissions(['dispatch.view']);
        $denied->assignRole($role);
        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($denied)
            ->post(route('admin.dispatch.delivery-notes.dispatch', $note))
            ->assertForbidden();
    }

    public function test_tenant_isolation_on_delivery_note_show(): void
    {
        [, , , $user, $job] = $this->readyJobContext();
        $note = app(DeliveryNoteService::class)->createDraftFromJobCard($job);

        $otherCompany = Company::factory()->create();
        $otherBranch = Branch::factory()->create(['company_id' => $otherCompany->id]);
        $otherUser = User::factory()->create([
            'company_id' => $otherCompany->id,
            'default_branch_id' => $otherBranch->id,
            'email_verified_at' => now(),
        ]);
        $role = Role::create(['name' => 'Other Dispatch '.uniqid(), 'guard_name' => 'web']);
        $role->syncPermissions(['dispatch.view']);
        $otherUser->assignRole($role);

        session(['active_company_id' => $otherCompany->id, 'active_branch_id' => $otherBranch->id]);

        $this->actingAs($otherUser)
            ->get(route('admin.dispatch.delivery-notes.show', $note))
            ->assertForbidden();
    }

    /**
     * @param  list<string>  $permissions
     * @return array{0: Company, 1: Branch, 2: Customer, 3: User, 4: ProductionJobCard}
     */
    protected function readyJobContext(array $permissions = []): array
    {
        $permissions = $permissions === [] ? [
            'dispatch.view', 'dispatch.create', 'dispatch.dispatch', 'dispatch.deliver', 'dispatch.cancel',
            'production.view', 'production.create', 'production.complete', 'crm.customers.view',
        ] : $permissions;

        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();
        $customer = Customer::factory()->create(['company_id' => $company->id, 'branch_id' => $branch->id]);
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);
        $role = Role::create(['name' => 'Dispatch Tester '.uniqid(), 'guard_name' => 'web']);
        $role->syncPermissions($permissions);
        $user->assignRole($role);

        $quotation = Quotation::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'prepared_by' => $user->id,
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
            'created_by' => $user->id,
            'status' => SalesOrderStatus::Confirmed,
        ]);

        SalesOrderItem::query()->create([
            'sales_order_id' => $salesOrder->id,
            'item_name' => 'Print batch',
            'description' => 'Branded shirts',
            'quantity' => 100,
            'unit_price' => 10,
            'line_total' => 1000,
            'sort_order' => 1,
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $salesOrder->refresh();
        $this->assertSame($artwork->id, $salesOrder->artwork_request_id);

        $job = ProductionJobCard::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'sales_order_id' => $salesOrder->id,
            'customer_id' => $customer->id,
            'job_card_number' => 'JOB-DN-'.uniqid(),
            'status' => ProductionJobCardStatus::ReadyForDispatch,
            'created_by' => $user->id,
        ]);

        $eligibility = app(JobProductionControlService::class)->deliveryNoteCreationEligibility($job->fresh());
        $this->assertTrue($eligibility['eligible'], implode(' ', $eligibility['blockers']));

        return [$company, $branch, $customer, $user, $job->fresh()];
    }

    /**
     * @return array{0: Company, 1: Branch}
     */
    protected function tenantPair(): array
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();

        return [$company, $branch];
    }
}
