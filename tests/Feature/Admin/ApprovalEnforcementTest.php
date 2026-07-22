<?php

namespace Tests\Feature\Admin;

use App\Enums\ApprovalChainRunStatus;
use App\Enums\ApprovalChainStepStatus;
use App\Enums\ApprovalRuleType;
use App\Enums\DelegationStatus;
use App\Enums\EscalationMethod;
use App\Enums\EscalationRuleStatus;
use App\Enums\PurchaseOrderStatus;
use App\Enums\QuotationStatus;
use App\Enums\StockAdjustmentDirection;
use App\Enums\StockAdjustmentStatus;
use App\Models\Accounting\AccountingPeriod;
use App\Models\Accounting\GlAccount;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Governance\ApprovalChainRun;
use App\Models\Governance\ApprovalChainStepRecord;
use App\Models\Governance\WorkflowEscalationRule;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\StockAdjustment;
use App\Models\Inventory\Warehouse;
use App\Models\Platform\ApprovalDelegation;
use App\Models\Platform\ApprovalRule;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\Vendor;
use App\Models\Sales\Quotation;
use App\Models\User;
use App\Support\Accounting\JournalPostingService;
use App\Support\Governance\ApprovalEnforcementEngine;
use App\Support\Governance\EscalationEngine;
use App\Support\Procurement\PurchaseOrderService;
use App\Support\StockAdjustmentService;
use App\Support\TenantContext;
use Database\Seeders\GlAccountTypeSeeder;
use Database\Seeders\InventoryFoundationSeeder;
use Database\Seeders\JanaPrintsAccountingPeriodsSeeder;
use Database\Seeders\JanaPrintsChartOfAccountsSeeder;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\PlatformConfigurationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ApprovalEnforcementTest extends TestCase
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
        $this->seed(GlAccountTypeSeeder::class);
        $this->seed(JanaPrintsChartOfAccountsSeeder::class);
        $this->seed(JanaPrintsAccountingPeriodsSeeder::class);

        $this->company = Company::query()->where('code', 'JANA')->firstOrFail();
        $this->branch = Branch::query()->where('company_id', $this->company->id)->where('code', 'HQ')->firstOrFail();

        session(['active_company_id' => $this->company->id, 'active_branch_id' => $this->branch->id]);
        app()->instance(TenantContext::class, new TenantContext($this->company, $this->branch, false));
    }

    public function test_inventory_adjustment_blocks_posting_without_approval(): void
    {
        [$submitter, $approver, $adjustment] = $this->stockAdjustmentActors(totalValue: 2000);

        $this->expectException(ValidationException::class);
        StockAdjustmentService::post($adjustment, $submitter->id);
    }

    public function test_procurement_purchase_order_requires_approval_before_send(): void
    {
        $preparer = $this->companyUser('Company Admin', ['procurement.orders.create', 'procurement.orders.edit', 'procurement.orders.approve']);
        $vendor = Vendor::factory()->create(['company_id' => $this->company->id]);

        $order = PurchaseOrder::query()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'vendor_id' => $vendor->id,
            'po_number' => 'PO-ENF-'.uniqid(),
            'order_date' => now()->toDateString(),
            'status' => PurchaseOrderStatus::Draft,
            'subtotal' => 60000,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => 60000,
            'prepared_by' => $preparer->id,
        ]);

        PurchaseOrderService::submit($order, $preparer->id);
        $order->refresh();

        $this->assertSame(PurchaseOrderStatus::PendingApproval, $order->status);
        $this->assertNotNull(app(ApprovalEnforcementEngine::class)->latestRun($order));

        $this->expectException(ValidationException::class);
        PurchaseOrderService::assertCanSend($order);
    }

    public function test_procurement_purchase_order_send_after_approval(): void
    {
        $actor = $this->companyUser('Company Admin', ['procurement.orders.create', 'procurement.orders.edit', 'procurement.orders.approve']);
        $vendor = Vendor::factory()->create(['company_id' => $this->company->id]);

        $order = PurchaseOrder::query()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'vendor_id' => $vendor->id,
            'po_number' => 'PO-ENF-'.uniqid(),
            'order_date' => now()->toDateString(),
            'status' => PurchaseOrderStatus::Draft,
            'subtotal' => 60000,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => 60000,
            'prepared_by' => $actor->id,
        ]);

        PurchaseOrderService::submit($order, $actor->id);

        $engine = app(ApprovalEnforcementEngine::class);
        $attempts = 0;
        while (! $engine->hasApprovedChain($order->fresh()) && $attempts < 5) {
            PurchaseOrderService::approve($order->fresh(), $actor);
            $attempts++;
        }

        PurchaseOrderService::assertCanSend($order->fresh());
        $this->assertTrue(app(ApprovalEnforcementEngine::class)->hasApprovedChain($order->fresh()));
    }

    public function test_finance_journal_blocks_posting_until_chain_approved(): void
    {
        $user = $this->companyUser('Company Admin', ['accounting.journals.create', 'accounting.journals.post']);
        $period = AccountingPeriod::query()->where('company_id', $this->company->id)->where('is_current', true)->firstOrFail();
        $cash = GlAccount::query()->where('company_id', $this->company->id)->where('code', '1110')->firstOrFail();
        $revenue = GlAccount::query()->where('company_id', $this->company->id)->where('code', '4110')->firstOrFail();

        $journal = app(JournalPostingService::class)->createDraft([
            'accounting_period_id' => $period->id,
            'branch_id' => $this->branch->id,
            'journal_date' => $period->start_date->toDateString(),
            'description' => 'High-value manual journal',
        ], [
            ['gl_account_id' => $cash->id, 'debit' => 75000, 'credit' => 0],
            ['gl_account_id' => $revenue->id, 'debit' => 0, 'credit' => 75000],
        ], $user->id);

        try {
            app(JournalPostingService::class)->post($journal, $user->id);
            $this->fail('Expected approval gate before posting high-value journal.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('approval', $exception->errors());
        }

        $run = app(ApprovalEnforcementEngine::class)->latestRun($journal);
        $this->assertNotNull($run);
        $this->assertSame(ApprovalChainRunStatus::Pending, $run->status);

        app(ApprovalEnforcementEngine::class)->recordApproval($journal, $user);
        app(JournalPostingService::class)->post($journal->fresh(), $user->id);

        $this->assertTrue($journal->fresh()->status->value === 'posted');
    }

    public function test_discount_approval_submits_quotation_for_review(): void
    {
        $user = $this->companyUser('Sales', ['quotations.edit', 'quotations.view']);
        $customer = Customer::factory()->create(['company_id' => $this->company->id, 'branch_id' => $this->branch->id]);

        $quotation = Quotation::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $customer->id,
            'status' => QuotationStatus::Draft,
            'subtotal' => 1000,
            'discount_amount' => 150,
            'tax_amount' => 0,
            'total_amount' => 850,
        ]);

        $this->actingAs($user)
            ->post(route('admin.quotations.submit-approval', $quotation))
            ->assertRedirect();

        $quotation->refresh();
        $this->assertSame(QuotationStatus::PendingApproval, $quotation->status);
        $this->assertNotNull(app(ApprovalEnforcementEngine::class)->latestRun($quotation));
    }

    public function test_procurement_rejection_marks_chain_rejected(): void
    {
        $actor = $this->companyUser('Company Admin', ['procurement.orders.create', 'procurement.orders.edit', 'procurement.orders.approve']);
        $vendor = Vendor::factory()->create(['company_id' => $this->company->id]);

        $order = PurchaseOrder::query()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'vendor_id' => $vendor->id,
            'po_number' => 'PO-REJ-'.uniqid(),
            'order_date' => now()->toDateString(),
            'status' => PurchaseOrderStatus::Draft,
            'subtotal' => 60000,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => 60000,
            'prepared_by' => $actor->id,
        ]);

        PurchaseOrderService::submit($order, $actor->id);
        PurchaseOrderService::reject($order->fresh(), $actor, 'Budget freeze');

        $order->refresh();
        $run = app(ApprovalEnforcementEngine::class)->latestRun($order);

        $this->assertSame(PurchaseOrderStatus::Rejected, $order->status);
        $this->assertSame(ApprovalChainRunStatus::Rejected, $run->status);
    }

    public function test_delegate_can_complete_inventory_approval_chain_step(): void
    {
        [$submitter, $approver, $adjustment] = $this->stockAdjustmentActors(totalValue: 2000);

        $delegate = User::factory()->create([
            'company_id' => $this->company->id,
            'default_branch_id' => $this->branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        Role::findByName('Storekeeper', 'web')->syncPermissions(['inventory.view', 'inventory.adjust']);
        $delegate->assignRole('Storekeeper');

        ApprovalDelegation::query()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'delegator_user_id' => $approver->id,
            'delegate_user_id' => $delegate->id,
            'modules' => ['inventory'],
            'approval_types' => [ApprovalRuleType::StockAdjustmentApproval->value],
            'reason' => 'annual_leave',
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addWeek()->toDateString(),
            'status' => DelegationStatus::Active,
            'created_by_user_id' => $approver->id,
        ]);

        StockAdjustmentService::submit($adjustment->fresh(), $submitter->id);
        app(ApprovalEnforcementEngine::class)->recordApproval($adjustment->fresh(), $delegate, 'Delegated approval');

        $run = app(ApprovalEnforcementEngine::class)->latestRun($adjustment->fresh());
        $this->assertSame(ApprovalChainRunStatus::Approved, $run->status);
    }

    public function test_escalation_marks_overdue_chain_step_as_escalated(): void
    {
        [$submitter, $approver, $adjustment] = $this->stockAdjustmentActors(totalValue: 2000);
        StockAdjustmentService::submit($adjustment->fresh(), $submitter->id);

        $run = app(ApprovalEnforcementEngine::class)->latestRun($adjustment);
        $record = ApprovalChainStepRecord::query()->where('approval_chain_run_id', $run->id)->firstOrFail();
        ApprovalChainStepRecord::query()->whereKey($record->id)->update(['created_at' => now()->subHours(25)]);
        $record->refresh();

        Role::findOrCreate('Operations Manager', 'web');

        WorkflowEscalationRule::query()
            ->where('company_id', $this->company->id)
            ->where('workflow_key', 'inventory_adjustment')
            ->update(['status' => EscalationRuleStatus::Inactive]);

        WorkflowEscalationRule::query()->create([
            'company_id' => $this->company->id,
            'branch_id' => null,
            'name' => 'Inventory approval escalation',
            'workflow_key' => 'inventory_adjustment',
            'waiting_hours' => 24,
            'escalation_target_role' => 'Operations Manager',
            'escalation_method' => EscalationMethod::AutoEscalate,
            'status' => EscalationRuleStatus::Active,
        ]);

        $stats = app(EscalationEngine::class)->processPendingSteps($this->company->id);

        $this->assertGreaterThanOrEqual(1, $stats['escalations']);
        $this->assertSame(
            ApprovalChainStepStatus::Escalated,
            $record->fresh()->status,
        );
    }

    /**
     * @return array{0: User, 1: User, 2: StockAdjustment}
     */
    protected function stockAdjustmentActors(float $totalValue): array
    {
        $this->seed(InventoryFoundationSeeder::class);

        $submitter = $this->companyUser('Storekeeper', ['inventory.view', 'inventory.adjust', 'inventory.receive']);
        $approver = User::factory()->create([
            'company_id' => $this->company->id,
            'default_branch_id' => $this->branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        Role::findByName('Storekeeper', 'web')->syncPermissions(['inventory.view', 'inventory.adjust']);
        $approver->assignRole('Storekeeper');

        ApprovalRule::query()->updateOrCreate(
            [
                'company_id' => $this->company->id,
                'branch_id' => $this->branch->id,
                'rule_type' => ApprovalRuleType::StockAdjustmentApproval->value,
            ],
            [
                'is_enabled' => true,
                'min_approvers' => 1,
                'settings_json' => [
                    'tiers' => [
                        ['threshold_amount' => 500, 'approver_role' => 'Storekeeper', 'approver_permission' => 'inventory.adjust'],
                    ],
                ],
            ],
        );

        $warehouse = Warehouse::query()->where('company_id', $this->company->id)->where('branch_id', $this->branch->id)->firstOrFail();
        $item = InventoryItem::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'inventory_category_id' => \App\Models\Inventory\InventoryCategory::query()->where('company_id', $this->company->id)->value('id'),
            'unit_of_measure_id' => \App\Models\Inventory\UnitOfMeasure::query()->where('company_id', $this->company->id)->value('id'),
            'sku' => 'ENF-'.uniqid(),
            'standard_cost' => $totalValue / 100,
        ]);

        $adjustment = StockAdjustment::query()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'warehouse_id' => $warehouse->id,
            'adjustment_number' => 'SA-ENF-'.uniqid(),
            'adjustment_date' => now()->toDateString(),
            'status' => StockAdjustmentStatus::Draft,
            'reason' => 'Enforcement test',
            'adjusted_by' => $submitter->id,
        ]);

        $adjustment->items()->create([
            'inventory_item_id' => $item->id,
            'direction' => StockAdjustmentDirection::Increase,
            'quantity' => 100,
            'unit_cost' => $totalValue / 100,
        ]);

        return [$submitter, $approver, $adjustment->fresh(['items'])];
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
