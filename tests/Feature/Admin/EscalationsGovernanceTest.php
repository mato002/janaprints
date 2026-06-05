<?php

namespace Tests\Feature\Admin;

use App\Enums\ApprovalChainMode;
use App\Enums\ApprovalChainRunStatus;
use App\Enums\ApprovalChainStatus;
use App\Enums\ApprovalChainStepStatus;
use App\Enums\ApprovalRuleType;
use App\Enums\EscalationEventType;
use App\Enums\EscalationMethod;
use App\Enums\EscalationRuleStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Governance\ApprovalChain;
use App\Models\Governance\ApprovalChainRun;
use App\Models\Governance\ApprovalChainStep;
use App\Models\Governance\ApprovalChainStepRecord;
use App\Models\Governance\WorkflowEscalationEvent;
use App\Models\Governance\WorkflowEscalationRule;
use App\Models\SecurityAuditEvent;
use App\Models\User;
use App\Support\Governance\ApprovalChainEngine;
use App\Support\Governance\EscalationEngine;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\PlatformConfigurationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EscalationsGovernanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        $this->seed(PlatformConfigurationSeeder::class);
    }

    public function test_escalations_dashboard_is_accessible(): void
    {
        $user = $this->userWithPermissions(['governance.escalations.view']);
        $company = Company::query()->where('code', 'JANA')->firstOrFail();

        $this->actingAs($user)
            ->get(route('admin.governance.escalations.index', ['company_id' => $company->id]))
            ->assertOk()
            ->assertSee(__('Workflow Escalations'))
            ->assertSee(__('Purchase Order'))
            ->assertSee(__('Finance Director'));
    }

    public function test_admin_can_create_escalation_rule(): void
    {
        $actor = $this->userWithPermissions([
            'governance.escalations.view',
            'governance.escalations.manage',
        ]);
        $company = Company::query()->where('code', 'JANA')->firstOrFail();

        $this->actingAs($actor)
            ->post(route('admin.governance.escalations.store'), [
                'company_id' => $company->id,
                'name' => 'Quotation Reminder SLA',
                'workflow_key' => 'quotation',
                'waiting_hours' => 24,
                'escalation_target_role' => 'Company Admin',
                'escalation_method' => EscalationMethod::Reminder->value,
                'description' => 'Send reminder after 24 hours',
            ])
            ->assertRedirect(route('admin.governance.escalations.index', [
                'company_id' => $company->id,
                'branch_id' => Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail()->id,
            ]));

        $this->assertDatabaseHas('workflow_escalation_rules', [
            'company_id' => $company->id,
            'workflow_key' => 'quotation',
            'waiting_hours' => 24,
            'escalation_target_role' => 'Company Admin',
            'escalation_method' => EscalationMethod::Reminder->value,
        ]);

        $this->assertDatabaseHas('security_audit_events', [
            'action' => 'escalation.created',
            'module' => 'governance',
            'entity' => 'workflow_escalation_rule',
        ]);
    }

    public function test_reminder_is_sent_when_timeout_reached(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();

        WorkflowEscalationRule::query()
            ->where('company_id', $company->id)
            ->where('workflow_key', 'purchase_order')
            ->update(['status' => EscalationRuleStatus::Inactive]);

        $rule = WorkflowEscalationRule::query()->create([
            'company_id' => $company->id,
            'branch_id' => null,
            'name' => 'PO Reminder Test',
            'workflow_key' => 'purchase_order',
            'waiting_hours' => 1,
            'escalation_target_role' => 'Finance Director',
            'escalation_method' => EscalationMethod::Reminder,
            'status' => EscalationRuleStatus::Active,
        ]);

        $record = $this->createPendingStepRecord($company, $branch, ApprovalRuleType::ProcurementApproval, 'purchase_order');
        ApprovalChainStepRecord::query()->whereKey($record->id)->update(['created_at' => now()->subHours(2)]);
        $record->refresh();

        $stats = app(EscalationEngine::class)->processPendingSteps($company->id);

        $this->assertSame(1, $stats['reminders']);
        $record->refresh();
        $this->assertNotNull($record->reminder_sent_at);
        $this->assertSame($rule->id, $record->workflow_escalation_rule_id);

        $this->assertTrue(
            WorkflowEscalationEvent::query()
                ->where('workflow_escalation_rule_id', $rule->id)
                ->where('approval_chain_step_record_id', $record->id)
                ->where('event_type', EscalationEventType::ReminderSent)
                ->exists()
        );

        $this->assertTrue(
            SecurityAuditEvent::query()->where('action', 'escalation.reminder_sent')->exists()
        );
    }

    public function test_auto_escalation_routes_to_target(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();

        Role::findOrCreate('Operations Manager', 'web');

        WorkflowEscalationRule::query()
            ->where('company_id', $company->id)
            ->where('workflow_key', 'inventory_adjustment')
            ->update(['status' => EscalationRuleStatus::Inactive]);

        $rule = WorkflowEscalationRule::query()->create([
            'company_id' => $company->id,
            'branch_id' => null,
            'name' => 'Inventory Adjustment Escalation',
            'workflow_key' => 'inventory_adjustment',
            'waiting_hours' => 24,
            'escalation_target_role' => 'Operations Manager',
            'escalation_method' => EscalationMethod::AutoEscalate,
            'status' => EscalationRuleStatus::Active,
        ]);

        $record = $this->createPendingStepRecord($company, $branch, ApprovalRuleType::StockAdjustmentApproval, 'stock_adjustment');
        ApprovalChainStepRecord::query()->whereKey($record->id)->update(['created_at' => now()->subHours(25)]);
        $record->refresh();

        $stats = app(EscalationEngine::class)->processPendingSteps($company->id);

        $this->assertSame(1, $stats['escalations']);
        $record->refresh();
        $this->assertSame(ApprovalChainStepStatus::Escalated, $record->status);
        $this->assertSame('Operations Manager', $record->escalated_to_role);
        $this->assertNotNull($record->escalated_at);

        $operationsRole = Role::findOrCreate('Operations Manager', 'web');
        $targetUser = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $targetUser->assignRole($operationsRole);
        $this->assertTrue(app(ApprovalChainEngine::class)->canUserActOnStep($targetUser, $record));

        $this->assertTrue(
            WorkflowEscalationEvent::query()
                ->where('workflow_escalation_rule_id', $rule->id)
                ->where('event_type', EscalationEventType::Escalated)
                ->exists()
        );
    }

    public function test_timeout_not_processed_before_waiting_period(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();

        WorkflowEscalationRule::query()
            ->where('company_id', $company->id)
            ->where('workflow_key', 'purchase_order')
            ->update(['status' => EscalationRuleStatus::Inactive]);

        WorkflowEscalationRule::query()->create([
            'company_id' => $company->id,
            'branch_id' => null,
            'name' => 'PO 48h Rule',
            'workflow_key' => 'purchase_order',
            'waiting_hours' => 48,
            'escalation_target_role' => 'Finance Director',
            'escalation_method' => EscalationMethod::AutoEscalate,
            'status' => EscalationRuleStatus::Active,
        ]);

        $record = $this->createPendingStepRecord($company, $branch, ApprovalRuleType::ProcurementApproval, 'purchase_order');
        ApprovalChainStepRecord::query()->whereKey($record->id)->update(['created_at' => now()->subHours(12)]);
        $record->refresh();

        $stats = app(EscalationEngine::class)->processPendingSteps($company->id);

        $this->assertSame(0, $stats['reminders']);
        $this->assertSame(0, $stats['escalations']);
        $record->refresh();
        $this->assertSame(ApprovalChainStepStatus::Pending, $record->status);
        $this->assertNull($record->escalated_at);
        $this->assertNull($record->reminder_sent_at);
    }

    public function test_forbidden_without_manage_permission(): void
    {
        $user = $this->userWithPermissions(['governance.escalations.view']);
        $company = Company::query()->where('code', 'JANA')->firstOrFail();

        $this->actingAs($user)
            ->get(route('admin.governance.escalations.create', ['company_id' => $company->id]))
            ->assertForbidden();

        $this->actingAs($user)
            ->post(route('admin.governance.escalations.store'), [
                'company_id' => $company->id,
                'name' => 'Blocked Rule',
                'workflow_key' => 'quotation',
                'waiting_hours' => 24,
                'escalation_target_role' => 'Company Admin',
                'escalation_method' => EscalationMethod::Reminder->value,
            ])
            ->assertForbidden();
    }

    protected function createPendingStepRecord(
        Company $company,
        Branch $branch,
        ApprovalRuleType $ruleType,
        string $documentType,
    ): ApprovalChainStepRecord {
        $chain = ApprovalChain::query()->create([
            'company_id' => $company->id,
            'branch_id' => null,
            'name' => 'Test Chain '.$ruleType->value,
            'module' => 'procurement',
            'document_type' => $documentType,
            'approval_rule_type' => $ruleType,
            'approval_mode' => ApprovalChainMode::Sequential,
            'status' => ApprovalChainStatus::Active,
        ]);

        $step = ApprovalChainStep::query()->create([
            'approval_chain_id' => $chain->id,
            'step_number' => 1,
            'approver_role' => 'Branch Manager',
            'is_required' => true,
        ]);

        $run = ApprovalChainRun::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'approval_chain_id' => $chain->id,
            'approval_rule_type' => $ruleType,
            'subject_type' => 'test.subject',
            'subject_id' => 1,
            'status' => ApprovalChainRunStatus::Pending,
            'started_at' => now(),
        ]);

        return ApprovalChainStepRecord::query()->create([
            'approval_chain_run_id' => $run->id,
            'approval_chain_step_id' => $step->id,
            'step_number' => 1,
            'status' => ApprovalChainStepStatus::Pending,
        ]);
    }

    /**
     * @param  list<string>  $permissions
     */
    protected function userWithPermissions(array $permissions): User
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();

        return $this->makeCompanyUser($company, $branch, 'Escalation Tester', $permissions);
    }

    /**
     * @param  list<string>  $permissions
     */
    protected function makeCompanyUser(Company $company, Branch $branch, string $roleLabel, array $permissions): User
    {
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $role = Role::create(['name' => $roleLabel.' '.uniqid(), 'guard_name' => 'web']);
        $role->syncPermissions($permissions);
        $user->assignRole($role);

        return $user;
    }
}
