<?php

namespace Tests\Feature\Production;

use App\Enums\ProductionJobCardStatus;
use App\Enums\ProductionQueueStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionQueue;
use App\Models\Production\WorkCenter;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use App\Support\Production\ProductionQueueService;
use Database\Seeders\ProductionFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductionQueueUnificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_queue_action_creates_queue_entry_and_syncs_job_status(): void
    {
        [$company, $branch, $user, $jobCard, $workCenter] = $this->queueContext();

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->post(route('admin.production.job-cards.queue', $jobCard), [
                'work_center_id' => $workCenter->id,
                'queue_position' => 1,
            ])
            ->assertRedirect();

        $this->assertEquals(ProductionJobCardStatus::Queued, $jobCard->fresh()->status);
        $this->assertDatabaseHas('production_queues', [
            'production_job_card_id' => $jobCard->id,
            'work_center_id' => $workCenter->id,
            'queue_position' => 1,
            'status' => ProductionQueueStatus::Pending->value,
        ]);
    }

    public function test_queues_store_uses_same_enqueue_path(): void
    {
        [$company, $branch, $user, $jobCard, $workCenter] = $this->queueContext();

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->post(route('admin.production.queues.store', $jobCard), [
                'work_center_id' => $workCenter->id,
            ])
            ->assertRedirect();

        $this->assertEquals(ProductionJobCardStatus::Queued, $jobCard->fresh()->status);
        $this->assertEquals(1, ProductionQueue::query()->where('production_job_card_id', $jobCard->id)->count());
    }

    public function test_queued_job_without_queue_record_is_rejected(): void
    {
        [, , , $jobCard] = $this->queueContext();

        $this->expectException(ValidationException::class);

        $jobCard->transitionTo(ProductionJobCardStatus::Queued);
    }

    public function test_operator_assignment_updates_queue_status(): void
    {
        [$company, $branch, $user, $jobCard, $workCenter] = $this->queueContext();
        $operator = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $entry = app(ProductionQueueService::class)->enqueue($jobCard, $workCenter->id, 1);

        $this->actingAs($user)
            ->put(route('admin.production.queues.update', [$jobCard, $entry]), [
                'queue_position' => 2,
                'assigned_operator_id' => $operator->id,
                'status' => ProductionQueueStatus::Assigned->value,
            ])
            ->assertRedirect();

        $entry->refresh();
        $this->assertEquals(2, $entry->queue_position);
        $this->assertEquals($operator->id, $entry->assigned_operator_id);
        $this->assertEquals(ProductionQueueStatus::Assigned, $entry->status);
    }

    public function test_starting_production_marks_queue_in_progress(): void
    {
        [$company, $branch, $user, $jobCard, $workCenter] = $this->queueContext();

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $entry = app(ProductionQueueService::class)->enqueue($jobCard, $workCenter->id, 1);

        $this->actingAs($user)
            ->post(route('admin.production.job-cards.start', $jobCard))
            ->assertRedirect();

        $this->assertEquals(ProductionQueueStatus::InProgress, $entry->fresh()->status);
        $this->assertEquals(ProductionJobCardStatus::InProduction, $jobCard->fresh()->status);
    }

    public function test_completing_job_marks_queue_completed(): void
    {
        [, , $user, $jobCard, $workCenter] = $this->queueContext();
        $queues = app(ProductionQueueService::class);
        $entry = $queues->enqueue($jobCard, $workCenter->id, 1);

        $jobCard->update(['status' => ProductionJobCardStatus::QualityCheck]);
        $jobCard->transitionTo(ProductionJobCardStatus::Completed);

        $this->assertEquals(ProductionQueueStatus::Completed, $entry->fresh()->status);
    }

    public function test_removing_last_queue_entry_reverts_job_to_draft(): void
    {
        [$company, $branch, $user, $jobCard, $workCenter] = $this->queueContext();

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $entry = app(ProductionQueueService::class)->enqueue($jobCard, $workCenter->id, 1);

        $this->actingAs($user)
            ->delete(route('admin.production.queues.destroy', [$jobCard, $entry]))
            ->assertRedirect();

        $this->assertEquals(ProductionJobCardStatus::Draft, $jobCard->fresh()->status);
        $this->assertDatabaseMissing('production_queues', ['id' => $entry->id]);
    }

    public function test_queue_sync_updates_sales_order_to_queued(): void
    {
        [, , , $jobCard, $workCenter] = $this->queueContext();

        $salesOrder = $jobCard->salesOrder;
        $salesOrder->update(['status' => SalesOrderStatus::ReadyForProduction]);

        app(ProductionQueueService::class)->enqueue($jobCard, $workCenter->id, 1);

        $this->assertEquals(SalesOrderStatus::Queued, $salesOrder->fresh()->status);
    }

    /**
     * @return array{0: Company, 1: Branch, 2: User, 3: ProductionJobCard, 4: WorkCenter}
     */
    protected function queueContext(): array
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);
        $role = Role::create(['name' => 'Queue Unify '.uniqid(), 'guard_name' => 'web']);
        $role->syncPermissions([
            'production.view', 'production.create', 'production.schedule',
            'production.start', 'production.complete',
        ]);
        $user->assignRole($role);

        $this->seed(ProductionFoundationSeeder::class);

        $salesOrder = SalesOrder::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'created_by' => $user->id,
        ]);

        $jobCard = ProductionJobCard::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'sales_order_id' => $salesOrder->id,
            'customer_id' => $salesOrder->customer_id,
            'status' => ProductionJobCardStatus::Draft,
            'created_by' => $user->id,
        ]);

        $workCenter = WorkCenter::query()->where('company_id', $company->id)->firstOrFail();

        return [$company, $branch, $user, $jobCard, $workCenter];
    }
}
