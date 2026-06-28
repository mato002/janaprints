<?php

namespace Tests\Feature\Production;

use App\Enums\ArtworkApprovalDecision;
use App\Enums\ArtworkRequestStatus;
use App\Enums\CustomerStatus;
use App\Enums\Dispatch\DeliveryNoteStatus;
use App\Enums\ProductionJobCardStatus;
use App\Enums\QuotationStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Artwork\ArtworkApproval;
use App\Models\Artwork\ArtworkRequest;
use App\Models\Artwork\ArtworkVersion;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionQueue;
use App\Models\Production\WorkCenter;
use App\Models\Sales\Quotation;
use App\Models\Sales\SalesOrder;
use App\Models\Sales\SalesOrderItem;
use App\Models\User;
use App\Services\Dispatch\DeliveryNoteService;
use App\Support\Dispatch\DeliverySalesOrderSyncService;
use App\Support\Platform\SystemSettingsService;
use App\Support\Production\ProductionQueueService;
use App\Support\Production\SalesOrderProductionBridgeService;
use Database\Seeders\ProductionFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SalesOrderJobCardStatusSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    #[DataProvider('jobToSalesOrderStatusMatrix')]
    public function test_every_job_status_transition_syncs_sales_order(
        ProductionJobCardStatus $from,
        ProductionJobCardStatus $to,
        SalesOrderStatus $salesOrderStart,
        SalesOrderStatus $expected,
    ): void {
        [, , , $user, $salesOrder] = $this->productionContext();
        $jobCard = $this->createJobCard($salesOrder, $user);

        $this->seed(ProductionFoundationSeeder::class);
        $salesOrder->update(['status' => $salesOrderStart]);
        $this->prepareJobForTransition($jobCard, $from);

        if ($to === ProductionJobCardStatus::Queued) {
            $this->ensureQueueEntry($jobCard->fresh());
        } else {
            $jobCard->fresh()->transitionTo($to);
        }

        app(SalesOrderProductionBridgeService::class)->syncSalesOrderStatus($jobCard->fresh(), $to);

        $this->assertEquals($expected, $salesOrder->fresh()->status);
        $this->assertTrue(app(SalesOrderProductionBridgeService::class)->isSynchronized($jobCard->fresh()));
    }

    /**
     * @return array<string, array{0: ProductionJobCardStatus, 1: ProductionJobCardStatus, 2: SalesOrderStatus, 3: SalesOrderStatus}>
     */
    public static function jobToSalesOrderStatusMatrix(): array
    {
        return [
            'queued' => [
                ProductionJobCardStatus::Draft,
                ProductionJobCardStatus::Queued,
                SalesOrderStatus::ReadyForProduction,
                SalesOrderStatus::ReadyForProduction,
            ],
            'in production' => [
                ProductionJobCardStatus::Queued,
                ProductionJobCardStatus::InProduction,
                SalesOrderStatus::ReadyForProduction,
                SalesOrderStatus::InProduction,
            ],
            'quality check' => [
                ProductionJobCardStatus::InProduction,
                ProductionJobCardStatus::QualityCheck,
                SalesOrderStatus::InProduction,
                SalesOrderStatus::InProduction,
            ],
            'production complete' => [
                ProductionJobCardStatus::QualityCheck,
                ProductionJobCardStatus::Completed,
                SalesOrderStatus::InProduction,
                SalesOrderStatus::Completed,
            ],
            'ready for dispatch' => [
                ProductionJobCardStatus::Completed,
                ProductionJobCardStatus::ReadyForDispatch,
                SalesOrderStatus::Completed,
                SalesOrderStatus::Completed,
            ],
            'rework returns to in production' => [
                ProductionJobCardStatus::QualityCheck,
                ProductionJobCardStatus::Rework,
                SalesOrderStatus::InProduction,
                SalesOrderStatus::InProduction,
            ],
            'on hold' => [
                ProductionJobCardStatus::InProduction,
                ProductionJobCardStatus::OnHold,
                SalesOrderStatus::InProduction,
                SalesOrderStatus::OnHold,
            ],
        ];
    }

    public function test_matrix_maps_to_valid_sales_order_statuses(): void
    {
        $valid = array_column(SalesOrderStatus::cases(), 'value');

        foreach (self::jobToSalesOrderStatusMatrix() as $case => $row) {
            [, , $salesOrderStart, $expected] = $row;

            $this->assertContains(
                $salesOrderStart->value,
                $valid,
                "Matrix case [{$case}] uses invalid start status: {$salesOrderStart->value}",
            );
            $this->assertContains(
                $expected->value,
                $valid,
                "Matrix case [{$case}] uses invalid expected status: {$expected->value}",
            );
        }
    }

    public function test_draft_job_card_creation_syncs_ready_for_production(): void
    {
        [, , , $user, $salesOrder] = $this->productionContext();

        $jobCard = $this->createJobCard($salesOrder, $user);

        app(SalesOrderProductionBridgeService::class)->syncSalesOrderStatus(
            $jobCard->fresh(),
            ProductionJobCardStatus::Draft,
        );

        $this->assertEquals(SalesOrderStatus::ReadyForProduction, $salesOrder->fresh()->status);
    }

    public function test_bidirectional_validation_passes_when_synchronized(): void
    {
        [, , , $user, $salesOrder] = $this->productionContext();
        $jobCard = $this->createJobCard($salesOrder, $user);

        $this->seed(ProductionFoundationSeeder::class);
        $salesOrder->update(['status' => SalesOrderStatus::ReadyForProduction]);
        $workCenter = WorkCenter::query()->where('company_id', $jobCard->company_id)->firstOrFail();
        app(ProductionQueueService::class)->enqueue($jobCard, $workCenter->id, 1);
        app(SalesOrderProductionBridgeService::class)->syncSalesOrderStatus(
            $jobCard->fresh(),
            ProductionJobCardStatus::Queued,
        );

        $bridge = app(SalesOrderProductionBridgeService::class);

        $this->assertTrue($bridge->isSynchronized($jobCard->fresh()));
        $bridge->assertSynchronized($jobCard->fresh());
        $this->addToAssertionCount(1);
    }

    public function test_bidirectional_validation_fails_when_desynchronized(): void
    {
        [, , , $user, $salesOrder] = $this->productionContext();
        $jobCard = $this->createJobCard($salesOrder, $user);

        ProductionJobCard::withoutEvents(function () use ($salesOrder, $jobCard): void {
            $salesOrder->update(['status' => SalesOrderStatus::Confirmed]);
            $jobCard->update(['status' => ProductionJobCardStatus::Queued]);
        });

        $bridge = app(SalesOrderProductionBridgeService::class);

        $this->assertFalse($bridge->isSynchronized($jobCard->fresh()));

        try {
            $bridge->assertSynchronized($jobCard->fresh());
            $this->fail('Expected validation exception for desynchronized statuses.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('status', $exception->errors());
        }
    }

    public function test_delivery_note_delivered_syncs_sales_order_to_delivered(): void
    {
        [$company, $branch, , $user, $salesOrder] = $this->productionContext();
        app(SystemSettingsService::class)->set(
            'production_qc_required',
            false,
            $company->id,
            $branch->id,
            'boolean',
        );
        $jobCard = $this->createJobCard($salesOrder, $user);

        SalesOrderItem::query()->create([
            'sales_order_id' => $salesOrder->id,
            'item_name' => 'Print batch',
            'description' => 'Printed banners',
            'quantity' => 10,
            'unit_price' => 100,
            'line_total' => 1000,
            'sort_order' => 1,
        ]);

        $salesOrder->update(['status' => SalesOrderStatus::Completed]);
        $jobCard->update(['status' => ProductionJobCardStatus::ReadyForDispatch]);

        $note = app(DeliveryNoteService::class)->createDraftFromJobCard($jobCard);
        $note->update([
            'status' => DeliveryNoteStatus::Dispatched,
            'dispatched_at' => now(),
            'dispatched_by' => $user->id,
            'recipient_name' => 'Customer Rep',
        ]);
        $note->update([
            'status' => DeliveryNoteStatus::Delivered,
            'delivered_at' => now(),
            'delivered_by' => $user->id,
        ]);

        app(DeliverySalesOrderSyncService::class)->syncFromDeliveredNote($note->fresh());

        $this->assertEquals(SalesOrderStatus::Delivered, $salesOrder->fresh()->status);
        $this->assertSame(DeliveryNoteStatus::Delivered, $note->fresh()->status);
    }

    /**
     * @return array{0: Company, 1: Branch, 2: Customer, 3: User, 4: SalesOrder}
     */
    protected function productionContext(): array
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $customer = Customer::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_code' => 'C-SYNC-1',
            'company_name' => 'Sync Customer',
            'status' => CustomerStatus::Active,
        ]);
        $user = $this->productionUser($company, $branch, ['production.view', 'production.create']);

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

        return [$company, $branch, $customer, $user, $salesOrder];
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

    protected function prepareJobForTransition(ProductionJobCard $jobCard, ProductionJobCardStatus $status): void
    {
        match ($status) {
            ProductionJobCardStatus::Draft => $jobCard->update(['status' => $status]),
            ProductionJobCardStatus::Queued => $this->ensureQueueEntry($jobCard),
            default => ProductionJobCard::withoutEvents(function () use ($jobCard, $status): void {
                $workCenter = WorkCenter::query()->where('company_id', $jobCard->company_id)->firstOrFail();

                if (! ProductionQueue::query()->where('production_job_card_id', $jobCard->id)->exists()) {
                    ProductionQueue::query()->create([
                        'company_id' => $jobCard->company_id,
                        'branch_id' => $jobCard->branch_id,
                        'production_job_card_id' => $jobCard->id,
                        'work_center_id' => $workCenter->id,
                        'queue_position' => 1,
                        'status' => \App\Enums\ProductionQueueStatus::InProgress,
                    ]);
                }

                $jobCard->update(['status' => $status]);
            }),
        };
    }

    protected function ensureQueueEntry(ProductionJobCard $jobCard): void
    {
        if (app(ProductionQueueService::class)->hasActiveQueue($jobCard)) {
            return;
        }

        $workCenter = WorkCenter::query()->where('company_id', $jobCard->company_id)->firstOrFail();
        app(ProductionQueueService::class)->enqueue($jobCard->fresh(), $workCenter->id, 1);
    }

    protected function createJobCard(SalesOrder $salesOrder, User $user): ProductionJobCard
    {
        $this->actingAs($user)->post(route('admin.production.job-cards.store'), [
            'sales_order_id' => $salesOrder->id,
            'production_type' => 'mixed',
            'priority' => 'normal',
        ]);

        return ProductionJobCard::query()->where('sales_order_id', $salesOrder->id)->firstOrFail();
    }
}
