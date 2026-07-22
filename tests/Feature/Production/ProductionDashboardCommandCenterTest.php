<?php

namespace Tests\Feature\Production;

use App\Enums\ArtworkRequestStatus;
use App\Enums\CustomerStatus;
use App\Enums\ProductionJobCardStatus;
use App\Enums\ProductionPriority;
use App\Enums\ProductionQueueStatus;
use App\Enums\QuotationStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Artwork\ArtworkRequest;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionQueue;
use App\Models\Production\WorkCenter;
use App\Models\Sales\Quotation;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use App\Services\Production\ProductionDashboardCommandCenterService;
use App\Support\TenantContext;
use Database\Seeders\ProductionFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductionDashboardCommandCenterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_dashboard_loads_for_authorized_user(): void
    {
        [, , , $user] = $this->productionContext();

        $this->actingAs($user)
            ->get(route('admin.production.dashboard'))
            ->assertOk()
            ->assertSee(__('Production Command Center'), false)
            ->assertSee(__('Production Snapshot'), false)
            ->assertSee(__('Production Pipeline'), false)
            ->assertSee(__('Urgent Attention Center'), false)
            ->assertSee(__('Department Capacity'), false)
            ->assertSee(__('Machine Overview'), false)
            ->assertSee('Production Feed', false)
            ->assertSee(__('Quick Actions'), false);
    }

    public function test_dashboard_denied_without_permission(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
        ]);

        $this->actingAs($user)
            ->get(route('admin.production.dashboard'))
            ->assertForbidden();
    }

    public function test_snapshot_renders_six_cards(): void
    {
        [, , , $user] = $this->productionContext();

        $payload = app(ProductionDashboardCommandCenterService::class)->build($user);

        $this->assertCount(6, $payload['snapshot']);
        $keys = collect($payload['snapshot'])->pluck('key')->all();
        $this->assertEquals(
            ['open', 'in_production', 'awaiting_qc', 'ready_for_dispatch', 'delayed', 'completed_today'],
            $keys,
        );
    }

    public function test_pipeline_includes_scheduled_stage(): void
    {
        [, , , $user, $salesOrder] = $this->productionContext();
        $this->createJobCard($salesOrder, $user, [
            'status' => ProductionJobCardStatus::Draft,
            'planned_start_date' => now()->toDateString(),
            'planned_end_date' => now()->addDays(3)->toDateString(),
        ]);

        $pipeline = collect(app(ProductionDashboardCommandCenterService::class)->build($user)['pipeline'])
            ->keyBy('key');

        $this->assertArrayHasKey('scheduled', $pipeline->all());
        $this->assertGreaterThanOrEqual(1, $pipeline['scheduled']['count']);
    }

    public function test_urgent_center_includes_escalated_jobs(): void
    {
        [, , , $user, $salesOrder] = $this->productionContext();
        $this->createJobCard($salesOrder, $user, [
            'status' => ProductionJobCardStatus::InProduction,
            'priority' => ProductionPriority::Urgent,
            'planned_end_date' => now()->addDay(),
        ]);

        $response = $this->actingAs($user)->get(route('admin.production.dashboard'));

        $response->assertOk()->assertSee(__('Escalated Jobs'), false);
    }

    public function test_quick_actions_include_command_center_operations(): void
    {
        [, , , $user] = $this->productionContext();

        $labels = collect(app(ProductionDashboardCommandCenterService::class)->build($user)['quick_actions'])
            ->pluck('label')
            ->all();

        $this->assertContains(__('Create Job Card'), $labels);
        $this->assertContains(__('Schedule Job'), $labels);
        $this->assertContains(__('QC'), $labels);
        $this->assertContains(__('Create Delivery Note'), $labels);
    }

    public function test_quick_actions_respect_permissions(): void
    {
        [, , , $user] = $this->productionContext(['production.view']);

        $labels = collect(app(ProductionDashboardCommandCenterService::class)->build($user)['quick_actions'])
            ->pluck('label')
            ->all();

        $this->assertNotContains(__('Create Job Card'), $labels);
        $this->assertNotContains(__('Schedule Job'), $labels);
        $this->assertNotContains(__('QC'), $labels);
    }

    public function test_empty_states_render_safely(): void
    {
        [, , , $user] = $this->productionContext();

        $this->actingAs($user)
            ->get(route('admin.production.dashboard'))
            ->assertOk()
            ->assertSee(__('All clear'), false);
    }

    public function test_command_center_service_builds_all_sections(): void
    {
        [, , , $user, $salesOrder] = $this->productionContext();
        $job = $this->createJobCard($salesOrder, $user, [
            'status' => ProductionJobCardStatus::InProduction,
            'planned_start_date' => now()->toDateString(),
            'planned_end_date' => now()->toDateString(),
        ]);

        $workCenter = WorkCenter::query()
            ->where('company_id', $job->company_id)
            ->where('branch_id', $job->branch_id)
            ->firstOrFail();
        ProductionQueue::query()->create([
            'company_id' => $job->company_id,
            'branch_id' => $job->branch_id,
            'production_job_card_id' => $job->id,
            'work_center_id' => $workCenter->id,
            'queue_position' => 1,
            'status' => ProductionQueueStatus::Assigned,
        ]);

        $payload = app(ProductionDashboardCommandCenterService::class)->build($user);

        $this->assertArrayHasKey('snapshot', $payload);
        $this->assertArrayHasKey('pipeline', $payload);
        $this->assertArrayHasKey('urgent', $payload);
        $this->assertArrayHasKey('department_capacity', $payload);
        $this->assertArrayHasKey('machine_capacity', $payload);
        $this->assertArrayHasKey('activity', $payload);
        $this->assertArrayHasKey('quick_actions', $payload);
        $this->assertCount(7, $payload['department_capacity']);
        $this->assertArrayHasKey('escalated_jobs', $payload['urgent']);
    }

    /**
     * @param  list<string>  $permissions
     * @return array{0: Company, 1: Branch, 2: Customer, 3: User, 4: SalesOrder}
     */
    protected function productionContext(array $permissions = []): array
    {
        $permissions = $permissions === [] ? [
            'production.view',
            'production.create',
            'production.queue.view',
            'production.scheduling.view',
            'production.quality.view',
            'production.work-centers.view',
            'dispatch.view',
        ] : $permissions;

        $company = Company::factory()->create(['code' => 'JANA']);
        $branch = Branch::factory()->create(['company_id' => $company->id, 'code' => 'HQ']);
        $customer = Customer::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'status' => CustomerStatus::Active,
        ]);
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
        ]);
        $role = Role::findByName('Company Admin', 'web');
        $role->syncPermissions($permissions);
        $user->assignRole($role);

        $quotation = Quotation::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'status' => QuotationStatus::Converted,
        ]);
        $artwork = ArtworkRequest::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'quotation_id' => $quotation->id,
            'status' => ArtworkRequestStatus::Approved,
        ]);
        $salesOrder = SalesOrder::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'quotation_id' => $quotation->id,
            'artwork_request_id' => $artwork->id,
            'status' => SalesOrderStatus::Confirmed,
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->seed(ProductionFoundationSeeder::class);

        app()->instance(TenantContext::class, new TenantContext($company, $branch, false));

        return [$company, $branch, $customer, $user, $salesOrder];
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    protected function createJobCard(SalesOrder $salesOrder, User $user, array $overrides = []): ProductionJobCard
    {
        return ProductionJobCard::factory()->create(array_merge([
            'company_id' => $salesOrder->company_id,
            'branch_id' => $salesOrder->branch_id,
            'sales_order_id' => $salesOrder->id,
            'customer_id' => $salesOrder->customer_id,
            'quotation_id' => $salesOrder->quotation_id,
            'artwork_request_id' => $salesOrder->artwork_request_id,
            'created_by' => $user->id,
            'status' => ProductionJobCardStatus::Draft,
        ], $overrides));
    }
}
