<?php

namespace Tests\Feature\Procurement;

use App\Enums\ApprovalChainRunStatus;
use App\Enums\ApprovalRuleType;
use App\Enums\PurchaseOrderStatus;
use App\Enums\PurchaseRequestStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Inventory\InventoryItem;
use App\Models\Platform\ApprovalRule;
use App\Models\Platform\SystemSetting;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\PurchaseRequest;
use App\Models\Procurement\Vendor;
use App\Models\User;
use App\Support\Governance\ApprovalEnforcementEngine;
use App\Support\Procurement\PurchaseOrderService;
use App\Support\Procurement\PurchaseRequestService;
use App\Support\TenantContext;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\PlatformConfigurationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProcurementApprovalConsistencyTest extends TestCase
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

    public function test_procurement_approval_dashboard_requires_permission(): void
    {
        $user = $this->companyUser('Storekeeper', ['procurement.requests.view']);

        $this->actingAs($user)
            ->get(route('admin.procurement.approvals.index'))
            ->assertForbidden();
    }

    public function test_procurement_approval_dashboard_lists_pending_chains(): void
    {
        [$submitter, , $request] = $this->purchaseRequestActors(totalAmount: 15000);
        PurchaseRequestService::submit($request, $submitter->id);

        $approver = $this->companyUser('Company Admin', ['procurement.approvals.view', 'procurement.requests.approve']);

        $this->actingAs($approver)
            ->get(route('admin.procurement.approvals.index'))
            ->assertOk()
            ->assertSee(__('Pending Procurement Approvals'), false)
            ->assertSee($request->fresh()->request_number, false);
    }

    public function test_purchase_request_governance_enforcement_matches_po_pattern(): void
    {
        [$submitter, $approver, $request] = $this->purchaseRequestActors(totalAmount: 15000);

        PurchaseRequestService::submit($request, $submitter->id);
        $this->assertSame(PurchaseRequestStatus::PendingApproval, $request->fresh()->status);
        $this->assertNotNull(app(ApprovalEnforcementEngine::class)->latestRun($request));

        PurchaseRequestService::approve($request->fresh(), $approver);
        $this->assertSame(PurchaseRequestStatus::Approved, $request->fresh()->status);
        $this->assertTrue(app(ApprovalEnforcementEngine::class)->hasApprovedChain($request->fresh()));
    }

    public function test_purchase_order_requires_chain_before_send(): void
    {
        $actor = $this->companyUser('Company Admin', ['procurement.orders.create', 'procurement.orders.edit', 'procurement.orders.approve']);
        $vendor = Vendor::factory()->create(['company_id' => $this->company->id]);

        $order = PurchaseOrder::query()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'vendor_id' => $vendor->id,
            'po_number' => 'PO-CONS-'.uniqid(),
            'order_date' => now()->toDateString(),
            'status' => PurchaseOrderStatus::Draft,
            'subtotal' => 60000,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => 60000,
            'prepared_by' => $actor->id,
        ]);

        PurchaseOrderService::submit($order, $actor->id);

        $this->expectException(ValidationException::class);
        PurchaseOrderService::assertCanSend($order->fresh());
    }

    public function test_segregation_blocks_requester_from_approving_purchase_request(): void
    {
        SystemSetting::query()->updateOrCreate(
            ['company_id' => $this->company->id, 'branch_id' => null, 'key' => 'procurement_enforce_requester_approver_separation'],
            ['value' => ['data' => true], 'value_type' => 'boolean'],
        );

        [$submitter, , $request] = $this->purchaseRequestActors(totalAmount: 15000);
        $submitter->givePermissionTo('procurement.requests.approve');

        PurchaseRequestService::submit($request, $submitter->id);

        $this->expectException(ValidationException::class);
        PurchaseRequestService::approve($request->fresh(), $submitter);
    }

    public function test_purchase_request_rejection_writes_audit_trail(): void
    {
        [$submitter, $approver, $request] = $this->purchaseRequestActors(totalAmount: 15000);

        PurchaseRequestService::submit($request, $submitter->id);
        PurchaseRequestService::reject($request->fresh(), $approver, 'Budget hold');

        $this->assertDatabaseHas('activity_logs', [
            'model_type' => PurchaseRequest::class,
            'model_id' => $request->id,
            'action' => 'purchase_request_rejected',
        ]);

        $run = app(ApprovalEnforcementEngine::class)->latestRun($request->fresh());
        $this->assertSame(ApprovalChainRunStatus::Rejected, $run->status);
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

        $request = PurchaseRequest::query()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'request_number' => 'PR-CONS-'.uniqid(),
            'requested_by' => $submitter->id,
            'status' => PurchaseRequestStatus::Draft,
            'total_amount' => $totalAmount,
        ]);

        $request->items()->create([
            'inventory_item_id' => $item->id,
            'description' => $item->item_name,
            'quantity' => 10,
            'estimated_unit_cost' => $totalAmount / 10,
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
