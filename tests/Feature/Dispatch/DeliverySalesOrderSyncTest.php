<?php

namespace Tests\Feature\Dispatch;

use App\Enums\ArtworkApprovalDecision;
use App\Enums\ArtworkRequestStatus;
use App\Enums\Dispatch\DeliveryNoteStatus;
use App\Enums\ProductionJobCardStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Artwork\ArtworkApproval;
use App\Models\Artwork\ArtworkRequest;
use App\Models\Artwork\ArtworkVersion;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Production\ProductionJobCard;
use App\Models\Sales\Quotation;
use App\Models\Sales\SalesOrder;
use App\Models\Sales\SalesOrderItem;
use App\Models\User;
use App\Services\Dispatch\DeliveryNoteService;
use App\Support\Dispatch\DeliverySalesOrderSyncService;
use App\Support\Platform\SystemSettingsService;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\PlatformConfigurationSeeder;
use Database\Seeders\ProductionFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DeliverySalesOrderSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        $this->seed(PlatformConfigurationSeeder::class);
        $this->seed(ProductionFoundationSeeder::class);
    }

    public function test_delivery_syncs_sales_order_to_delivered_and_commercially_closed(): void
    {
        [, , , $user, $salesOrder, $job] = $this->deliveryContext();

        $salesOrder->update(['status' => SalesOrderStatus::ReadyForDispatch]);
        $job->update(['status' => ProductionJobCardStatus::ReadyForDispatch]);

        $service = app(DeliveryNoteService::class);
        $note = $service->createDraftFromJobCard($job);
        $service->dispatch($note, $user->id);
        $service->deliver($note, $user->id, ['recipient_name' => 'Site Manager']);

        $this->assertSame(DeliveryNoteStatus::Delivered, $note->fresh()->status);
        $this->assertSame(SalesOrderStatus::Closed, $salesOrder->fresh()->status);
    }

    public function test_proof_of_delivery_is_required_at_delivery(): void
    {
        [, , , $user, , $job] = $this->deliveryContext();
        $service = app(DeliveryNoteService::class);
        $note = $service->createDraftFromJobCard($job);
        $service->dispatch($note, $user->id);

        $this->expectException(ValidationException::class);
        $service->deliver($note, $user->id);
    }

    public function test_sales_order_not_synced_without_proof_of_delivery(): void
    {
        [, , , $user, $salesOrder, $job] = $this->deliveryContext();
        $salesOrder->update(['status' => SalesOrderStatus::ReadyForDispatch]);

        $service = app(DeliveryNoteService::class);
        $note = $service->createDraftFromJobCard($job);
        $service->dispatch($note, $user->id);

        $note->update([
            'status' => DeliveryNoteStatus::Delivered,
            'delivered_by' => $user->id,
            'delivered_at' => now(),
            'recipient_name' => null,
            'invoice_ready' => true,
        ]);

        app(DeliverySalesOrderSyncService::class)->syncFromDeliveredNote($note->fresh());

        $this->assertSame(SalesOrderStatus::ReadyForDispatch, $salesOrder->fresh()->status);
    }

    public function test_sales_order_not_synced_when_linked_job_incomplete(): void
    {
        [, , , $user, $salesOrder, $job] = $this->deliveryContext();
        $salesOrder->update(['status' => SalesOrderStatus::ReadyForDispatch]);

        $service = app(DeliveryNoteService::class);
        $note = $service->createDraftFromJobCard($job);
        $service->dispatch($note, $user->id);

        ProductionJobCard::withoutEvents(function () use ($job, $salesOrder): void {
            $job->update(['status' => ProductionJobCardStatus::InProduction]);
            $salesOrder->update(['status' => SalesOrderStatus::InProduction]);
        });

        $this->expectException(ValidationException::class);
        $service->deliver($note->fresh(), $user->id, ['recipient_name' => 'Receiver']);

        $this->assertSame(SalesOrderStatus::InProduction, $salesOrder->fresh()->status);
    }

    public function test_multi_job_order_waits_for_all_delivery_notes(): void
    {
        Schema::table('production_job_cards', function ($table): void {
            $table->dropUnique(['sales_order_id']);
        });

        [, , , $user, $salesOrder, $jobA] = $this->deliveryContext();
        $salesOrder->update(['status' => SalesOrderStatus::ReadyForDispatch]);
        $jobA->update(['status' => ProductionJobCardStatus::ReadyForDispatch]);

        $jobB = ProductionJobCard::factory()->create([
            'company_id' => $jobA->company_id,
            'branch_id' => $jobA->branch_id,
            'sales_order_id' => $salesOrder->id,
            'customer_id' => $jobA->customer_id,
            'job_card_number' => 'JOB-MULTI-'.uniqid(),
            'status' => ProductionJobCardStatus::ReadyForDispatch,
            'created_by' => $user->id,
        ]);

        $service = app(DeliveryNoteService::class);

        $noteA = $service->createDraftFromJobCard($jobA);
        $service->dispatch($noteA, $user->id);
        $service->deliver($noteA, $user->id, ['recipient_name' => 'Receiver A']);

        $this->assertSame(SalesOrderStatus::ReadyForDispatch, $salesOrder->fresh()->status);

        $noteB = $service->createDraftFromJobCard($jobB);
        $service->dispatch($noteB, $user->id);
        $service->deliver($noteB, $user->id, ['recipient_name' => 'Receiver B']);

        $this->assertSame(SalesOrderStatus::Closed, $salesOrder->fresh()->status);
    }

    public function test_manual_sales_order_deliver_requires_delivery_truth(): void
    {
        [, , , $user, $salesOrder] = $this->deliveryContext();
        $salesOrder->update(['status' => SalesOrderStatus::ReadyForDispatch]);

        $role = Role::create(['name' => 'Sales Deliver Tester '.uniqid(), 'guard_name' => 'web']);
        $role->syncPermissions(['sales_orders.view', 'sales_orders.production']);
        $user->assignRole($role);

        $this->actingAs($user)
            ->post(route('admin.sales-orders.deliver', $salesOrder))
            ->assertSessionHasErrors('status');
    }

    /**
     * @return array{0: Company, 1: Branch, 2: Customer, 3: User, 4: SalesOrder, 5: ProductionJobCard}
     */
    protected function deliveryContext(): array
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();
        $customer = Customer::factory()->create(['company_id' => $company->id, 'branch_id' => $branch->id]);
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);
        $role = Role::create(['name' => 'Delivery Sync Tester '.uniqid(), 'guard_name' => 'web']);
        $role->syncPermissions([
            'dispatch.view', 'dispatch.create', 'dispatch.dispatch', 'dispatch.deliver',
            'production.view', 'production.create',
        ]);
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

        app(SystemSettingsService::class)->set(
            'production_qc_required',
            false,
            $company->id,
            $branch->id,
            'boolean',
        );

        $job = ProductionJobCard::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'sales_order_id' => $salesOrder->id,
            'customer_id' => $customer->id,
            'job_card_number' => 'JOB-SYNC-'.uniqid(),
            'status' => ProductionJobCardStatus::ReadyForDispatch,
            'created_by' => $user->id,
        ]);

        return [$company, $branch, $customer, $user, $salesOrder, $job->fresh()];
    }
}
