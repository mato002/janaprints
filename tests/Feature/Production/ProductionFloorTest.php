<?php

namespace Tests\Feature\Production;

use App\Enums\ProductionJobCardStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\WorkCenter;
use App\Models\User;
use App\Support\Production\ProductionQueueService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductionFloorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_production_floor_page_loads_for_authorized_user(): void
    {
        [$company, $branch, $user] = $this->productionContext();

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);
        app()->instance(\App\Support\TenantContext::class, new \App\Support\TenantContext($company, $branch));

        $this->actingAs($user)
            ->get(route('admin.production.floor', ['embedded' => 1]))
            ->assertOk()
            ->assertSee(__('Production Floor'), false)
            ->assertSee(__('At vendor'), false)
            ->assertSee('production-floor-shell', false)
            ->assertSee('production-floor-command-sticky', false)
            ->assertSee('production-floor-col-job', false)
            ->assertSee(__('Group by'), false);
    }

    public function test_floor_panel_returns_job_context_json(): void
    {
        [$company, $branch, $customer, $user, $jobCard] = $this->productionContext(withJob: true);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);
        app()->instance(\App\Support\TenantContext::class, new \App\Support\TenantContext($company, $branch));

        $this->actingAs($user)
            ->getJson(route('admin.production.floor.panel', $jobCard))
            ->assertOk()
            ->assertJsonPath('header.job_number', $jobCard->job_card_number)
            ->assertJsonStructure(['primary_action', 'outsource', 'fulfilment', 'quality', 'links']);
    }

    public function test_floor_panel_exposes_qc_form_for_quality_check_jobs(): void
    {
        [$company, $branch, $customer, $user, $jobCard] = $this->productionContext(withJob: true);

        $user->givePermissionTo('production.qc');
        $this->releaseJobToFloor($jobCard);
        $jobCard->update(['status' => ProductionJobCardStatus::QualityCheck]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);
        app()->instance(\App\Support\TenantContext::class, new \App\Support\TenantContext($company, $branch));

        $response = $this->actingAs($user)
            ->getJson(route('admin.production.floor.panel', $jobCard))
            ->assertOk()
            ->assertJsonPath('primary_action.label', __('QC'))
            ->assertJsonPath('primary_action.type', 'modal')
            ->assertJsonPath('primary_action.target', 'qc')
            ->assertJsonStructure(['quality' => ['checklist_items']])
            ->assertJsonPath('quality.can_record', true)
            ->assertJsonPath('quality.store_url', route('admin.production.quality-checks.store', $jobCard));

        $this->assertNotEmpty($response->json('quality.checklist_items'));
        $this->assertSame(__('Correct Quantity'), $response->json('quality.checklist_items.0.label'));
    }

    public function test_floor_qc_store_returns_to_floor_when_requested(): void
    {
        [$company, $branch, $customer, $user, $jobCard] = $this->productionContext(withJob: true);

        $user->givePermissionTo('production.qc');
        $this->releaseJobToFloor($jobCard);
        $jobCard->update(['status' => ProductionJobCardStatus::QualityCheck]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);
        app()->instance(\App\Support\TenantContext::class, new \App\Support\TenantContext($company, $branch));

        $this->actingAs($user)
            ->post(route('admin.production.quality-checks.store', $jobCard), [
                'from' => 'production-floor',
                'result' => 'passed',
                'comments' => 'Floor inspection pass',
            ])
            ->assertRedirect(route('admin.production.floor', ['job' => $jobCard->public_id]))
            ->assertSessionHas('status');
    }

    public function test_floor_filters_at_vendor_stage(): void
    {
        [$company, $branch, $customer, $user, $jobCard] = $this->productionContext(withJob: true);

        $jobCard->update(['status' => ProductionJobCardStatus::Outsourced]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);
        app()->instance(\App\Support\TenantContext::class, new \App\Support\TenantContext($company, $branch));

        $this->actingAs($user)
            ->get(route('admin.production.floor', ['stage' => 'at_vendor', 'embedded' => 1]))
            ->assertOk()
            ->assertSee($jobCard->job_card_number, false);
    }

    public function test_draft_jobs_are_excluded_from_production_floor(): void
    {
        [$company, $branch, $customer, $user, $jobCard] = $this->productionContext(withJob: true);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);
        app()->instance(\App\Support\TenantContext::class, new \App\Support\TenantContext($company, $branch));

        $this->actingAs($user)
            ->get(route('admin.production.floor', ['embedded' => 1]))
            ->assertOk()
            ->assertDontSee($jobCard->job_card_number, false);
    }

    public function test_queued_job_on_floor_offers_assign_operator_before_machine(): void
    {
        [$company, $branch, $customer, $user, $jobCard] = $this->productionContext(withJob: true);

        $this->releaseJobToFloor($jobCard);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);
        app()->instance(\App\Support\TenantContext::class, new \App\Support\TenantContext($company, $branch));

        $this->actingAs($user)
            ->getJson(route('admin.production.floor.panel', $jobCard))
            ->assertOk()
            ->assertJsonPath('primary_action.label', __('Assign operator'))
            ->assertJsonPath('primary_action.type', 'modal')
            ->assertJsonPath('primary_action.target', 'operator')
            ->assertJsonMissing(['primary_action' => ['label' => __('Add to queue')]])
            ->assertJsonMissing(['primary_action' => ['label' => __('Start')]])
            ->assertJsonMissing(['primary_action' => ['label' => __('Start work')]]);
    }

    public function test_queued_job_with_operator_and_machine_offers_start_work(): void
    {
        [$company, $branch, $customer, $user, $jobCard] = $this->productionContext(withJob: true);

        $category = \App\Models\Assets\AssetCategory::query()->create([
            'company_id' => $company->id,
            'code' => 'PRINT-'.substr(md5((string) $jobCard->id), 0, 4),
            'name' => 'Print Equipment',
        ]);

        $machine = \App\Models\Assets\FixedAsset::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'asset_category_id' => $category->id,
            'asset_name' => 'Test Floor Press',
            'asset_number' => 'FA-TEST-'.substr(md5((string) $jobCard->id), 0, 6),
            'acquisition_date' => now()->toDateString(),
            'acquisition_cost' => 100000,
            'status' => \App\Enums\FixedAssetStatus::Active,
        ]);

        $operator = \App\Models\User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'is_active' => true,
        ]);

        $this->releaseJobToFloor($jobCard);
        $jobCard->update(['assigned_machine_asset_id' => $machine->id]);

        $queue = $jobCard->queues()->first();
        if ($queue) {
            app(\App\Support\Production\ProductionQueueService::class)->updateEntry($queue, [
                'assigned_operator_id' => $operator->id,
            ]);
        }

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);
        app()->instance(\App\Support\TenantContext::class, new \App\Support\TenantContext($company, $branch));

        $this->actingAs($user)
            ->getJson(route('admin.production.floor.panel', $jobCard))
            ->assertOk()
            ->assertJsonPath('primary_action.label', __('Start work'));
    }

    public function test_design_stage_allows_start_work_without_machine_when_operator_assigned(): void
    {
        [$company, $branch, $customer, $user, $jobCard] = $this->productionContext(withJob: true);

        $operator = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'is_active' => true,
            'name' => 'Grace Wanjiku',
        ]);

        $this->releaseJobToFloor($jobCard, [
            'code' => 'DESIGN',
            'name' => 'Design',
            'requires_machine' => false,
        ]);

        $queue = $jobCard->queues()->firstOrFail();
        app(ProductionQueueService::class)->updateEntry($queue, [
            'assigned_operator_id' => $operator->id,
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);
        app()->instance(\App\Support\TenantContext::class, new \App\Support\TenantContext($company, $branch));

        $this->actingAs($user)
            ->getJson(route('admin.production.floor.panel', $jobCard))
            ->assertOk()
            ->assertJsonPath('primary_action.label', __('Start work'))
            ->assertJsonPath('execution.requires_machine', false)
            ->assertJsonPath('execution.needs_machine', false)
            ->assertJsonPath('execution.is_ready_to_start', true)
            ->assertJsonPath('execution.next_action', __('Ready to start — operator assigned; this stage does not require a machine.'))
            ->assertJsonPath('execution.operator_name', 'Grace Wanjiku');
    }

    public function test_machine_required_stage_blocks_start_until_machine_assigned(): void
    {
        [$company, $branch, $customer, $user, $jobCard] = $this->productionContext(withJob: true);

        $operator = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'is_active' => true,
            'name' => 'Grace Wanjiku',
        ]);

        $this->releaseJobToFloor($jobCard, [
            'code' => 'DIGITAL',
            'name' => 'Digital Printing',
            'requires_machine' => true,
        ]);

        $queue = $jobCard->queues()->firstOrFail();
        app(ProductionQueueService::class)->updateEntry($queue, [
            'assigned_operator_id' => $operator->id,
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);
        app()->instance(\App\Support\TenantContext::class, new \App\Support\TenantContext($company, $branch));

        $this->actingAs($user)
            ->getJson(route('admin.production.floor.panel', $jobCard))
            ->assertOk()
            ->assertJsonPath('primary_action.label', __('Assign machine'))
            ->assertJsonPath('execution.requires_machine', true)
            ->assertJsonPath('execution.needs_machine', true)
            ->assertJsonPath('execution.is_ready_to_start', false)
            ->assertJsonPath('blockers', []);
    }

    public function test_queued_job_360_hides_downstream_completion_blockers(): void
    {
        [$company, $branch, $customer, $user, $jobCard] = $this->productionContext(withJob: true);

        $operator = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'is_active' => true,
            'name' => 'Grace Wanjiku',
        ]);

        $this->releaseJobToFloor($jobCard, [
            'code' => 'DESIGN',
            'name' => 'Design',
            'requires_machine' => false,
        ]);

        $queue = $jobCard->queues()->firstOrFail();
        app(ProductionQueueService::class)->updateEntry($queue, [
            'assigned_operator_id' => $operator->id,
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);
        app()->instance(\App\Support\TenantContext::class, new \App\Support\TenantContext($company, $branch));

        $this->actingAs($user)
            ->get(route('admin.production.job-cards.show', $jobCard))
            ->assertOk()
            ->assertSee(__('Ready to start — operator assigned; this stage does not require a machine.'), false)
            ->assertSee(__('Assigned operator'), false)
            ->assertSee('Grace Wanjiku', false)
            ->assertSee(__('Not required for :stage', ['stage' => 'Design']), false)
            ->assertDontSee(__('Production complete'), false)
            ->assertDontSee(__('Material consumption missing'), false)
            ->assertDontSee(__('items need attention before the job can proceed.'), false);
    }

    public function test_floor_lists_newest_job_cards_first(): void
    {
        [$company, $branch, $user] = $this->productionContext(withJob: false);

        $customer = Customer::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
        ]);

        $older = ProductionJobCard::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'status' => ProductionJobCardStatus::Draft,
            'created_by' => $user->id,
            'created_at' => now()->subDay(),
            'required_date' => now()->addDays(3),
        ]);
        $this->releaseJobToFloor($older);

        $newer = ProductionJobCard::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'status' => ProductionJobCardStatus::Draft,
            'created_by' => $user->id,
            'created_at' => now(),
            'required_date' => now()->addDays(10),
        ]);
        $this->releaseJobToFloor($newer);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);
        app()->instance(\App\Support\TenantContext::class, new \App\Support\TenantContext($company, $branch));

        $response = $this->actingAs($user)
            ->get(route('admin.production.floor', ['embedded' => 1]))
            ->assertOk();

        $this->assertLessThan(
            strpos($response->getContent(), $older->job_card_number),
            strpos($response->getContent(), $newer->job_card_number),
        );
    }

    public function test_floor_assign_machine_persists_via_json(): void
    {
        [$company, $branch, $customer, $user, $jobCard] = $this->productionContext(withJob: true);

        $category = \App\Models\Assets\AssetCategory::query()->create([
            'company_id' => $company->id,
            'code' => 'PRINT-'.substr(md5((string) $jobCard->id), 0, 4),
            'name' => 'Print Equipment',
        ]);

        $machine = \App\Models\Assets\FixedAsset::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'asset_category_id' => $category->id,
            'asset_name' => 'Test Floor Press',
            'asset_number' => 'FA-TEST-'.substr(md5((string) $jobCard->id), 0, 6),
            'acquisition_date' => now()->toDateString(),
            'acquisition_cost' => 100000,
            'status' => \App\Enums\FixedAssetStatus::Active,
        ]);

        $this->releaseJobToFloor($jobCard);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);
        app()->instance(\App\Support\TenantContext::class, new \App\Support\TenantContext($company, $branch));

        $this->actingAs($user)
            ->postJson(route('admin.production.floor.assign-machine', $jobCard), [
                'assigned_machine_asset_id' => $machine->id,
            ])
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('machine_id', $machine->id)
            ->assertJsonPath('machine_name', 'Test Floor Press');

        $this->assertSame($machine->id, $jobCard->fresh()->assigned_machine_asset_id);
    }

    public function test_assign_operator_modal_exposes_plus_button_for_hr_managers(): void
    {
        [$company, $branch, $user] = $this->productionContext();
        $role = Role::findByName('Production', 'web');
        $role->givePermissionTo('employees.manage');
        $user->refresh();

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);
        app()->instance(\App\Support\TenantContext::class, new \App\Support\TenantContext($company, $branch));

        $this->assertTrue($user->can('employees.manage'));

        $this->actingAs($user)
            ->get(route('admin.production.floor', ['embedded' => 1]))
            ->assertOk()
            ->assertSee('erp-lookup-select__add', false)
            ->assertSee('openCreateOperator', false)
            ->assertSee(__('Add new operator'), false);
    }

    public function test_operator_quick_create_form_loads_for_authorized_user(): void
    {
        [$company, $branch, $user] = $this->productionContext();
        $role = Role::findByName('Production', 'web');
        $role->givePermissionTo('employees.manage');
        $user->refresh();

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);
        app()->instance(\App\Support\TenantContext::class, new \App\Support\TenantContext($company, $branch));

        $this->actingAs($user)
            ->get(route('admin.operators.quick-create'))
            ->assertOk()
            ->assertSee(__('Create operator'), false);
    }

    /**
     * @return array{0: Company, 1: Branch, 2?: Customer, 3: User, 4?: ProductionJobCard}
     */
    protected function productionContext(bool $withJob = false): array
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $customer = Customer::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
        ]);
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $role = Role::findByName('Production', 'web');
        $role->syncPermissions(['production.view', 'production.create', 'production.edit', 'production.start', 'production.complete', 'machines.assign']);
        $user->assignRole('Production');

        if (! $withJob) {
            return [$company, $branch, $user];
        }

        $jobCard = ProductionJobCard::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'status' => ProductionJobCardStatus::Draft,
            'created_by' => $user->id,
        ]);

        return [$company, $branch, $customer, $user, $jobCard];
    }

    protected function releaseJobToFloor(ProductionJobCard $jobCard, array $workCenterAttributes = []): void
    {
        $workCenter = WorkCenter::query()->create(array_merge([
            'company_id' => $jobCard->company_id,
            'branch_id' => $jobCard->branch_id,
            'code' => 'WC-'.substr(md5((string) $jobCard->id), 0, 6),
            'name' => 'Floor test WC',
            'is_active' => true,
            'requires_machine' => false,
        ], $workCenterAttributes));

        app(ProductionQueueService::class)->enqueue($jobCard, $workCenter->id, 1);
    }
}
