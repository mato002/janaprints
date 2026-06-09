<?php

namespace Tests\Feature\Production;

use App\Enums\ArtworkApprovalDecision;
use App\Enums\ArtworkRequestStatus;
use App\Enums\CustomerStatus;
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
use App\Models\Production\WorkCenter;
use App\Models\Sales\Quotation;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use App\Support\Production\ProductionQueueService;
use Database\Seeders\ProductionFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SalesOrderProductionBridgeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_start_production_auto_creates_job_card(): void
    {
        [$company, $branch, , $user, $salesOrder] = $this->productionContext([
            'sales_orders.view', 'sales_orders.production',
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $salesOrder->update(['status' => SalesOrderStatus::ReadyForProduction]);

        $this->actingAs($user)
            ->post(route('admin.sales-orders.start-production', $salesOrder))
            ->assertRedirect();

        $this->assertDatabaseHas('production_job_cards', [
            'sales_order_id' => $salesOrder->id,
            'status' => ProductionJobCardStatus::Draft->value,
        ]);
        $this->assertEquals(SalesOrderStatus::InProduction, $salesOrder->fresh()->status);
    }

    public function test_start_production_reuses_existing_job_card(): void
    {
        [$company, $branch, , $user, $salesOrder] = $this->productionContext([
            'production.view', 'production.create',
            'sales_orders.view', 'sales_orders.production',
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->createJobCard($salesOrder, $user);

        $existing = ProductionJobCard::query()->where('sales_order_id', $salesOrder->id)->firstOrFail();
        $salesOrder->update(['status' => SalesOrderStatus::ReadyForProduction]);

        $this->actingAs($user)
            ->post(route('admin.sales-orders.start-production', $salesOrder))
            ->assertRedirect();

        $this->assertEquals(1, ProductionJobCard::query()->where('sales_order_id', $salesOrder->id)->count());
        $this->assertEquals($existing->id, ProductionJobCard::query()->where('sales_order_id', $salesOrder->id)->value('id'));
        $this->assertEquals(SalesOrderStatus::InProduction, $salesOrder->fresh()->status);
    }

    public function test_start_production_blocked_without_approved_artwork(): void
    {
        [$company, $branch, $customer, $user] = $this->productionContext([
            'sales_orders.view', 'sales_orders.production',
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

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
            'status' => ArtworkRequestStatus::Submitted,
            'current_version' => 1,
        ]);

        $salesOrder = SalesOrder::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'quotation_id' => $quotation->id,
            'artwork_request_id' => $artwork->id,
            'status' => SalesOrderStatus::ReadyForProduction,
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->post(route('admin.sales-orders.start-production', $salesOrder))
            ->assertSessionHasErrors('artwork');

        $this->assertDatabaseMissing('production_job_cards', ['sales_order_id' => $salesOrder->id]);
        $this->assertEquals(SalesOrderStatus::ReadyForProduction, $salesOrder->fresh()->status);
    }

    public function test_ready_for_production_creates_job_card_when_eligible(): void
    {
        [$company, $branch, , $user, $salesOrder] = $this->productionContext([
            'sales_orders.view', 'sales_orders.production',
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->post(route('admin.sales-orders.ready-for-production', $salesOrder))
            ->assertRedirect();

        $this->assertDatabaseHas('production_job_cards', ['sales_order_id' => $salesOrder->id]);
        $this->assertEquals(SalesOrderStatus::ReadyForProduction, $salesOrder->fresh()->status);
    }

    public function test_job_card_queue_syncs_sales_order_to_queued(): void
    {
        [$company, $branch, , $user, $salesOrder] = $this->productionContext([
            'production.view', 'production.create', 'production.schedule',
        ]);

        $this->seed(ProductionFoundationSeeder::class);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $jobCard = $this->createJobCard($salesOrder, $user);
        $salesOrder->update(['status' => SalesOrderStatus::ReadyForProduction]);
        $workCenter = WorkCenter::query()->where('company_id', $company->id)->firstOrFail();

        app(ProductionQueueService::class)->enqueue($jobCard, $workCenter->id, 1);

        $this->assertEquals(SalesOrderStatus::Queued, $salesOrder->fresh()->status);
    }

    public function test_job_card_start_syncs_sales_order_to_in_production(): void
    {
        [$company, $branch, , $user, $salesOrder] = $this->productionContext([
            'production.view', 'production.create', 'production.schedule', 'production.start',
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->seed(ProductionFoundationSeeder::class);

        $jobCard = $this->createJobCard($salesOrder, $user);
        $workCenter = WorkCenter::query()->where('company_id', $company->id)->firstOrFail();
        app(ProductionQueueService::class)->enqueue($jobCard, $workCenter->id, 1);
        $salesOrder->update(['status' => SalesOrderStatus::Queued]);

        $jobCard->fresh()->transitionTo(ProductionJobCardStatus::InProduction);

        $this->assertEquals(SalesOrderStatus::InProduction, $salesOrder->fresh()->status);
    }

    public function test_job_card_completed_syncs_sales_order_to_production_complete(): void
    {
        [$company, $branch, , $user, $salesOrder] = $this->productionContext([
            'production.view', 'production.create',
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $jobCard = $this->createJobCard($salesOrder, $user);
        $salesOrder->update(['status' => SalesOrderStatus::QualityCheck]);
        $jobCard->update(['status' => ProductionJobCardStatus::QualityCheck]);

        $jobCard->transitionTo(ProductionJobCardStatus::Completed);

        $this->assertEquals(SalesOrderStatus::ProductionComplete, $salesOrder->fresh()->status);
    }

    public function test_job_card_ready_for_dispatch_syncs_sales_order_to_ready_for_dispatch(): void
    {
        [$company, $branch, , $user, $salesOrder] = $this->productionContext([
            'production.view', 'production.create',
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $jobCard = $this->createJobCard($salesOrder, $user);
        $salesOrder->update(['status' => SalesOrderStatus::ProductionComplete]);
        $jobCard->update(['status' => ProductionJobCardStatus::Completed]);

        $jobCard->transitionTo(ProductionJobCardStatus::ReadyForDispatch);

        $this->assertEquals(SalesOrderStatus::ReadyForDispatch, $salesOrder->fresh()->status);
    }

    /**
     * @return array{0: Company, 1: Branch, 2: Customer, 3: User, 4: SalesOrder}
     */
    protected function productionContext(?array $permissions = null): array
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $customer = Customer::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_code' => 'C-BRIDGE-1',
            'company_name' => 'Bridge Customer',
            'status' => CustomerStatus::Active,
        ]);
        $permissions ??= ['production.view', 'production.create'];
        $user = $this->productionUser($company, $branch, $permissions);

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
