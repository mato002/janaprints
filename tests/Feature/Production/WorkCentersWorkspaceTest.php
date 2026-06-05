<?php

namespace Tests\Feature\Production;

use App\Enums\ProductionQueueStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionQueue;
use App\Models\Production\ProductionStage;
use App\Models\Production\WorkCenter;
use App\Models\User;
use Database\Seeders\ProductionFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WorkCentersWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_work_centers_index_requires_permission(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $user = $this->userWithPermissions($company, $branch, ['production.view']);

        $this->actingAs($user)
            ->get(route('admin.production.work-centers.index'))
            ->assertForbidden();
    }

    public function test_work_centers_index_loads_with_filters_and_empty_state(): void
    {
        [$company, $branch, $user, $prepressStage] = $this->workCenterContext();

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $response = $this->actingAs($user)->get(route('admin.production.work-centers.index'));

        $response->assertOk();
        $response->assertSee(__('Work Center Register'), false);
        $response->assertSee('Prepress', false);
        $response->assertDontSee(__('Bottleneck Detection'), false);
        $response->assertDontSee(__('Work centers with queued jobs'), false);

        $response = $this->actingAs($user)->get(route('admin.production.work-centers.index', [
            'stage_id' => $prepressStage->id,
            'status' => 'active',
            'search' => 'Prepress',
        ]));

        $response->assertOk();
        $response->assertSee('Prepress', false);
        $response->assertDontSee('OFFSET', false);
    }

    public function test_work_center_show_loads_metrics_and_active_queues(): void
    {
        [$company, $branch, $user, , $workCenter, $jobCard] = $this->workCenterContext(withQueue: true);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $response = $this->actingAs($user)->get(route('admin.production.work-centers.show', $workCenter));

        $response->assertOk();
        $response->assertSee($workCenter->name, false);
        $response->assertSee($workCenter->code, false);
        $response->assertSee(__('Active jobs'), false);
        $response->assertSee($jobCard->job_card_number, false);
    }

    public function test_empty_work_center_show_does_not_break(): void
    {
        [$company, $branch, $user, , $workCenter] = $this->workCenterContext();

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.production.work-centers.show', $workCenter))
            ->assertOk()
            ->assertSee(__('No active queue entries'), false);
    }

    public function test_work_center_show_blocks_other_tenant(): void
    {
        [$company, $branch, $user] = $this->workCenterContext();

        $otherCompany = Company::factory()->create();
        $otherBranch = Branch::factory()->create(['company_id' => $otherCompany->id]);
        $this->seed(ProductionFoundationSeeder::class);

        $foreignCenter = WorkCenter::query()
            ->where('company_id', $otherCompany->id)
            ->where('branch_id', $otherBranch->id)
            ->firstOrFail();

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.production.work-centers.show', $foreignCenter))
            ->assertForbidden();
    }

    /**
     * @return array{0: Company, 1: Branch, 2: User, 3: ProductionStage, 4: WorkCenter, 5: ?ProductionJobCard}
     */
    protected function workCenterContext(bool $withQueue = false): array
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $user = $this->userWithPermissions($company, $branch, ['production.work-centers.view']);

        $this->seed(ProductionFoundationSeeder::class);

        $prepressStage = ProductionStage::query()
            ->where('company_id', $company->id)
            ->where('code', 'PREPRESS')
            ->firstOrFail();

        $workCenter = WorkCenter::query()
            ->where('company_id', $company->id)
            ->where('code', 'PREPRESS')
            ->firstOrFail();

        $jobCard = null;

        if ($withQueue) {
            $jobCard = ProductionJobCard::factory()->create([
                'company_id' => $company->id,
                'branch_id' => $branch->id,
            ]);

            ProductionQueue::query()->create([
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'production_job_card_id' => $jobCard->id,
                'work_center_id' => $workCenter->id,
                'queue_position' => 1,
                'status' => ProductionQueueStatus::Pending,
            ]);
        }

        return [$company, $branch, $user, $prepressStage, $workCenter, $jobCard];
    }

    protected function userWithPermissions(Company $company, Branch $branch, array $permissions): User
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
