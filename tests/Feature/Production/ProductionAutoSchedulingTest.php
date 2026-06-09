<?php

namespace Tests\Feature\Production;

use App\Enums\ArtworkApprovalDecision;
use App\Enums\ArtworkRequestStatus;
use App\Enums\CustomerStatus;
use App\Enums\FixedAssetStatus;
use App\Enums\ProductionJobCardStatus;
use App\Enums\ProductionMachineStatus;
use App\Enums\ProductionQueueStatus;
use App\Enums\QuotationStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Artwork\ArtworkApproval;
use App\Models\Artwork\ArtworkRequest;
use App\Models\Artwork\ArtworkVersion;
use App\Models\Assets\AssetCategory;
use App\Models\Assets\FixedAsset;
use App\Models\Assets\MachineProfile;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\WorkCenter;
use App\Models\Sales\Quotation;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use App\Services\Production\ProductionAutoSchedulingService;
use App\Support\Platform\SystemSettingsService;
use App\Support\Production\ProductionQueueService;
use App\Support\Production\SalesOrderProductionBridgeService;
use App\Support\ProductionJobCardService;
use Database\Seeders\ProductionFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductionAutoSchedulingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_auto_schedule_creates_queue_planned_dates_and_position_when_enabled(): void
    {
        [$company, $branch, , $user, $salesOrder] = $this->productionContext();
        $this->seed(ProductionFoundationSeeder::class);
        $this->enableAutoSchedule($company->id, $branch->id);

        $jobCard = ProductionJobCardService::createFromSalesOrder($salesOrder, $user->id, [
            'production_type' => 'digital',
            'priority' => 'high',
        ]);

        $digitalCenter = WorkCenter::query()
            ->where('company_id', $company->id)
            ->where('code', 'DIGITAL')
            ->firstOrFail();

        $this->assertNotNull($jobCard->planned_start_date);
        $this->assertNotNull($jobCard->planned_end_date);
        $this->assertEquals(ProductionJobCardStatus::Queued, $jobCard->status);

        $this->assertDatabaseHas('production_queues', [
            'production_job_card_id' => $jobCard->id,
            'work_center_id' => $digitalCenter->id,
            'queue_position' => 1,
            'status' => ProductionQueueStatus::Pending->value,
        ]);
    }

    public function test_capacity_check_reports_work_center_utilization(): void
    {
        [$company, $branch, , $user, $salesOrder] = $this->productionContext();
        $this->seed(ProductionFoundationSeeder::class);

        config(['production.scheduling.default_work_center_capacity' => 2]);

        $workCenter = WorkCenter::query()
            ->where('company_id', $company->id)
            ->where('code', 'DIGITAL')
            ->firstOrFail();

        $this->fillWorkCenterCapacity($company->id, $branch->id, $workCenter, 1);

        $service = app(ProductionAutoSchedulingService::class);
        $snapshot = $service->capacitySnapshot($workCenter);

        $this->assertSame(2, $snapshot['capacity']);
        $this->assertSame(1, $snapshot['active_jobs']);
        $this->assertTrue($snapshot['has_capacity']);
        $this->assertFalse($snapshot['is_overbooked']);
    }

    public function test_overload_prevention_blocks_scheduling_at_capacity(): void
    {
        [$company, $branch, , $user, $salesOrder] = $this->productionContext();
        $this->seed(ProductionFoundationSeeder::class);

        config(['production.scheduling.default_work_center_capacity' => 1]);

        $workCenter = WorkCenter::query()
            ->where('company_id', $company->id)
            ->where('code', 'DIGITAL')
            ->firstOrFail();

        $this->fillWorkCenterCapacity($company->id, $branch->id, $workCenter, 1);

        $jobCard = ProductionJobCard::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'sales_order_id' => $salesOrder->id,
            'production_type' => 'digital',
            'status' => ProductionJobCardStatus::Draft,
        ]);

        $this->expectException(ValidationException::class);

        app(ProductionAutoSchedulingService::class)->schedule($jobCard, $user->id);
    }

    public function test_setting_toggle_disables_auto_schedule_on_create(): void
    {
        [$company, $branch, , $user, $salesOrder] = $this->productionContext();
        $this->seed(ProductionFoundationSeeder::class);

        app(SystemSettingsService::class)->set(
            'production_auto_schedule_on_create',
            false,
            $company->id,
            $branch->id,
            'boolean',
        );

        $jobCard = ProductionJobCardService::createFromSalesOrder($salesOrder, $user->id, [
            'production_type' => 'digital',
        ]);

        $this->assertEquals(ProductionJobCardStatus::Draft, $jobCard->status);
        $this->assertNull($jobCard->planned_start_date);
        $this->assertDatabaseMissing('production_queues', [
            'production_job_card_id' => $jobCard->id,
        ]);
    }

    public function test_auto_schedule_skips_gracefully_when_overloaded_on_create(): void
    {
        [$company, $branch, , $user, $salesOrder] = $this->productionContext();
        $this->seed(ProductionFoundationSeeder::class);
        $this->enableAutoSchedule($company->id, $branch->id);

        config(['production.scheduling.default_work_center_capacity' => 1]);

        $workCenter = WorkCenter::query()
            ->where('company_id', $company->id)
            ->where('code', 'DIGITAL')
            ->firstOrFail();

        $this->fillWorkCenterCapacity($company->id, $branch->id, $workCenter, 1);

        $jobCard = ProductionJobCardService::createFromSalesOrder($salesOrder, $user->id, [
            'production_type' => 'digital',
        ]);

        $this->assertEquals(ProductionJobCardStatus::Draft, $jobCard->status);
        $this->assertDatabaseMissing('production_queues', [
            'production_job_card_id' => $jobCard->id,
        ]);
    }

    public function test_next_job_queues_after_existing_planned_end_date(): void
    {
        [$company, $branch, , $user, $salesOrder] = $this->productionContext();
        $this->seed(ProductionFoundationSeeder::class);
        $this->enableAutoSchedule($company->id, $branch->id);

        $workCenter = WorkCenter::query()
            ->where('company_id', $company->id)
            ->where('code', 'DIGITAL')
            ->firstOrFail();

        $existing = ProductionJobCard::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'planned_start_date' => now()->toDateString(),
            'planned_end_date' => now()->addDays(4)->toDateString(),
            'status' => ProductionJobCardStatus::Draft,
        ]);

        app(ProductionQueueService::class)->enqueue($existing->fresh(), $workCenter->id, 1);

        $jobCard = ProductionJobCardService::createFromSalesOrder($salesOrder, $user->id, [
            'production_type' => 'digital',
        ]);

        $this->assertEquals(
            now()->addDays(5)->toDateString(),
            $jobCard->planned_start_date?->toDateString(),
        );

        $this->assertDatabaseHas('production_queues', [
            'production_job_card_id' => $jobCard->id,
            'queue_position' => 2,
        ]);
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

    public function test_machine_unavailability_blocks_manual_schedule(): void
    {
        [$company, $branch, , $user, $salesOrder] = $this->productionContext();
        $this->seed(ProductionFoundationSeeder::class);

        $workCenter = WorkCenter::query()
            ->where('company_id', $company->id)
            ->where('code', 'DIGITAL')
            ->firstOrFail();

        $asset = $this->makeMachineAsset($company->id, $branch->id);
        MachineProfile::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'fixed_asset_id' => $asset->id,
            'machine_code' => 'OFFLINE-01',
            'machine_type' => 'Digital Press',
            'production_status' => ProductionMachineStatus::Offline,
            'shift_capacity' => 10,
            'hourly_capacity' => 2,
        ]);
        $workCenter->update(['fixed_asset_id' => $asset->id]);

        $jobCard = ProductionJobCard::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'sales_order_id' => $salesOrder->id,
            'production_type' => 'digital',
            'status' => ProductionJobCardStatus::Draft,
        ]);

        $this->expectException(ValidationException::class);

        app(ProductionAutoSchedulingService::class)->schedule($jobCard, $user->id);
    }

    public function test_auto_schedule_skips_when_machine_unavailable_on_create(): void
    {
        [$company, $branch, , $user, $salesOrder] = $this->productionContext();
        $this->seed(ProductionFoundationSeeder::class);
        $this->enableAutoSchedule($company->id, $branch->id);

        $workCenter = WorkCenter::query()
            ->where('company_id', $company->id)
            ->where('code', 'DIGITAL')
            ->firstOrFail();

        $asset = $this->makeMachineAsset($company->id, $branch->id);
        MachineProfile::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'fixed_asset_id' => $asset->id,
            'machine_code' => 'OFFLINE-02',
            'machine_type' => 'Digital Press',
            'production_status' => ProductionMachineStatus::Offline,
            'shift_capacity' => 10,
            'hourly_capacity' => 2,
        ]);
        $workCenter->update(['fixed_asset_id' => $asset->id]);

        $jobCard = ProductionJobCardService::createFromSalesOrder($salesOrder, $user->id, [
            'production_type' => 'digital',
        ]);

        $this->assertEquals(ProductionJobCardStatus::Draft, $jobCard->status);
        $this->assertDatabaseMissing('production_queues', [
            'production_job_card_id' => $jobCard->id,
        ]);
    }

    public function test_sales_order_bridge_triggers_auto_schedule_when_enabled(): void
    {
        [$company, $branch, , $user, $salesOrder] = $this->productionContext();
        $this->seed(ProductionFoundationSeeder::class);
        $this->enableAutoSchedule($company->id, $branch->id);

        $jobCard = app(SalesOrderProductionBridgeService::class)->ensureJobCard($salesOrder, $user->id, [
            'production_type' => 'digital',
        ]);

        $digitalCenter = WorkCenter::query()
            ->where('company_id', $company->id)
            ->where('code', 'DIGITAL')
            ->firstOrFail();

        $this->assertEquals(ProductionJobCardStatus::Queued, $jobCard->status);
        $this->assertNotNull($jobCard->planned_start_date);
        $this->assertDatabaseHas('production_queues', [
            'production_job_card_id' => $jobCard->id,
            'work_center_id' => $digitalCenter->id,
        ]);
    }

    protected function fillWorkCenterCapacity(
        int $companyId,
        int $branchId,
        WorkCenter $workCenter,
        int $count,
    ): void {
        $queueService = app(ProductionQueueService::class);

        for ($i = 0; $i < $count; $i++) {
            $job = ProductionJobCard::factory()->create([
                'company_id' => $companyId,
                'branch_id' => $branchId,
                'status' => ProductionJobCardStatus::Draft,
            ]);

            $queueService->enqueue($job->fresh(), $workCenter->id, $i + 1);
        }
    }

    protected function makeMachineAsset(int $companyId, int $branchId): FixedAsset
    {
        $category = AssetCategory::query()->firstOrCreate(
            ['company_id' => $companyId, 'code' => 'MACHINES'],
            ['name' => 'Production Machines', 'is_active' => true],
        );

        return FixedAsset::query()->create([
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'asset_category_id' => $category->id,
            'asset_number' => 'AST-SCH-'.uniqid(),
            'asset_name' => 'Digital Press',
            'acquisition_date' => now(),
            'acquisition_cost' => 1000000,
            'status' => FixedAssetStatus::Active,
        ]);
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
            'customer_code' => 'C-AUTO-1',
            'company_name' => 'Auto Schedule Customer',
            'status' => CustomerStatus::Active,
        ]);
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $role = Role::findByName('Production', 'web');
        $role->syncPermissions(['production.view', 'production.create', 'production.schedule']);
        $user->assignRole('Production');

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
            'required_date' => now()->addDays(10),
            'created_by' => $user->id,
        ]);

        return [$company, $branch, $customer, $user, $salesOrder];
    }
}
