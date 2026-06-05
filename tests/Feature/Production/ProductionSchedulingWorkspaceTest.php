<?php

namespace Tests\Feature\Production;

use App\Enums\ProductionJobCardStatus;
use App\Enums\ProductionPriority;
use App\Enums\ProductionQueueStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionQueue;
use App\Models\Production\WorkCenter;
use App\Models\User;
use Database\Seeders\ProductionFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductionSchedulingWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_scheduling_workspace_requires_auth(): void
    {
        $this->get(route('admin.production.scheduling.index'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_scheduling_workspace_requires_permission(): void
    {
        [, , $user] = $this->schedulingContext(permissions: ['production.view']);

        $this->actingAs($user)
            ->get(route('admin.production.scheduling.index'))
            ->assertForbidden();
    }

    public function test_scheduling_list_view_renders_kpis_and_jobs(): void
    {
        [$company, $branch, $user, $job] = $this->schedulingContext();

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $response = $this->actingAs($user)->get(route('admin.production.scheduling.index'));

        $response->assertOk();
        $response->assertSee(__('Scheduled Jobs'), false);
        $response->assertSee(__('Unscheduled Jobs'), false);
        $response->assertSee(__('Overdue Jobs'), false);
        $response->assertSee(__('Upcoming Jobs'), false);
        $response->assertSee(__('List view'), false);
        $response->assertSee($job->job_card_number, false);
    }

    public function test_scheduling_calendar_view_renders_month_grid(): void
    {
        [$company, $branch, $user, $job] = $this->schedulingContext();

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $month = $job->planned_start_date->format('Y-m');

        $this->actingAs($user)
            ->get(route('admin.production.scheduling.index', ['view' => 'calendar', 'month' => $month]))
            ->assertOk()
            ->assertSee(__('Calendar view'), false)
            ->assertSee($job->planned_start_date->format('F Y'), false)
            ->assertSee($job->job_card_number, false);
    }

    public function test_scheduling_filters_by_work_center_via_queue(): void
    {
        [$company, $branch, $user, $job, $workCenter] = $this->schedulingContext(withQueue: true);

        $otherJob = ProductionJobCard::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'planned_start_date' => now()->addDays(3),
            'planned_end_date' => now()->addDays(5),
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.production.scheduling.index', ['work_center_id' => $workCenter->id]))
            ->assertOk()
            ->assertSee($job->job_card_number, false)
            ->assertDontSee($otherJob->job_card_number, false);
    }

    public function test_scheduling_intelligence_panels_render(): void
    {
        [$company, $branch, $user, $job, $workCenter] = $this->schedulingContext(withQueue: true);

        $job->update([
            'planned_end_date' => now()->subDay()->toDateString(),
        ]);

        ProductionJobCard::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'planned_start_date' => null,
            'planned_end_date' => null,
        ]);

        $extraJob = ProductionJobCard::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'planned_start_date' => now()->addDays(2),
            'planned_end_date' => now()->addDays(4),
        ]);

        ProductionQueue::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'production_job_card_id' => $extraJob->id,
            'work_center_id' => $workCenter->id,
            'queue_position' => 2,
            'status' => ProductionQueueStatus::Assigned,
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        config(['production.scheduling.default_work_center_capacity' => 1]);

        $this->actingAs($user)
            ->get(route('admin.production.scheduling.index'))
            ->assertOk()
            ->assertSee(__('Work Center Load'), false)
            ->assertSee(__('Assigned jobs'), false)
            ->assertSee(__('Capacity utilization'), false)
            ->assertSee(__('Warnings'), false)
            ->assertSee(__('Late jobs'), false)
            ->assertSee(__('Missing schedule dates'), false)
            ->assertSee($workCenter->name, false)
            ->assertSee(__('Overbooked'), false);
    }

    public function test_capacity_conflict_warning_when_jobs_overlap_on_same_center(): void
    {
        [$company, $branch, $user, , $workCenter] = $this->schedulingContext(withQueue: true);

        $jobB = ProductionJobCard::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'planned_start_date' => now(),
            'planned_end_date' => now()->addDays(5),
            'status' => ProductionJobCardStatus::Queued,
        ]);

        ProductionQueue::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'production_job_card_id' => $jobB->id,
            'work_center_id' => $workCenter->id,
            'queue_position' => 1,
            'status' => ProductionQueueStatus::Assigned,
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.production.scheduling.index'))
            ->assertOk()
            ->assertSee(__('Capacity conflicts'), false)
            ->assertSee($jobB->job_card_number, false);
    }

    public function test_scheduling_kpis_reflect_scheduled_and_unscheduled_jobs(): void
    {
        [$company, $branch, $user] = $this->schedulingContext();

        ProductionJobCard::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'planned_start_date' => now()->addDays(2),
            'planned_end_date' => now()->addDays(5),
        ]);

        ProductionJobCard::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'planned_start_date' => null,
            'planned_end_date' => null,
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $response = $this->actingAs($user)->get(route('admin.production.scheduling.index'));

        $response->assertOk();
        $response->assertSee(__('Scheduled Jobs'), false);
        $response->assertSee(__('Unscheduled Jobs'), false);
        $response->assertSee('>2<', false);
        $response->assertSee('>1<', false);
    }

    public function test_scheduling_filters_by_priority(): void
    {
        [$company, $branch, $user, $job] = $this->schedulingContext();

        $urgentJob = ProductionJobCard::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'priority' => ProductionPriority::Urgent,
            'planned_start_date' => now()->addDays(3),
            'planned_end_date' => now()->addDays(6),
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.production.scheduling.index', ['priority' => ProductionPriority::Urgent->value]))
            ->assertOk()
            ->assertSee($urgentJob->job_card_number, false)
            ->assertDontSee($job->job_card_number, false);
    }

    public function test_scheduling_job_360_links_are_route_safe(): void
    {
        [$company, $branch, $user, $job] = $this->schedulingContext();

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->assertTrue(Route::has('admin.production.job-cards.show'));

        $this->actingAs($user)
            ->get(route('admin.production.scheduling.index'))
            ->assertOk()
            ->assertSee(route('admin.production.job-cards.show', $job), false)
            ->assertSee(__('View Job 360'), false);
    }

    public function test_scheduling_search_filters_list(): void
    {
        [$company, $branch, $user, $job] = $this->schedulingContext();

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.production.scheduling.index', ['search' => $job->job_card_number]))
            ->assertOk()
            ->assertSee($job->job_card_number, false);

        $this->actingAs($user)
            ->get(route('admin.production.scheduling.index', ['search' => 'NO-MATCH-XYZ']))
            ->assertOk()
            ->assertSee(__('No jobs match your filters'), false)
            ->assertSee(__('Schedule Register'), false);
    }

    /**
     * @return array{0: Company, 1: Branch, 2: User, 3: ProductionJobCard, 4?: WorkCenter}
     */
    protected function schedulingContext(
        ?array $permissions = null,
        bool $withQueue = false,
    ): array {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $this->seed(ProductionFoundationSeeder::class);

        $permissions ??= ['production.scheduling.view', 'production.view'];
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $role = Role::findByName('Production', 'web');
        $role->syncPermissions($permissions);
        $user->assignRole('Production');

        $job = ProductionJobCard::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'planned_start_date' => now()->addDay(),
            'planned_end_date' => now()->addDays(7),
            'status' => ProductionJobCardStatus::Queued,
        ]);

        $workCenter = null;
        if ($withQueue) {
            $workCenter = WorkCenter::query()->where('company_id', $company->id)->firstOrFail();
            ProductionQueue::query()->create([
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'production_job_card_id' => $job->id,
                'work_center_id' => $workCenter->id,
                'queue_position' => 1,
                'status' => ProductionQueueStatus::Pending,
            ]);
        }

        return $withQueue
            ? [$company, $branch, $user, $job, $workCenter]
            : [$company, $branch, $user, $job];
    }
}
