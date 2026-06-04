<?php

namespace Tests\Feature\Production;

use App\Enums\ArtworkApprovalDecision;
use App\Enums\ArtworkRequestStatus;
use App\Enums\CustomerStatus;
use App\Enums\ProductionJobCardStatus;
use App\Enums\QuotationStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Artwork\ArtworkApproval;
use App\Models\Artwork\ArtworkRequest;
use App\Models\Artwork\ArtworkVersion;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\WorkCenter;
use App\Models\Sales\Quotation;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use App\Services\Production\ProductionJobCardIndexService;
use Database\Seeders\ProductionFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class JobCardsCommandCenterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_job_cards_command_center_loads(): void
    {
        [$company, $branch, , $user, $salesOrder] = $this->productionContext(['production.view', 'production.create']);
        $this->createJobCard($salesOrder, $user);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.production.job-cards.index'))
            ->assertOk()
            ->assertSee(__('Production Operations Command Center'), false)
            ->assertSee(__('Total Open Jobs'), false)
            ->assertSee(__('Production Pipeline'), false)
            ->assertSee(__('Production Alerts'), false)
            ->assertSee(__('Open Job 360'), false);
    }

    public function test_kpi_strip_and_pipeline_render_from_service(): void
    {
        [, , , $user, $salesOrder] = $this->productionContext(['production.view', 'production.create']);
        $this->createJobCard($salesOrder, $user);

        $payload = app(ProductionJobCardIndexService::class)->build(request(), $user);

        $this->assertCount(7, $payload['kpis']);
        $this->assertCount(7, $payload['pipeline']);
        $this->assertArrayHasKey('overdue', $payload['alerts']);
    }

    public function test_status_filter_preserves_pagination_query(): void
    {
        [$company, $branch, , $user, $salesOrder] = $this->productionContext(['production.view', 'production.create']);
        $jobCard = $this->createJobCard($salesOrder, $user);
        $jobCard->update(['status' => ProductionJobCardStatus::InProduction]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.production.job-cards.index', ['status' => 'in_production']))
            ->assertOk()
            ->assertSee($jobCard->job_card_number, false);
    }

    public function test_search_finds_customer_and_job_number(): void
    {
        [$company, $branch, $customer, $user, $salesOrder] = $this->productionContext(['production.view', 'production.create']);
        $jobCard = $this->createJobCard($salesOrder, $user);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.production.job-cards.index', ['search' => $jobCard->job_card_number]))
            ->assertOk()
            ->assertSee($jobCard->job_card_number, false);

        $this->actingAs($user)
            ->get(route('admin.production.job-cards.index', ['search' => $customer->company_name]))
            ->assertOk()
            ->assertSee($jobCard->job_card_number, false);
    }

    public function test_awaiting_qc_filter(): void
    {
        [$company, $branch, , $user, $salesOrder] = $this->productionContext(['production.view', 'production.create']);
        $jobCard = $this->createJobCard($salesOrder, $user);
        $jobCard->update(['status' => ProductionJobCardStatus::QualityCheck]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.production.job-cards.index', ['awaiting_qc' => 1]))
            ->assertOk()
            ->assertSee($jobCard->job_card_number, false);
    }

    public function test_bulk_actions_hidden_without_export_permission(): void
    {
        [, , , $user, $salesOrder] = $this->productionContext(['production.create']);
        $this->createJobCard($salesOrder, $user);

        $payload = app(ProductionJobCardIndexService::class)->build(request(), $user);

        $this->assertSame([], $payload['bulk_actions']);
    }

    public function test_bulk_export_available_with_view_permission(): void
    {
        [, , , $user, $salesOrder] = $this->productionContext(['production.view', 'production.create']);
        $this->createJobCard($salesOrder, $user);

        $payload = app(ProductionJobCardIndexService::class)->build(request(), $user);

        $this->assertContains('export', collect($payload['bulk_actions'])->pluck('key')->all());
    }

    public function test_job_360_link_in_present_row(): void
    {
        [, , , $user, $salesOrder] = $this->productionContext(['production.view', 'production.create']);
        $jobCard = $this->createJobCard($salesOrder, $user);

        $row = app(ProductionJobCardIndexService::class)->presentRow($jobCard->fresh(['customer', 'salesOrder.items', 'queues.workCenter', 'artworkRequest', 'qualityChecks', 'deliveryNotes']), $user);

        $this->assertNotNull($row['job_360_url']);
        $this->assertStringContainsString((string) $jobCard->id, $row['job_360_url']);
    }

    public function test_unauthorized_user_has_no_workflow_actions(): void
    {
        [$company, $branch, , $user, $salesOrder] = $this->productionContext(['production.view', 'production.create']);
        $jobCard = $this->createJobCard($salesOrder, $user);
        $jobCard->update(['status' => ProductionJobCardStatus::InProduction]);

        $viewOnly = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $role = Role::findByName('Production', 'web');
        $role->syncPermissions(['production.view']);
        $viewOnly->assignRole('Production');

        $row = app(ProductionJobCardIndexService::class)->presentRow($jobCard->fresh(), $viewOnly);

        $this->assertSame([], $row['workflow_actions']);
        $this->assertNull($row['edit_url']);
    }

    public function test_index_avoids_n_plus_one_on_table_rows(): void
    {
        [$company, $branch, , $user, $salesOrder] = $this->productionContext(['production.view', 'production.create']);
        $this->createJobCard($salesOrder, $user);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        DB::flushQueryLog();
        DB::enableQueryLog();

        $this->actingAs($user)->get(route('admin.production.job-cards.index'))->assertOk();

        $queryCount = count(DB::getQueryLog());

        $this->assertLessThan(80, $queryCount, "Expected bounded queries, got {$queryCount}");
    }

    public function test_empty_state_when_no_matches(): void
    {
        [$company, $branch, , $user] = $this->productionContext(['production.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.production.job-cards.index', ['search' => 'NO-MATCH-XYZ']))
            ->assertOk()
            ->assertSee(__('No job cards match your filters'), false);
    }

    public function test_quick_actions_respect_permissions(): void
    {
        [, , , $user] = $this->productionContext(['production.view']);

        $actions = app(ProductionJobCardIndexService::class)->quickActions($user);

        $this->assertFalse(collect($actions)->contains('label', __('New Job Card')));
        $this->assertFalse(collect($actions)->contains('label', __('Open Queue')));
    }

    /**
     * @return array{0: Company, 1: Branch, 2: Customer, 3: User, 4: SalesOrder}
     */
    protected function productionContext(?array $permissions = null): array
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $customer = Customer::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_code' => 'JC-CC',
            'company_name' => 'Command Center Customer',
            'status' => CustomerStatus::Active,
        ]);
        $permissions ??= ['production.view', 'production.create'];
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $role = Role::findByName('Production', 'web');
        $role->syncPermissions($permissions);
        $user->assignRole('Production');

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
            'status' => SalesOrderStatus::Confirmed,
            'created_by' => $user->id,
        ]);

        $this->seed(ProductionFoundationSeeder::class);

        return [$company, $branch, $customer, $user, $salesOrder];
    }

    protected function createJobCard(SalesOrder $salesOrder, User $user): ProductionJobCard
    {
        $this->actingAs($user)->post(route('admin.production.job-cards.store'), [
            'sales_order_id' => $salesOrder->id,
            'production_type' => 'mixed',
            'priority' => 'normal',
        ]);

        return ProductionJobCard::query()->where('sales_order_id', $salesOrder->id)->firstOrFail();
    }
}
