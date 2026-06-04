<?php

namespace Tests\Feature\Production;

use App\Enums\ArtworkApprovalDecision;
use App\Enums\ArtworkRequestStatus;
use App\Enums\CustomerStatus;
use App\Enums\ProductionJobCardStatus;
use App\Enums\QualityCheckResult;
use App\Enums\QuotationStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Artwork\ArtworkApproval;
use App\Models\Artwork\ArtworkRequest;
use App\Models\Artwork\ArtworkVersion;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\QualityCheck;
use App\Models\Sales\Quotation;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use App\Services\Production\Job360WorkspaceService;
use Database\Seeders\ProductionFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class Job360WorkspaceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_job_360_show_loads(): void
    {
        [, , , $user, $salesOrder] = $this->productionContext(['production.view', 'production.create']);
        $jobCard = $this->createJobCard($salesOrder, $user);

        $this->actingAs($user)
            ->get(route('admin.production.job-cards.show', $jobCard))
            ->assertOk()
            ->assertSee($jobCard->job_card_number)
            ->assertSee(__('Overview'), false)
            ->assertSee(__('Operation completion'), false);
    }

    public function test_header_data_displays(): void
    {
        [, , $customer, $user, $salesOrder] = $this->productionContext(['production.view', 'production.create']);
        $jobCard = $this->createJobCard($salesOrder, $user);

        $payload = app(Job360WorkspaceService::class)->build($jobCard);

        $this->assertSame($jobCard->job_card_number, $payload['header']['job_number']);
        $this->assertSame($customer->company_name, $payload['header']['customer_name']);
        $this->assertSame($salesOrder->order_number, $payload['header']['sales_order_number']);
    }

    public function test_traceability_tab_loads(): void
    {
        [, , , $user, $salesOrder] = $this->productionContext(['production.view', 'production.create']);
        $jobCard = $this->createJobCard($salesOrder, $user);

        $this->actingAs($user)
            ->get(route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'traceability']))
            ->assertOk()
            ->assertSee(__('End-to-end traceability'), false)
            ->assertSee(__('Quotation'), false)
            ->assertSee(__('Delivery tracking available after Dispatch module activation'), false);
    }

    public function test_artwork_tab_loads(): void
    {
        [, , , $user, $salesOrder] = $this->productionContext(['production.view', 'production.create', 'artwork.view']);
        $jobCard = $this->createJobCard($salesOrder, $user);

        $this->actingAs($user)
            ->get(route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'artwork']))
            ->assertOk()
            ->assertSee(__('Artwork request'), false);
    }

    public function test_operations_tab_loads(): void
    {
        [$company, $branch, , $user, $salesOrder] = $this->productionContext([
            'production.view', 'production.create', 'production.start',
        ]);
        $this->seed(ProductionFoundationSeeder::class);
        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $jobCard = $this->createJobCard($salesOrder, $user);

        $this->actingAs($user)
            ->get(route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'operations']))
            ->assertOk()
            ->assertSee(__('Operations log'), false);
    }

    public function test_materials_tab_loads_with_bom_warning(): void
    {
        [, , , $user, $salesOrder] = $this->productionContext(['production.view', 'production.create', 'inventory.issue']);
        $jobCard = $this->createJobCard($salesOrder, $user);

        $this->actingAs($user)
            ->get(route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'materials']))
            ->assertOk()
            ->assertSee(__('Required materials/BOM not yet activated'), false);
    }

    public function test_qc_tab_loads(): void
    {
        [, , , $user, $salesOrder] = $this->productionContext(['production.view', 'production.create', 'production.qc']);
        $jobCard = $this->createJobCard($salesOrder, $user);
        $jobCard->update(['status' => ProductionJobCardStatus::QualityCheck]);

        QualityCheck::query()->create([
            'company_id' => $jobCard->company_id,
            'branch_id' => $jobCard->branch_id,
            'production_job_card_id' => $jobCard->id,
            'checked_by' => $user->id,
            'result' => QualityCheckResult::Failed,
            'comments' => 'Defect found',
            'checked_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'quality']))
            ->assertOk()
            ->assertSee(__('Quality checks'), false)
            ->assertSee('Defect found');
    }

    public function test_dispatch_tab_placeholder_works(): void
    {
        [, , , $user, $salesOrder] = $this->productionContext(['production.view', 'production.create']);
        $jobCard = $this->createJobCard($salesOrder, $user);

        $this->actingAs($user)
            ->get(route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'dispatch']))
            ->assertOk()
            ->assertSee(__('Delivery history will appear when dispatch module is activated'), false);
    }

    public function test_timeline_tab_loads(): void
    {
        [, , , $user, $salesOrder] = $this->productionContext(['production.view', 'production.create']);
        $jobCard = $this->createJobCard($salesOrder, $user);

        $this->actingAs($user)
            ->get(route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'timeline']))
            ->assertOk()
            ->assertSee(__('Unified job timeline'), false);
    }

    public function test_authorization_hides_restricted_quick_actions(): void
    {
        [, , , $user, $salesOrder] = $this->productionContext(['production.view', 'production.create']);
        $jobCard = $this->createJobCard($salesOrder, $user);

        $payload = app(Job360WorkspaceService::class)->build($jobCard);
        $labels = collect($payload['quick_actions'])->pluck('label')->all();

        $this->assertNotContains(__('Start Job'), $labels);
        $this->assertNotContains(__('Consume Material'), $labels);
    }

    public function test_tenant_isolation_preserved(): void
    {
        $companyA = Company::factory()->create(['code' => 'JA']);
        $companyB = Company::factory()->create(['code' => 'JB']);
        $branchA = Branch::factory()->create(['company_id' => $companyA->id]);
        $branchB = Branch::factory()->create(['company_id' => $companyB->id]);

        $user = $this->productionUser($companyA, $branchA, ['production.view']);
        $jobCardB = ProductionJobCard::factory()->create([
            'company_id' => $companyB->id,
            'branch_id' => $branchB->id,
        ]);

        $this->actingAs($user)
            ->get(route('admin.production.job-cards.show', $jobCardB))
            ->assertForbidden();
    }

    public function test_no_duplicate_job_show_routes(): void
    {
        $matches = collect(Route::getRoutes())->filter(
            fn ($route) => $route->getName() === 'admin.production.job-cards.show',
        );

        $this->assertCount(1, $matches);
    }

    public function test_timeline_service_aggregates_job_creation(): void
    {
        [, , , $user, $salesOrder] = $this->productionContext(['production.view', 'production.create']);
        $jobCard = $this->createJobCard($salesOrder, $user);

        $payload = app(Job360WorkspaceService::class)->build($jobCard, 'timeline');
        $events = $payload['tab_data']['events'];

        $this->assertNotNull($events);
        $this->assertGreaterThanOrEqual(1, $events->total());
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
            'customer_code' => 'C-J360',
            'company_name' => 'Job 360 Customer',
            'status' => CustomerStatus::Active,
        ]);
        $permissions ??= ['production.view', 'production.create'];
        $user = $this->productionUser($company, $branch, $permissions);

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

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        return [$company, $branch, $customer, $user, $salesOrder];
    }

    protected function productionUser(Company $company, Branch $branch, array $permissions): User
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
