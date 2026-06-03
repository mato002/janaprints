<?php

namespace Tests\Feature\Production;

use App\Enums\ArtworkApprovalDecision;
use App\Enums\ArtworkRequestStatus;
use App\Enums\CustomerStatus;
use App\Enums\ProductionJobCardStatus;
use App\Enums\ProductionQueueStatus;
use App\Enums\QualityCheckResult;
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
use App\Models\Production\ProductionStage;
use App\Models\Production\QualityCheck;
use App\Models\Production\WorkCenter;
use App\Models\Sales\Quotation;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use Database\Seeders\ProductionFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductionFoundationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_company_isolation_for_job_cards(): void
    {
        $companyA = Company::factory()->create(['code' => 'PA']);
        $companyB = Company::factory()->create(['code' => 'PB']);
        $branchA = Branch::factory()->create(['company_id' => $companyA->id]);
        $branchB = Branch::factory()->create(['company_id' => $companyB->id]);

        $user = $this->productionUser($companyA, $branchA, ['production.view']);
        $jobCardB = ProductionJobCard::factory()->create([
            'company_id' => $companyB->id,
            'branch_id' => $branchB->id,
        ]);

        $this->actingAs($user)
            ->get(route('admin.production.job-cards.show', $jobCardB))
            ->assertForbidden();
    }

    public function test_viewer_cannot_create_job_card(): void
    {
        [, , , $user] = $this->productionContext(['production.view']);

        $this->actingAs($user)
            ->get(route('admin.production.job-cards.create'))
            ->assertForbidden();
    }

    public function test_job_card_requires_confirmed_sales_order_and_approved_artwork(): void
    {
        [$company, $branch, $customer, $user, $salesOrder] = $this->productionContext([
            'production.view', 'production.create',
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $salesOrder->update(['status' => SalesOrderStatus::Draft]);

        $this->actingAs($user)
            ->post(route('admin.production.job-cards.store'), [
                'sales_order_id' => $salesOrder->id,
                'production_type' => 'mixed',
                'priority' => 'normal',
            ])
            ->assertSessionHasErrors('sales_order');

        $this->assertDatabaseMissing('production_job_cards', ['sales_order_id' => $salesOrder->id]);
    }

    public function test_job_card_creation_links_traceability(): void
    {
        [$company, $branch, $customer, $user, $salesOrder] = $this->productionContext([
            'production.view', 'production.create', 'production.schedule', 'production.start',
        ]);

        $this->seed(ProductionFoundationSeeder::class);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->post(route('admin.production.job-cards.store'), [
                'sales_order_id' => $salesOrder->id,
                'production_type' => 'digital',
                'priority' => 'high',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('production_job_cards', [
            'sales_order_id' => $salesOrder->id,
            'customer_id' => $customer->id,
            'quotation_id' => $salesOrder->quotation_id,
            'artwork_request_id' => $salesOrder->artwork_request_id,
            'status' => ProductionJobCardStatus::Draft->value,
        ]);
    }

    public function test_queue_workflow(): void
    {
        [$company, $branch, , $user, $salesOrder] = $this->productionContext([
            'production.view', 'production.create', 'production.schedule',
        ]);

        $this->seed(ProductionFoundationSeeder::class);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $jobCard = $this->createJobCard($salesOrder, $user);
        $workCenter = WorkCenter::query()->where('company_id', $company->id)->first();

        $this->actingAs($user)
            ->post(route('admin.production.queues.store', $jobCard), [
                'work_center_id' => $workCenter->id,
                'queue_position' => 1,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('production_queues', [
            'production_job_card_id' => $jobCard->id,
            'work_center_id' => $workCenter->id,
            'status' => ProductionQueueStatus::Pending->value,
        ]);

        $this->actingAs($user)
            ->post(route('admin.production.job-cards.queue', $jobCard))
            ->assertRedirect();

        $this->assertEquals(ProductionJobCardStatus::Queued, $jobCard->fresh()->status);
    }

    public function test_quality_control_workflow(): void
    {
        [$company, $branch, , $user, $salesOrder] = $this->productionContext([
            'production.view', 'production.create', 'production.start', 'production.complete', 'production.qc',
        ]);

        $this->seed(ProductionFoundationSeeder::class);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $jobCard = $this->createJobCard($salesOrder, $user);
        $jobCard->update(['status' => ProductionJobCardStatus::QualityCheck]);

        $this->actingAs($user)
            ->post(route('admin.production.quality-checks.store', $jobCard), [
                'result' => QualityCheckResult::Passed->value,
                'comments' => 'All good',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('quality_checks', [
            'production_job_card_id' => $jobCard->id,
            'result' => QualityCheckResult::Passed->value,
        ]);
        $this->assertEquals(ProductionJobCardStatus::Completed, $jobCard->fresh()->status);
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
            'customer_code' => 'C-PROD-1',
            'company_name' => 'Production Customer',
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
