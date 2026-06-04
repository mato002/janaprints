<?php

namespace Tests\Feature\Production;

use App\Enums\ArtworkRequestStatus;
use App\Enums\CustomerStatus;
use App\Enums\ProductionJobCardStatus;
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
            ->assertSee(__('Production Pipeline'), false)
            ->assertSee(__('Urgent Attention'), false)
            ->assertSee('Production Schedule', false)
            ->assertSee(__('Work Center Load'), false)
            ->assertSee(__('Recent Production Activity'), false)
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

    public function test_kpi_strip_renders_eight_cards(): void
    {
        [, , , $user] = $this->productionContext();

        $payload = app(ProductionDashboardCommandCenterService::class)->build($user);

        $this->assertCount(8, $payload['kpis']);
        $this->assertArrayHasKey('open', collect($payload['kpis'])->keyBy('key')->all());
    }

    public function test_pipeline_and_urgent_sections_render_with_data(): void
    {
        [, , , $user, $salesOrder] = $this->productionContext();
        $this->createJobCard($salesOrder, $user, ['status' => ProductionJobCardStatus::Queued]);

        $response = $this->actingAs($user)->get(route('admin.production.dashboard'));

        $response->assertOk()->assertSee(__('Queued'), false);
    }

    public function test_quick_actions_respect_permissions(): void
    {
        [, , , $user] = $this->productionContext(['production.view']);

        $this->actingAs($user)
            ->get(route('admin.production.dashboard'))
            ->assertOk()
            ->assertSee(__('Job Cards'), false)
            ->assertDontSee(__('New Job Card'), false);

        $labels = collect(app(ProductionDashboardCommandCenterService::class)->build($user)['quick_actions'])
            ->pluck('label')
            ->all();

        $this->assertContains(__('Job Cards'), $labels);
        $this->assertNotContains(__('Queue'), $labels);
        $this->assertNotContains(__('Scheduling'), $labels);
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

        $this->assertArrayHasKey('kpis', $payload);
        $this->assertArrayHasKey('pipeline', $payload);
        $this->assertArrayHasKey('urgent', $payload);
        $this->assertArrayHasKey('schedule', $payload);
        $this->assertArrayHasKey('work_center_load', $payload);
        $this->assertArrayHasKey('activity', $payload);
        $this->assertArrayHasKey('quick_actions', $payload);
        $this->assertArrayHasKey('performance', $payload);
        $this->assertNotEmpty($payload['schedule']);
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
