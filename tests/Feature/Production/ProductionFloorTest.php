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
            ->assertJsonPath('primary_action.label', __('Record QC'))
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

    public function test_queued_job_on_floor_offers_assign_machine_not_add_to_queue(): void
    {
        [$company, $branch, $customer, $user, $jobCard] = $this->productionContext(withJob: true);

        $this->releaseJobToFloor($jobCard);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);
        app()->instance(\App\Support\TenantContext::class, new \App\Support\TenantContext($company, $branch));

        $this->actingAs($user)
            ->getJson(route('admin.production.floor.panel', $jobCard))
            ->assertOk()
            ->assertJsonPath('primary_action.label', __('Assign machine'))
            ->assertJsonPath('primary_action.type', 'modal')
            ->assertJsonPath('primary_action.target', 'machine')
            ->assertJsonMissing(['primary_action' => ['label' => __('Add to queue')]]);
    }

    public function test_queued_job_with_machine_offers_start_job(): void
    {
        [$company, $branch, $customer, $user, $jobCard] = $this->productionContext(withJob: true);

        $machine = \App\Models\Assets\FixedAsset::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
        ]);

        $this->releaseJobToFloor($jobCard);
        $jobCard->update(['assigned_machine_asset_id' => $machine->id]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);
        app()->instance(\App\Support\TenantContext::class, new \App\Support\TenantContext($company, $branch));

        $this->actingAs($user)
            ->getJson(route('admin.production.floor.panel', $jobCard))
            ->assertOk()
            ->assertJsonPath('primary_action.label', __('Start job'));
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

    protected function releaseJobToFloor(ProductionJobCard $jobCard): void
    {
        $workCenter = WorkCenter::query()->create([
            'company_id' => $jobCard->company_id,
            'branch_id' => $jobCard->branch_id,
            'code' => 'WC-'.substr(md5((string) $jobCard->id), 0, 6),
            'name' => 'Floor test WC',
            'is_active' => true,
        ]);

        app(ProductionQueueService::class)->enqueue($jobCard, $workCenter->id, 1);
    }
}
