<?php

namespace Tests\Feature\Procurement;

use App\Enums\ApprovalChainRunStatus;
use App\Enums\ApprovalChainStepStatus;
use App\Enums\ApprovalRuleType;
use App\Enums\DelegationStatus;
use App\Enums\EscalationMethod;
use App\Enums\EscalationRuleStatus;
use App\Enums\PurchaseRequestStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Governance\ApprovalChainStepRecord;
use App\Models\Governance\WorkflowEscalationRule;
use App\Models\Inventory\InventoryItem;
use App\Models\Platform\ApprovalDelegation;
use App\Models\Platform\ApprovalRule;
use App\Models\Procurement\PurchaseRequest;
use App\Models\Procurement\Vendor;
use App\Models\User;
use App\Support\Governance\ApprovalEnforcementEngine;
use App\Support\Governance\EscalationEngine;
use App\Support\Procurement\PurchaseRequestService;
use App\Support\TenantContext;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\PlatformConfigurationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PurchaseRequestApprovalGovernanceTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        $this->seed(PlatformConfigurationSeeder::class);

        $this->company = Company::query()->where('code', 'JANA')->firstOrFail();
        $this->branch = Branch::query()->where('company_id', $this->company->id)->where('code', 'HQ')->firstOrFail();

        session(['active_company_id' => $this->company->id, 'active_branch_id' => $this->branch->id]);
        app()->instance(TenantContext::class, new TenantContext($this->company, $this->branch, false));
    }

    public function test_purchase_request_requires_approval_when_above_threshold(): void
    {
        [$submitter, $approver, $request] = $this->purchaseRequestActors(totalAmount: 15000);

        PurchaseRequestService::submit($request, $submitter->id);
        $request->refresh();

        $this->assertSame(PurchaseRequestStatus::PendingApproval, $request->status);
        $this->assertNotNull($request->submitted_at);
        $this->assertNotNull(app(ApprovalEnforcementEngine::class)->latestRun($request));
    }

    public function test_purchase_request_auto_approves_below_threshold(): void
    {
        [$submitter, , $request] = $this->purchaseRequestActors(totalAmount: 5000);

        PurchaseRequestService::submit($request, $submitter->id);

        $request->refresh();
        $this->assertSame(PurchaseRequestStatus::Approved, $request->status);
        $this->assertNull(app(ApprovalEnforcementEngine::class)->latestRun($request));
    }

    public function test_purchase_request_approval_chain_completes(): void
    {
        [$submitter, $approver, $request] = $this->purchaseRequestActors(totalAmount: 15000);

        PurchaseRequestService::submit($request, $submitter->id);
        PurchaseRequestService::approve($request->fresh(), $approver, 'Within budget');

        $request->refresh();
        $run = app(ApprovalEnforcementEngine::class)->latestRun($request);

        $this->assertSame(PurchaseRequestStatus::Approved, $request->status);
        $this->assertSame(ApprovalChainRunStatus::Approved, $run->status);
        $this->assertTrue(app(ApprovalEnforcementEngine::class)->hasApprovedChain($request));
        $this->assertSame($approver->id, $request->approved_by);
    }

    public function test_purchase_request_rejection_marks_chain_rejected(): void
    {
        [$submitter, $approver, $request] = $this->purchaseRequestActors(totalAmount: 15000);

        PurchaseRequestService::submit($request, $submitter->id);
        PurchaseRequestService::reject($request->fresh(), $approver, 'Not in budget');

        $request->refresh();
        $run = app(ApprovalEnforcementEngine::class)->latestRun($request);

        $this->assertSame(PurchaseRequestStatus::Rejected, $request->status);
        $this->assertSame(ApprovalChainRunStatus::Rejected, $run->status);
        $this->assertSame('Not in budget', $request->rejection_reason);
    }

    public function test_delegate_can_complete_purchase_request_approval_chain_step(): void
    {
        [$submitter, $approver, $request] = $this->purchaseRequestActors(totalAmount: 15000);

        $delegate = User::factory()->create([
            'company_id' => $this->company->id,
            'default_branch_id' => $this->branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        Role::findByName('Company Admin', 'web')->syncPermissions(['procurement.requests.approve']);
        $delegate->assignRole('Company Admin');

        ApprovalDelegation::query()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'delegator_user_id' => $approver->id,
            'delegate_user_id' => $delegate->id,
            'modules' => ['procurement'],
            'approval_types' => [ApprovalRuleType::PurchaseRequestApproval->value],
            'reason' => 'annual_leave',
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addWeek()->toDateString(),
            'status' => DelegationStatus::Active,
            'created_by_user_id' => $approver->id,
        ]);

        PurchaseRequestService::submit($request, $submitter->id);
        PurchaseRequestService::approve($request->fresh(), $delegate, 'Delegated approval');

        $run = app(ApprovalEnforcementEngine::class)->latestRun($request->fresh());
        $this->assertSame(PurchaseRequestStatus::Approved, $request->fresh()->status);
        $this->assertSame(ApprovalChainRunStatus::Approved, $run->status);
    }

    public function test_escalation_marks_overdue_purchase_request_chain_step_as_escalated(): void
    {
        [$submitter, $approver, $request] = $this->purchaseRequestActors(totalAmount: 15000);
        PurchaseRequestService::submit($request, $submitter->id);

        $run = app(ApprovalEnforcementEngine::class)->latestRun($request);
        $record = ApprovalChainStepRecord::query()->where('approval_chain_run_id', $run->id)->firstOrFail();
        ApprovalChainStepRecord::query()->whereKey($record->id)->update(['created_at' => now()->subHours(25)]);
        $record->refresh();

        Role::findOrCreate('Operations Manager', 'web');

        WorkflowEscalationRule::query()
            ->where('company_id', $this->company->id)
            ->where('workflow_key', 'purchase_request')
            ->update(['status' => EscalationRuleStatus::Inactive]);

        WorkflowEscalationRule::query()->create([
            'company_id' => $this->company->id,
            'branch_id' => null,
            'name' => 'PR approval escalation test',
            'workflow_key' => 'purchase_request',
            'waiting_hours' => 24,
            'escalation_target_role' => 'Operations Manager',
            'escalation_method' => EscalationMethod::AutoEscalate,
            'status' => EscalationRuleStatus::Active,
        ]);

        $stats = app(EscalationEngine::class)->processPendingSteps($this->company->id);

        $this->assertGreaterThanOrEqual(1, $stats['escalations']);
        $this->assertSame(ApprovalChainStepStatus::Escalated, $record->fresh()->status);
    }

    public function test_po_conversion_blocked_until_purchase_request_approved(): void
    {
        [$submitter, $approver, $request] = $this->purchaseRequestActors(totalAmount: 15000);
        $vendor = Vendor::factory()->create(['company_id' => $this->company->id]);

        PurchaseRequestService::submit($request, $submitter->id);

        $this->expectException(ValidationException::class);
        PurchaseRequestService::convertToPurchaseOrder($request->fresh(), $vendor->id, $submitter->id, 'PO-BLOCK-001');
    }

    public function test_po_conversion_allowed_after_purchase_request_chain_approved(): void
    {
        [$submitter, $approver, $request] = $this->purchaseRequestActors(totalAmount: 15000);
        $vendor = Vendor::factory()->create(['company_id' => $this->company->id]);

        PurchaseRequestService::submit($request, $submitter->id);
        PurchaseRequestService::approve($request->fresh(), $approver);

        $order = PurchaseRequestService::convertToPurchaseOrder(
            $request->fresh(),
            $vendor->id,
            $submitter->id,
            'PO-PR-OK-001',
        );

        $this->assertSame(PurchaseRequestStatus::ConvertedToPo, $request->fresh()->status);
        $this->assertNotNull($order->id);
    }

    public function test_purchase_request_approval_writes_audit_trail(): void
    {
        [$submitter, $approver, $request] = $this->purchaseRequestActors(totalAmount: 15000);

        PurchaseRequestService::submit($request, $submitter->id);
        PurchaseRequestService::approve($request->fresh(), $approver, 'Approved for Q3 stock-up');

        $this->assertDatabaseHas('activity_logs', [
            'model_type' => PurchaseRequest::class,
            'model_id' => $request->id,
            'action' => 'purchase_request_submitted',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'model_type' => PurchaseRequest::class,
            'model_id' => $request->id,
            'action' => 'purchase_request_approved',
        ]);

        $run = app(ApprovalEnforcementEngine::class)->latestRun($request->fresh());
        $this->assertNotNull($run);
        $this->assertDatabaseHas('approval_chain_step_records', [
            'approval_chain_run_id' => $run->id,
            'status' => ApprovalChainStepStatus::Approved->value,
            'notes' => 'Approved for Q3 stock-up',
        ]);
    }

    /**
     * @return array{0: User, 1: User, 2: PurchaseRequest}
     */
    protected function purchaseRequestActors(float $totalAmount): array
    {
        $submitter = $this->companyUser('Storekeeper', [
            'procurement.requests.view',
            'procurement.requests.create',
            'procurement.requests.edit',
            'procurement.orders.create',
        ]);
        $approver = $this->companyUser('Company Admin', ['procurement.requests.approve']);

        ApprovalRule::query()->updateOrCreate(
            [
                'company_id' => $this->company->id,
                'branch_id' => $this->branch->id,
                'rule_type' => ApprovalRuleType::PurchaseRequestApproval->value,
            ],
            [
                'is_enabled' => true,
                'min_approvers' => 1,
                'settings_json' => [
                    'tiers' => [
                        ['threshold_amount' => 10000, 'approver_role' => 'Company Admin', 'approver_permission' => 'procurement.requests.approve'],
                    ],
                ],
            ],
        );

        $item = InventoryItem::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
        ]);

        $unitCost = round($totalAmount / 10, 2);

        $request = PurchaseRequest::query()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'request_number' => 'PR-GOV-'.uniqid(),
            'requested_by' => $submitter->id,
            'status' => PurchaseRequestStatus::Draft,
            'total_amount' => $totalAmount,
        ]);

        $request->items()->create([
            'inventory_item_id' => $item->id,
            'description' => $item->item_name,
            'quantity' => 10,
            'estimated_unit_cost' => $unitCost,
            'line_total' => $totalAmount,
        ]);

        return [$submitter, $approver, $request->fresh(['items'])];
    }

    /**
     * @param  list<string>  $permissions
     */
    protected function companyUser(string $roleName, array $permissions): User
    {
        $user = User::factory()->create([
            'company_id' => $this->company->id,
            'default_branch_id' => $this->branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $role = Role::findByName($roleName, 'web');
        $role->syncPermissions($permissions);
        $user->assignRole($roleName);

        return $user;
    }
}
