<?php

namespace Tests\Feature\Production;

use App\Enums\ProductionQueueStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionQueue;
use App\Models\Production\WorkCenter;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use App\Support\TenantContext;
use Database\Seeders\ProductionFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductionQueueWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_queue_workspace_loads(): void
    {
        [$company, $branch, $user, $workCenter, $jobCard] = $this->queueContext();

        ProductionQueue::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'production_job_card_id' => $jobCard->id,
            'work_center_id' => $workCenter->id,
            'queue_position' => 1,
            'status' => ProductionQueueStatus::Waiting,
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);
        app()->instance(TenantContext::class, new TenantContext($company, $branch, false));

        $this->actingAs($user)
            ->get(route('admin.production.queue.index', ['embedded' => '1']))
            ->assertOk()
            ->assertSee(__('Production Queue'), false)
            ->assertSee(__('Department queue register'), false)
            ->assertSee(__('Queued'), false)
            ->assertSee(__('Blocked'), false)
            ->assertDontSee(__('Bottleneck Detection'), false)
            ->assertDontSee(__('Work centers with queued jobs'), false)
            ->assertSee($jobCard->job_card_number, false);
    }

    public function test_queue_workspace_filters_by_operator(): void
    {
        [$company, $branch, $user, $workCenter, $jobCard] = $this->queueContext();
        $operator = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'name' => 'Queue Operator',
        ]);

        ProductionQueue::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'production_job_card_id' => $jobCard->id,
            'work_center_id' => $workCenter->id,
            'queue_position' => 1,
            'status' => ProductionQueueStatus::Assigned,
            'assigned_operator_id' => $operator->id,
        ]);

        $otherJob = ProductionJobCard::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'sales_order_id' => SalesOrder::factory()->create([
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'created_by' => $user->id,
            ])->id,
            'created_by' => $user->id,
        ]);

        ProductionQueue::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'production_job_card_id' => $otherJob->id,
            'work_center_id' => $workCenter->id,
            'queue_position' => 2,
            'status' => ProductionQueueStatus::Waiting,
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);
        app()->instance(TenantContext::class, new TenantContext($company, $branch, false));

        $this->actingAs($user)
            ->get(route('admin.production.queue.index', ['operator_id' => $operator->id, 'embedded' => '1']))
            ->assertOk()
            ->assertSee($jobCard->job_card_number, false)
            ->assertDontSee($otherJob->job_card_number, false);

        $this->actingAs($user)
            ->get(route('admin.production.queue.index', ['operator_id' => 'unassigned', 'embedded' => '1']))
            ->assertOk()
            ->assertSee($otherJob->job_card_number, false)
            ->assertDontSee($jobCard->job_card_number, false);
    }

    public function test_queue_workspace_filters_by_status_without_ambiguous_sql(): void
    {
        [$company, $branch, $user, $workCenter, $jobCard] = $this->queueContext();

        ProductionQueue::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'production_job_card_id' => $jobCard->id,
            'work_center_id' => $workCenter->id,
            'queue_position' => 1,
            'status' => ProductionQueueStatus::Waiting,
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);
        app()->instance(TenantContext::class, new TenantContext($company, $branch, false));

        $this->actingAs($user)
            ->get(route('admin.production.queue.index', ['status' => 'waiting', 'embedded' => '1']))
            ->assertOk()
            ->assertSee($jobCard->job_card_number, false);
    }

    public function test_queue_workspace_filters_by_work_center(): void
    {
        [$company, $branch, $user, $workCenter, $jobCard] = $this->queueContext();

        ProductionQueue::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'production_job_card_id' => $jobCard->id,
            'work_center_id' => $workCenter->id,
            'queue_position' => 1,
            'status' => ProductionQueueStatus::Assigned,
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);
        app()->instance(TenantContext::class, new TenantContext($company, $branch, false));

        $this->actingAs($user)
            ->get(route('admin.production.queue.index', ['work_center_id' => $workCenter->id, 'embedded' => '1']))
            ->assertOk()
            ->assertSee($jobCard->job_card_number, false);
    }

    /**
     * @return array{0: Company, 1: Branch, 2: User, 3: WorkCenter, 4: ProductionJobCard}
     */
    protected function queueContext(): array
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $role = Role::findByName('Production', 'web');
        $role->syncPermissions(['production.queue.view', 'production.view', 'production.work-centers.view']);
        $user->assignRole('Production');

        $this->seed(ProductionFoundationSeeder::class);

        $workCenter = WorkCenter::query()->where('company_id', $company->id)->firstOrFail();
        $salesOrder = SalesOrder::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'created_by' => $user->id,
        ]);
        $jobCard = ProductionJobCard::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'sales_order_id' => $salesOrder->id,
            'created_by' => $user->id,
        ]);

        return [$company, $branch, $user, $workCenter, $jobCard];
    }
}
