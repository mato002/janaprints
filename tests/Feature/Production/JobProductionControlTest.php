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
use App\Models\Employee;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionOperation;
use App\Models\Production\ProductionStage;
use App\Models\Production\QualityCheck;
use App\Models\Production\WorkCenter;
use App\Models\Sales\Quotation;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use App\Services\Production\JobProductionControlService;
use Database\Seeders\ProductionFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class JobProductionControlTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_operator_dropdown_is_scoped_to_tenant_branch(): void
    {
        [$company, $branch, , $user, $salesOrder] = $this->productionContext([
            'production.view', 'production.create', 'production.start',
        ]);
        $otherBranch = Branch::factory()->create(['company_id' => $company->id]);
        $jobCard = $this->createJobCard($salesOrder, $user);

        $scoped = $this->createEmployee($company->id, $branch->id, 'Scoped', 'Operator');
        $other = $this->createEmployee($company->id, $otherBranch->id, 'Other', 'Branch');

        $operators = app(JobProductionControlService::class)->scopedOperators($jobCard);

        $this->assertTrue($operators->contains('id', $scoped->id));
        $this->assertFalse($operators->contains('id', $other->id));
    }

    public function test_operator_appears_on_operation_row(): void
    {
        [$company, $branch, , $user, $salesOrder] = $this->productionContext([
            'production.view', 'production.create', 'production.start',
        ]);
        $this->seed(ProductionFoundationSeeder::class);
        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $jobCard = $this->createJobCard($salesOrder, $user);
        $employee = $this->createEmployee($company->id, $branch->id, 'Jane', 'Operator');
        $workCenter = WorkCenter::query()->where('company_id', $company->id)->first();
        $stage = ProductionStage::query()->where('company_id', $company->id)->first();

        ProductionOperation::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'production_job_card_id' => $jobCard->id,
            'work_center_id' => $workCenter->id,
            'production_stage_id' => $stage->id,
            'assigned_employee_id' => $employee->id,
            'started_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'operations']))
            ->assertOk()
            ->assertSee('Jane');
    }

    public function test_dispatch_blocked_when_qc_fails(): void
    {
        [, , , $user, $salesOrder] = $this->productionContext([
            'production.view', 'production.create', 'production.complete',
        ]);
        $jobCard = $this->createJobCard($salesOrder, $user);
        $jobCard->update(['status' => ProductionJobCardStatus::Completed]);

        QualityCheck::query()->create([
            'company_id' => $jobCard->company_id,
            'branch_id' => $jobCard->branch_id,
            'production_job_card_id' => $jobCard->id,
            'checked_by' => $user->id,
            'result' => QualityCheckResult::Failed,
            'checked_at' => now(),
        ]);

        $controls = app(JobProductionControlService::class);
        $this->assertTrue($controls->hasUnresolvedQcFailure($jobCard));
        $this->assertFalse($controls->dispatchEligibility($jobCard)['eligible']);

        $this->actingAs($user)
            ->post(route('admin.production.job-cards.ready-for-dispatch', $jobCard))
            ->assertForbidden();
    }

    public function test_dispatch_blocked_when_operations_incomplete(): void
    {
        [$company, $branch, , $user, $salesOrder] = $this->productionContext([
            'production.view', 'production.create', 'production.complete', 'production.start',
        ]);
        $this->seed(ProductionFoundationSeeder::class);
        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $jobCard = $this->createJobCard($salesOrder, $user);
        $jobCard->update(['status' => ProductionJobCardStatus::Completed]);

        $workCenter = WorkCenter::query()->where('company_id', $company->id)->first();
        $stage = ProductionStage::query()->where('company_id', $company->id)->first();

        ProductionOperation::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'production_job_card_id' => $jobCard->id,
            'work_center_id' => $workCenter->id,
            'production_stage_id' => $stage->id,
            'started_at' => now(),
            'ended_at' => null,
        ]);

        $controls = app(JobProductionControlService::class);
        $this->assertTrue($controls->hasIncompleteOperations($jobCard));
        $this->assertFalse($controls->dispatchEligibility($jobCard)['eligible']);

        $this->actingAs($user)
            ->post(route('admin.production.job-cards.ready-for-dispatch', $jobCard))
            ->assertForbidden();
    }

    public function test_dispatch_readiness_checklist_displays(): void
    {
        [, , , $user, $salesOrder] = $this->productionContext(['production.view', 'production.create']);
        $jobCard = $this->createJobCard($salesOrder, $user);

        $this->actingAs($user)
            ->get(route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'dispatch']))
            ->assertOk()
            ->assertSee(__('Dispatch readiness checklist'), false)
            ->assertSee(__('Operations complete'), false)
            ->assertSee(__('QC passed'), false);
    }

    public function test_materials_readiness_without_consumption(): void
    {
        [, , , $user, $salesOrder] = $this->productionContext(['production.view', 'production.create']);
        $jobCard = $this->createJobCard($salesOrder, $user);

        $state = app(JobProductionControlService::class)->materialsReadinessState($jobCard);

        $this->assertSame('warning', $state['state']);
        $this->assertNotSame('', $state['detail']);
    }

    public function test_wastage_summary_reflects_tracking_availability(): void
    {
        [, , , $user, $salesOrder] = $this->productionContext(['production.view', 'production.create']);
        $jobCard = $this->createJobCard($salesOrder, $user);

        $controls = app(JobProductionControlService::class);
        $wastage = $controls->wastageSummary($jobCard);

        if ($controls->wastageTrackingAvailable()) {
            $this->assertTrue($wastage['activated']);
            $this->assertArrayHasKey('metrics', $wastage);
        } else {
            $this->assertFalse($wastage['activated']);
            $this->assertSame(__('Wastage Tracking Not Activated'), $wastage['placeholder']);
            $this->assertNotEmpty($wastage['recommended_migration']);
        }
    }

    public function test_authorization_rejects_operator_outside_job_branch(): void
    {
        [$company, $branch, , $user, $salesOrder] = $this->productionContext([
            'production.view', 'production.create', 'production.start',
        ]);
        $otherBranch = Branch::factory()->create(['company_id' => $company->id]);

        $this->seed(ProductionFoundationSeeder::class);
        $jobCard = $this->createJobCard($salesOrder, $user);

        $otherBranchEmployee = $this->createEmployee($company->id, $otherBranch->id, 'Other', 'Branch');

        $workCenter = WorkCenter::query()->where('company_id', $company->id)->first();
        $stage = ProductionStage::query()->where('company_id', $company->id)->first();

        $this->actingAs($user)
            ->post(route('admin.production.operations.store', $jobCard), [
                'work_center_id' => $workCenter->id,
                'production_stage_id' => $stage->id,
                'assigned_employee_id' => $otherBranchEmployee->id,
            ]);

        $this->assertDatabaseMissing('production_operations', [
            'production_job_card_id' => $jobCard->id,
            'assigned_employee_id' => $otherBranchEmployee->id,
        ]);
    }

    public function test_tenant_isolation_on_job_show(): void
    {
        $companyA = Company::factory()->create();
        $branchA = Branch::factory()->create(['company_id' => $companyA->id]);
        $companyB = Company::factory()->create();
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

    protected function createEmployee(int $companyId, int $branchId, string $first, string $last): Employee
    {
        return Employee::query()->create([
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'employee_number' => 'EMP-'.uniqid(),
            'first_name' => $first,
            'last_name' => $last,
            'is_active' => true,
        ]);
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
