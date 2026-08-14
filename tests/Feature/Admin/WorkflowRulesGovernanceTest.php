<?php

namespace Tests\Feature\Admin;

use App\Enums\NotificationType;
use App\Enums\QuotationStatus;
use App\Enums\WorkflowRuleActionType;
use App\Enums\WorkflowRuleExecutionStatus;
use App\Enums\WorkflowRuleStatus;
use App\Enums\WorkflowRuleTrigger;
use App\Models\Branch;
use App\Models\Communications\ErpNotification;
use App\Models\Company;
use App\Models\Governance\WorkflowRule;
use App\Models\Governance\WorkflowRuleAction;
use App\Models\Governance\WorkflowRuleExecution;
use App\Models\Production\ProductionJobCard;
use App\Models\Sales\Quotation;
use App\Models\User;
use App\Support\Governance\WorkflowRuleEngine;
use App\Support\Governance\WorkflowRulesService;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\PlatformConfigurationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WorkflowRulesGovernanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        $this->seed(PlatformConfigurationSeeder::class);
    }

    public function test_dashboard_renders_seeded_workflow_rules(): void
    {
        $admin = $this->workflowAdmin();
        $company = Company::query()->where('code', 'JANA')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.governance.workflow-rules.index', ['company_id' => $company->id]))
            ->assertOk()
            ->assertSee(__('Workflow Rules'))
            ->assertSee(__('Quotation Approved → Sales Order'));
    }

    public function test_trigger_detection_resolves_active_rules_for_entity(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $quotation = Quotation::factory()->create([
            'company_id' => $company->id,
            'status' => QuotationStatus::Accepted,
            'total_amount' => 25000,
        ]);

        $rules = app(WorkflowRulesService::class)->resolveRules(
            $company->id,
            null,
            'quotation',
            WorkflowRuleTrigger::Approved,
        );

        $this->assertNotEmpty($rules);
        $this->assertTrue($rules->every(fn (WorkflowRule $rule) => $rule->status === WorkflowRuleStatus::Active));
        $this->assertSame('quotation', $rules->first()->entity_type);
    }

    public function test_condition_evaluation_filters_rules_by_amount(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $quotation = Quotation::factory()->create([
            'company_id' => $company->id,
            'total_amount' => 5000,
        ]);

        $engine = app(WorkflowRuleEngine::class);

        $this->assertTrue($engine->matchesConditions([
            ['field' => 'total_amount', 'operator' => 'gte', 'value' => '1000'],
        ], $quotation));

        $this->assertFalse($engine->matchesConditions([
            ['field' => 'total_amount', 'operator' => 'gte', 'value' => '10000'],
        ], $quotation));
    }

    public function test_action_execution_sends_notification_for_completed_job_card(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();
        $recipient = $this->makeCompanyUser($company, $branch, 'Sales Notifier', ['production.view']);

        $rule = WorkflowRule::query()->create([
            'company_id' => $company->id,
            'branch_id' => null,
            'name' => 'Test Job Completed Notify',
            'module' => 'production',
            'entity_type' => 'production_job_card',
            'trigger' => WorkflowRuleTrigger::Completed,
            'status' => WorkflowRuleStatus::Active,
        ]);

        WorkflowRuleAction::query()->create([
            'workflow_rule_id' => $rule->id,
            'sort_order' => 1,
            'action_type' => WorkflowRuleActionType::SendNotification,
            'config_json' => [
                'recipient_user_id' => $recipient->id,
                'notification_type' => NotificationType::ProductionCompleted->value,
                'title' => 'Production completed',
                'body' => 'Job card completed.',
            ],
        ]);

        $jobCard = ProductionJobCard::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
        ]);

        $executions = app(WorkflowRulesService::class)->dispatch(
            WorkflowRuleTrigger::Completed,
            $jobCard,
            $recipient,
        );

        $this->assertTrue($executions->contains(
            fn (WorkflowRuleExecution $execution) => $execution->workflow_rule_id === $rule->id
                && $execution->status === WorkflowRuleExecutionStatus::Completed,
        ));

        $this->assertTrue(
            ErpNotification::query()
                ->where('recipient_user_id', $recipient->id)
                ->where('type', NotificationType::ProductionCompleted)
                ->exists()
        );
    }

    public function test_permission_enforcement_blocks_view_without_rights(): void
    {
        $user = User::factory()->create([
            'company_id' => 1,
            'default_branch_id' => 1,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $user->assignRole('Designer');

        $this->actingAs($user)
            ->get(route('admin.governance.workflow-rules.index'))
            ->assertForbidden();
    }

    public function test_admin_can_create_and_activate_rule(): void
    {
        $admin = $this->workflowAdmin();
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.governance.workflow-rules.store'), [
                'company_id' => $company->id,
                'name' => 'Invoice Overdue Reminder',
                'entity_type' => 'customer_invoice',
                'trigger' => WorkflowRuleTrigger::Closed->value,
                'conditions' => [
                    ['field' => 'status', 'operator' => 'equals', 'value' => 'overdue'],
                ],
                'actions' => [
                    [
                        'action_type' => WorkflowRuleActionType::SendEmail->value,
                        'config' => [
                            'recipient_email' => 'finance@janaprints.local',
                            'subject' => 'Invoice overdue',
                            'body' => 'Please send payment reminder.',
                        ],
                    ],
                ],
            ])
            ->assertRedirect(route('admin.governance.workflow-rules.index', [
                'company_id' => $company->id,
                'branch_id' => $branch->id,
            ]));

        $rule = WorkflowRule::query()->where('name', 'Invoice Overdue Reminder')->firstOrFail();
        $this->assertSame(WorkflowRuleStatus::Draft, $rule->status);
        $this->assertCount(1, $rule->actions);

        $this->actingAs($admin)
            ->patch(route('admin.governance.workflow-rules.activate', $rule))
            ->assertRedirect();

        $this->assertSame(WorkflowRuleStatus::Active, $rule->fresh()->status);
    }

    public function test_workflow_governance_section_links_to_workflow_rules(): void
    {
        $admin = $this->workflowAdmin();

        $this->actingAs($admin)
            ->get(route('admin.workspaces.administration.catalog', ['section' => 'operations']))
            ->assertOk()
            ->assertSee(__('Workflow Rules'))
            ->assertSee(route('admin.workspaces.administration.section', [
                'section' => 'operations',
                'tab' => 'workflow-rules',
            ]), false);
    }

    protected function workflowAdmin(): User
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();

        return $this->makeCompanyUser($company, $branch, 'Workflow Admin', [
            'governance.workflow.view',
            'governance.workflow.create',
            'governance.workflow.manage',
        ]);
    }

    /**
     * @param  list<string>  $permissions
     */
    protected function makeCompanyUser(Company $company, Branch $branch, string $name, array $permissions): User
    {
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'name' => $name,
            'email' => strtolower(str_replace(' ', '.', $name)).'@janaprints.test',
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $role = Role::findOrCreate($name.' Role', 'web');

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $role->syncPermissions($permissions);
        $user->assignRole($role);

        return $user;
    }
}
