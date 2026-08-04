<?php

namespace Tests\Feature\Production;

use App\Enums\ProductionJobCardStatus;
use App\Enums\ProductionQueueStatus;
use App\Enums\ProductionType;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionQueue;
use App\Models\Production\WorkCenter;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use App\Support\Production\DepartmentQueueRoutingService;
use App\Support\Production\ProductionSpecificationService;
use App\Support\TenantContext;
use Database\Seeders\ProductionFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DepartmentProductionQueueTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_digital_queue_only_shows_digital_department_jobs(): void
    {
        [$company, $branch, $user, $digitalCenter, $offsetCenter] = $this->departmentContext();

        $digitalJob = $this->queueJob($company, $branch, $user, $digitalCenter, ProductionType::Digital);
        $offsetJob = $this->queueJob($company, $branch, $user, $offsetCenter, ProductionType::Offset);

        $this->actingAs($user)
            ->get(route('admin.production.queue.department', 'digital').'?embedded=1')
            ->assertOk()
            ->assertSee($digitalJob->job_card_number, false)
            ->assertDontSee($offsetJob->job_card_number, false)
            ->assertSee(__('Digital Command Centre'), false);
    }

    public function test_offset_queue_only_shows_offset_department_jobs(): void
    {
        [$company, $branch, $user, $digitalCenter, $offsetCenter] = $this->departmentContext();

        $digitalJob = $this->queueJob($company, $branch, $user, $digitalCenter, ProductionType::Digital);
        $offsetJob = $this->queueJob($company, $branch, $user, $offsetCenter, ProductionType::Offset);

        $this->actingAs($user)
            ->get(route('admin.production.queue.department', 'offset').'?embedded=1')
            ->assertOk()
            ->assertSee($offsetJob->job_card_number, false)
            ->assertDontSee($digitalJob->job_card_number, false);
    }

    public function test_outsource_queue_only_shows_outsourced_jobs(): void
    {
        [$company, $branch, $user, $digitalCenter] = $this->departmentContext();

        $outsourced = $this->queueJob($company, $branch, $user, $digitalCenter, ProductionType::Digital);
        $outsourced->update(['status' => ProductionJobCardStatus::Outsourced]);

        $normal = $this->queueJob($company, $branch, $user, $digitalCenter, ProductionType::Digital);

        $this->actingAs($user)
            ->get(route('admin.production.queue.department', 'outsource').'?embedded=1')
            ->assertOk()
            ->assertSee($outsourced->job_card_number, false)
            ->assertDontSee($normal->job_card_number, false);
    }

    public function test_routing_uses_production_specification_template_work_center(): void
    {
        [$company, $branch, $user, $digitalCenter, $offsetCenter] = $this->departmentContext();

        $salesOrder = SalesOrder::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'created_by' => $user->id,
        ]);

        $item = \App\Models\Sales\SalesOrderItem::query()->create([
            'sales_order_id' => $salesOrder->id,
            'item_name' => 'Spec routed job',
            'quantity' => 100,
            'unit_price' => 1,
            'line_total' => 100,
            'sort_order' => 1,
        ]);

        $template = \App\Models\Production\PrintProductTemplate::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'name' => 'Offset preset',
            'code' => 'OFFPRESET',
            'category' => \App\Enums\PrintProductTemplateCategory::Stationery,
            'preferred_work_center_id' => $offsetCenter->id,
            'is_active' => true,
            'created_by' => $user->id,
        ]);

        $spec = app(ProductionSpecificationService::class)->createForSalesOrderItem($item, [
            'production_type' => ProductionType::Digital->value,
            'print_product_template_id' => $template->id,
        ], $user);

        $jobCard = ProductionJobCard::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'sales_order_id' => $salesOrder->id,
            'production_type' => ProductionType::Digital,
            'created_by' => $user->id,
        ]);

        $spec->update(['production_job_card_id' => $jobCard->id]);

        $routing = app(DepartmentQueueRoutingService::class)->resolveForJobCard($jobCard->fresh([
            'productionSpecification.printProductTemplate.preferredWorkCenter',
        ]));

        $this->assertSame($offsetCenter->id, $routing['work_center']?->id);
        $this->assertSame('print_product_template', $routing['source']);
    }

    public function test_legacy_job_cards_without_specification_still_appear_in_department_queue(): void
    {
        [$company, $branch, $user, $digitalCenter] = $this->departmentContext();

        $legacyJob = $this->queueJob($company, $branch, $user, $digitalCenter, ProductionType::Digital);

        $this->actingAs($user)
            ->get(route('admin.production.queue.department', 'digital').'?embedded=1')
            ->assertOk()
            ->assertSee($legacyJob->job_card_number, false);
    }

    public function test_due_overdue_filter_works(): void
    {
        [$company, $branch, $user, $digitalCenter] = $this->departmentContext();

        $overdueJob = $this->queueJob($company, $branch, $user, $digitalCenter, ProductionType::Digital);
        $overdueJob->update(['required_date' => now()->subDay()]);

        $futureJob = $this->queueJob($company, $branch, $user, $digitalCenter, ProductionType::Digital);
        $futureJob->update(['required_date' => now()->addWeek()]);

        $this->actingAs($user)
            ->get(route('admin.production.queue.department', ['department' => 'digital', 'due' => 'overdue', 'embedded' => '1']))
            ->assertOk()
            ->assertSee($overdueJob->job_card_number, false)
            ->assertDontSee($futureJob->job_card_number, false);
    }

    public function test_department_queue_shows_live_metrics_and_open_job_360(): void
    {
        [$company, $branch, $user, $digitalCenter] = $this->departmentContext();

        $this->queueJob($company, $branch, $user, $digitalCenter, ProductionType::Digital);

        $this->actingAs($user)
            ->get(route('admin.production.queue.department', 'digital').'?embedded=1')
            ->assertOk()
            ->assertSee(__('Waiting jobs'), false)
            ->assertSee(__('Open Job 360'), false)
            ->assertSee(__('Department operational register'), false)
            ->assertSee(__('More filters'), false);
    }

    public function test_invalid_department_redirects_to_main_queue(): void
    {
        [, , $user] = $this->departmentContext();

        $this->actingAs($user)
            ->get(route('admin.production.queue.department', 'nonexistent').'?embedded=1')
            ->assertRedirect(route('admin.production.queue.index'));
    }

    public function test_cross_tenant_department_queue_forbidden(): void
    {
        [$companyA, $branchA, $userA, $digitalCenter] = $this->departmentContext();

        $job = $this->queueJob($companyA, $branchA, $userA, $digitalCenter, ProductionType::Digital);
        $jobNumber = $job->job_card_number;

        $companyB = Company::factory()->create();
        $branchB = Branch::factory()->create(['company_id' => $companyB->id]);
        $this->seed(ProductionFoundationSeeder::class);
        $userB = User::factory()->create([
            'company_id' => $companyB->id,
            'default_branch_id' => $branchB->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $role = Role::findByName('Production', 'web');
        $role->syncPermissions(['production.queue.view', 'production.view']);
        $userB->assignRole('Production');
        session(['active_company_id' => $companyB->id, 'active_branch_id' => $branchB->id]);

        unset($job);
        $this->actingAs($userB)
            ->get(route('admin.production.queue.department', 'digital').'?embedded=1')
            ->assertOk()
            ->assertDontSee($jobNumber, false);
    }

    /**
     * @return array{0: Company, 1: Branch, 2: User, 3: WorkCenter, 4: WorkCenter}
     */
    protected function departmentContext(): array
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

        $digitalCenter = WorkCenter::query()
            ->where('company_id', $company->id)
            ->where('code', 'DIGITAL')
            ->firstOrFail();

        $offsetCenter = WorkCenter::query()
            ->where('company_id', $company->id)
            ->where('code', 'OFFSET')
            ->firstOrFail();

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);
        app()->instance(TenantContext::class, new TenantContext($company, $branch, false));

        return [$company, $branch, $user, $digitalCenter, $offsetCenter];
    }

    protected function queueJob(
        Company $company,
        Branch $branch,
        User $user,
        WorkCenter $workCenter,
        ProductionType $type,
    ): ProductionJobCard {
        $salesOrder = SalesOrder::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'created_by' => $user->id,
        ]);

        $jobCard = ProductionJobCard::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'sales_order_id' => $salesOrder->id,
            'production_type' => $type,
            'created_by' => $user->id,
        ]);

        ProductionQueue::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'production_job_card_id' => $jobCard->id,
            'work_center_id' => $workCenter->id,
            'queue_position' => ProductionQueue::query()->where('work_center_id', $workCenter->id)->count() + 1,
            'status' => ProductionQueueStatus::Queued,
        ]);

        return $jobCard;
    }
}
