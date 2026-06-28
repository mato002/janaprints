<?php

namespace Tests\Feature\Production;

use App\Enums\ArtworkApprovalDecision;
use App\Enums\ArtworkRequestStatus;
use App\Enums\CustomerStatus;
use App\Enums\ProductionJobCardStatus;
use App\Enums\QualityCheckResult;
use App\Enums\QuotationStatus;
use App\Models\Artwork\ArtworkApproval;
use App\Models\Artwork\ArtworkRequest;
use App\Models\Artwork\ArtworkVersion;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Production\ProductionJobCard;
use App\Models\Sales\Quotation;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use App\Support\Platform\SystemSettingsService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductionQcEnforcementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_qc_pass_moves_job_ready_for_dispatch(): void
    {
        [$company, $branch, , $user, $salesOrder] = $this->productionContext([
            'production.view', 'production.create', 'production.qc',
        ]);

        $this->enableQcRequired($company->id, $branch->id);
        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $jobCard = $this->createJobCard($salesOrder, $user);
        $jobCard->update(['status' => ProductionJobCardStatus::QualityCheck]);

        $this->actingAs($user)
            ->post(route('admin.production.quality-checks.store', $jobCard), [
                'result' => QualityCheckResult::Passed->value,
                'comments' => 'Approved',
            ])
            ->assertRedirect();

        $this->assertEquals(ProductionJobCardStatus::ReadyForDispatch, $jobCard->fresh()->status);
    }

    public function test_qc_fail_moves_job_to_rework(): void
    {
        [, , , $user, $salesOrder] = $this->productionContext([
            'production.view', 'production.create', 'production.qc',
        ]);

        $jobCard = $this->createJobCard($salesOrder, $user);
        $jobCard->update(['status' => ProductionJobCardStatus::QualityCheck]);

        $this->actingAs($user)
            ->post(route('admin.production.quality-checks.store', $jobCard), [
                'result' => QualityCheckResult::Failed->value,
                'rework_reason' => \App\Enums\QualityReworkReason::BadPrint->value,
            ])
            ->assertRedirect();

        $this->assertEquals(ProductionJobCardStatus::Rework, $jobCard->fresh()->status);
    }

    public function test_qc_rework_puts_job_in_rework(): void
    {
        [, , , $user, $salesOrder] = $this->productionContext([
            'production.view', 'production.create', 'production.qc',
        ]);

        $jobCard = $this->createJobCard($salesOrder, $user);
        $jobCard->update(['status' => ProductionJobCardStatus::QualityCheck]);

        $this->actingAs($user)
            ->post(route('admin.production.quality-checks.store', $jobCard), [
                'result' => QualityCheckResult::ReworkRequired->value,
                'rework_reason' => \App\Enums\QualityReworkReason::WrongArtwork->value,
            ])
            ->assertRedirect();

        $this->assertEquals(ProductionJobCardStatus::Rework, $jobCard->fresh()->status);
    }

    public function test_mark_complete_blocked_when_qc_required_without_pass(): void
    {
        [$company, $branch, , $user, $salesOrder] = $this->productionContext([
            'production.view', 'production.create', 'production.complete',
        ]);

        $this->enableQcRequired($company->id, $branch->id);
        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $jobCard = $this->createJobCard($salesOrder, $user);
        $jobCard->update(['status' => ProductionJobCardStatus::QualityCheck]);

        $this->actingAs($user)
            ->post(route('admin.production.job-cards.complete', $jobCard))
            ->assertForbidden();

        $this->assertEquals(ProductionJobCardStatus::QualityCheck, $jobCard->fresh()->status);
    }

    public function test_mark_complete_allowed_when_qc_not_required(): void
    {
        [$company, $branch, , $user, $salesOrder] = $this->productionContext([
            'production.view', 'production.create', 'production.complete',
        ]);

        $this->disableQcRequired($company->id, $branch->id);
        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $jobCard = $this->createJobCard($salesOrder, $user);
        $jobCard->update(['status' => ProductionJobCardStatus::QualityCheck]);

        $this->actingAs($user)
            ->post(route('admin.production.job-cards.complete', $jobCard))
            ->assertRedirect();

        $this->assertEquals(ProductionJobCardStatus::ReadyForDispatch, $jobCard->fresh()->status);
    }

    public function test_qc_submission_requires_production_qc_permission(): void
    {
        [$company, $branch, , , $salesOrder] = $this->productionContext([
            'production.view', 'production.create',
        ]);
        $user = $this->productionUser($company, $branch, ['production.view', 'production.create']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $jobCard = $this->createJobCard($salesOrder, $user);
        $jobCard->update(['status' => ProductionJobCardStatus::QualityCheck]);

        $this->actingAs($user)
            ->post(route('admin.production.quality-checks.store', $jobCard), [
                'result' => QualityCheckResult::Passed->value,
            ])
            ->assertForbidden();
    }

    public function test_mark_complete_requires_production_complete_permission(): void
    {
        [$company, $branch, , , $salesOrder] = $this->productionContext([
            'production.view', 'production.create', 'production.qc',
        ]);
        $qcUser = $this->productionUser($company, $branch, [
            'production.view', 'production.create', 'production.qc',
        ]);

        $this->disableQcRequired($company->id, $branch->id);
        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $jobCard = $this->createJobCard($salesOrder, $qcUser);
        $jobCard->update(['status' => ProductionJobCardStatus::QualityCheck]);

        $this->actingAs($qcUser)
            ->post(route('admin.production.job-cards.complete', $jobCard))
            ->assertForbidden();
    }

    protected function enableQcRequired(int $companyId, int $branchId): void
    {
        app(SystemSettingsService::class)->set(
            'production_qc_required',
            true,
            $companyId,
            $branchId,
            'boolean',
        );
    }

    protected function disableQcRequired(int $companyId, int $branchId): void
    {
        app(SystemSettingsService::class)->set(
            'production_qc_required',
            false,
            $companyId,
            $branchId,
            'boolean',
        );
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
            'customer_code' => 'C-QC-1',
            'company_name' => 'QC Customer',
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
            'status' => \App\Enums\SalesOrderStatus::Confirmed,
            'created_by' => $user->id,
        ]);

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
