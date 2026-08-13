<?php

namespace Tests\Feature\Production;

use App\Enums\ArtworkApprovalDecision;
use App\Enums\ArtworkRequestStatus;
use App\Enums\CustomerStatus;
use App\Enums\ProductionJobCardStatus;
use App\Enums\QualityCheckResult;
use App\Enums\QualityReworkReason;
use App\Enums\QuotationStatus;
use App\Events\Communications\DomainCommunicationEventRaised;
use App\Enums\DomainCommunicationEvent;
use App\Models\Artwork\ArtworkApproval;
use App\Models\Artwork\ArtworkRequest;
use App\Models\Artwork\ArtworkVersion;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Inventory\InventoryItem;
use App\Models\Production\JobCardQcSnapshot;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\QualityCheck;
use App\Models\Sales\Quotation;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use App\Support\Production\ProductQcChecklistService;
use App\Support\Production\SerialNumberGovernanceService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductionQualityC6Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_pass_inspection_moves_to_awaiting_finished_goods(): void
    {
        [$company, $branch, , $user, $jobCard] = $this->qcJobContext();
        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);
        $jobCard->update(['status' => ProductionJobCardStatus::QualityCheck]);
        app(ProductQcChecklistService::class)->snapshotForJobCard($jobCard);

        $this->actingAs($user)
            ->post(route('admin.production.quality-checks.store', $jobCard), [
                'result' => QualityCheckResult::Passed->value,
                'comments' => 'All good',
            ])
            ->assertRedirect();

        $this->assertEquals(ProductionJobCardStatus::Completed, $jobCard->fresh()->status);
    }

    public function test_fail_inspection_moves_to_rework_with_quantities(): void
    {
        [, , , $user, $jobCard] = $this->qcJobContext();
        $jobCard->update(['status' => ProductionJobCardStatus::QualityCheck]);

        $this->actingAs($user)
            ->post(route('admin.production.quality-checks.store', $jobCard), [
                'result' => QualityCheckResult::Failed->value,
                'rework_reason' => QualityReworkReason::BadPrint->value,
                'estimated_rework_qty' => 50,
                'actual_rework_qty' => 45,
            ])
            ->assertRedirect();

        $this->assertEquals(ProductionJobCardStatus::Rework, $jobCard->fresh()->status);
        $this->assertDatabaseHas('quality_checks', [
            'production_job_card_id' => $jobCard->id,
            'rework_reason' => QualityReworkReason::BadPrint->value,
            'estimated_rework_qty' => 50,
        ]);
    }

    public function test_conditional_pass_awaits_customer_approval(): void
    {
        [$company, $branch, , $user, $jobCard] = $this->qcJobContext();
        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);
        $jobCard->update(['status' => ProductionJobCardStatus::QualityCheck]);

        $this->actingAs($user)
            ->post(route('admin.production.quality-checks.store', $jobCard), [
                'result' => QualityCheckResult::ConditionalPass->value,
                'requires_customer_approval' => true,
                'fail_reason' => \App\Enums\QualityFailReason::ArtworkError->value,
            ])
            ->assertRedirect();

        $this->assertEquals(ProductionJobCardStatus::AwaitingCustomerApproval, $jobCard->fresh()->status);

        $check = QualityCheck::query()->where('production_job_card_id', $jobCard->id)->firstOrFail();

        $this->actingAs($user)
            ->post(route('admin.production.quality-checks.approve-customer', [$jobCard, $check]))
            ->assertRedirect();

        $this->assertEquals(ProductionJobCardStatus::Completed, $jobCard->fresh()->status);
        $this->assertNotNull($check->fresh()->customer_approved_at);
    }

    public function test_checklist_snapshot_on_send_to_qc(): void
    {
        [$company, $branch, , $user, $jobCard, $finished] = $this->qcJobContext();

        app(ProductQcChecklistService::class)->syncFromCatalogItem($finished, [
            ['label' => 'Correct Quantity', 'is_active' => true],
            ['label' => 'Correct Artwork', 'is_active' => true],
        ], $user->id);

        $jobCard->update(['status' => ProductionJobCardStatus::InProduction, 'inventory_item_id' => $finished->id]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->post(route('admin.production.job-cards.send-to-qc', $jobCard))
            ->assertRedirect();

        $snapshot = JobCardQcSnapshot::query()->where('production_job_card_id', $jobCard->id)->first();
        $this->assertNotNull($snapshot);
        $this->assertCount(2, $snapshot->checklist_items);
    }

    public function test_serial_validation_prevents_overlapping_spoilage(): void
    {
        [, , , $user, $jobCard, $finished] = $this->qcJobContext();
        $finished->update(['uses_serial_numbers' => true, 'serial_prefix' => 'RB', 'serial_padding_length' => 4]);

        $service = app(SerialNumberGovernanceService::class);
        $allocation = $service->allocateForJobCard($jobCard->fresh(), $finished->fresh(), 100);
        $this->assertNotNull($allocation);

        $service->confirmProduction($allocation, [
            'produced_end' => 50,
            'spoiled_start' => 60,
            'spoiled_end' => 70,
        ], $user->id);

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $service->confirmProduction($allocation->fresh(), [
            'produced_end' => 50,
            'spoiled_start' => 65,
            'spoiled_end' => 75,
        ], $user->id);
    }

    public function test_quality_approved_notification_on_pass(): void
    {
        Event::fake([DomainCommunicationEventRaised::class]);

        [, , , $user, $jobCard] = $this->qcJobContext();
        $jobCard->update(['status' => ProductionJobCardStatus::QualityCheck]);

        $this->actingAs($user)
            ->post(route('admin.production.quality-checks.store', $jobCard), [
                'result' => QualityCheckResult::Passed->value,
            ]);

        $jobCard->refresh();
        app(\App\Listeners\Production\DispatchProductionCommunication::class)->handle(
            new \App\Events\Production\JobCardStatusChanged(
                $jobCard,
                ProductionJobCardStatus::Completed,
                ProductionJobCardStatus::QualityCheck,
            ),
        );

        Event::assertDispatched(DomainCommunicationEventRaised::class, function (DomainCommunicationEventRaised $event) {
            return $event->event === DomainCommunicationEvent::QualityApproved;
        });
    }

    public function test_late_qc_can_be_recorded_when_job_is_ready_for_dispatch(): void
    {
        [$company, $branch, , $user, $jobCard] = $this->qcJobContext();
        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);
        app()->instance(\App\Support\TenantContext::class, new \App\Support\TenantContext($company, $branch));

        $jobCard->update(['status' => ProductionJobCardStatus::ReadyForDispatch]);

        $this->assertTrue($user->can('create', [QualityCheck::class, $jobCard]));

        $this->actingAs($user)
            ->get(route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'quality']))
            ->assertOk()
            ->assertSee(__('Record inspection'), false)
            ->assertSee(__('QC approval required'), false);

        $this->actingAs($user)
            ->post(route('admin.production.quality-checks.store', $jobCard), [
                'result' => QualityCheckResult::Passed->value,
                'comments' => 'Late catch-up pass',
            ])
            ->assertRedirect();

        $this->assertEquals(ProductionJobCardStatus::ReadyForDispatch, $jobCard->fresh()->status);
        $this->assertDatabaseHas('quality_checks', [
            'production_job_card_id' => $jobCard->id,
            'result' => QualityCheckResult::Passed->value,
        ]);
        $this->assertFalse($user->can('create', [QualityCheck::class, $jobCard->fresh()]));
    }

    public function test_tenant_isolation_on_quality_checks(): void
    {
        $companyA = Company::factory()->create();
        $branchA = Branch::factory()->create(['company_id' => $companyA->id]);
        $companyB = Company::factory()->create();
        $branchB = Branch::factory()->create(['company_id' => $companyB->id]);

        QualityCheck::query()->create([
            'company_id' => $companyA->id,
            'branch_id' => $branchA->id,
            'production_job_card_id' => ProductionJobCard::factory()->create([
                'company_id' => $companyA->id,
                'branch_id' => $branchA->id,
            ])->id,
            'checked_by' => User::factory()->create(['company_id' => $companyA->id])->id,
            'result' => QualityCheckResult::Passed,
            'checked_at' => now(),
        ]);

        app()->instance(\App\Support\TenantContext::class, new \App\Support\TenantContext($companyB, $branchB));

        $this->assertEquals(0, QualityCheck::query()->forTenant()->count());
    }

    /**
     * @return array{0: Company, 1: Branch, 2: Customer, 3: User, 4: ProductionJobCard, 5: InventoryItem}
     */
    protected function qcJobContext(): array
    {
        [$company, $branch, $customer, $user, $salesOrder] = $this->productionContext();

        $finished = InventoryItem::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'is_active' => true,
        ]);

        $jobCard = ProductionJobCard::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'sales_order_id' => $salesOrder->id,
            'inventory_item_id' => $finished->id,
            'created_by' => $user->id,
        ]);

        return [$company, $branch, $customer, $user, $jobCard, $finished];
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
            'status' => CustomerStatus::Active,
        ]);
        $user = $this->productionUser($company, $branch, [
            'production.view', 'production.create', 'production.qc', 'production.complete',
        ]);

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
}
