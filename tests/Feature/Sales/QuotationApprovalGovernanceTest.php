<?php

namespace Tests\Feature\Sales;

use App\Enums\ApprovalChainMode;
use App\Enums\ApprovalChainRunStatus;
use App\Enums\ApprovalChainStatus;
use App\Enums\ApprovalChainStepStatus;
use App\Enums\ApprovalRuleType;
use App\Enums\DelegationStatus;
use App\Enums\EscalationMethod;
use App\Enums\EscalationRuleStatus;
use App\Enums\QuotationStatus;
use App\Models\Branch;
use App\Models\Communications\ErpNotification;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Governance\ApprovalChain;
use App\Models\Governance\ApprovalChainStep;
use App\Models\Governance\ApprovalChainStepRecord;
use App\Models\Governance\WorkflowEscalationRule;
use App\Models\Platform\ApprovalDelegation;
use App\Models\Platform\ApprovalRule;
use App\Models\Sales\Quotation;
use App\Models\User;
use App\Support\Governance\ApprovalEnforcementEngine;
use App\Support\Governance\EscalationEngine;
use App\Support\Sales\QuotationApprovalService;
use App\Support\TenantContext;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\PlatformConfigurationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class QuotationApprovalGovernanceTest extends TestCase
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

    public function test_submit_without_approval_required_sends_directly(): void
    {
        [$preparer, $quotation] = $this->quotationActors(totalAmount: 1000);

        $this->actingAs($preparer)
            ->post(route('admin.quotations.submit-approval', $quotation))
            ->assertRedirect();

        $quotation->refresh();
        $this->assertSame(QuotationStatus::Sent, $quotation->status);
        $this->assertNull(app(ApprovalEnforcementEngine::class)->latestRun($quotation));
    }

    public function test_submit_with_approval_required_enters_pending_approval(): void
    {
        [$preparer, $quotation] = $this->quotationActors(totalAmount: 75000);

        $this->actingAs($preparer)
            ->post(route('admin.quotations.submit-approval', $quotation))
            ->assertRedirect();

        $quotation->refresh();
        $this->assertSame(QuotationStatus::PendingApproval, $quotation->status);
        $this->assertTrue(app(QuotationApprovalService::class)->requiresApproval($quotation));
    }

    public function test_send_blocked_from_pending_approval(): void
    {
        [$preparer, $quotation] = $this->quotationActors(
            totalAmount: 75000,
            extraPermissions: ['quotations.send'],
        );

        app(QuotationApprovalService::class)->submit($quotation, $preparer->id);

        $this->actingAs($preparer)
            ->post(route('admin.quotations.send', $quotation->fresh()))
            ->assertForbidden();

        $quotation->refresh();
        $this->assertSame(QuotationStatus::PendingApproval, $quotation->status);
    }

    public function test_approve_moves_to_approved_not_sent_until_send_action(): void
    {
        [$preparer, $quotation] = $this->quotationActors(totalAmount: 75000);
        $approver = $this->companyUser('Company Admin', ['quotations.approve', 'quotations.view']);

        app(QuotationApprovalService::class)->submit($quotation, $preparer->id);

        $this->actingAs($approver)
            ->post(route('admin.quotations.approve', $quotation->fresh()), [
                'approval_reason' => 'Within policy',
            ])
            ->assertRedirect();

        $quotation->refresh();
        $this->assertSame(QuotationStatus::Approved, $quotation->status);
        $this->assertNotNull($quotation->approved_at);
        $this->assertTrue(app(ApprovalEnforcementEngine::class)->hasApprovedChain($quotation));
    }

    public function test_send_only_allowed_after_approval_chain_completed(): void
    {
        [$preparer, $quotation] = $this->quotationActors(
            totalAmount: 75000,
            extraPermissions: ['quotations.send'],
        );
        $approver = $this->companyUser('Company Admin', ['quotations.approve', 'quotations.view']);

        app(QuotationApprovalService::class)->submit($quotation, $preparer->id);
        app(QuotationApprovalService::class)->approve($quotation->fresh(), $approver, 'Approved');

        $this->actingAs($preparer)
            ->post(route('admin.quotations.send', $quotation->fresh()))
            ->assertRedirect();

        $quotation->refresh();
        $this->assertSame(QuotationStatus::Sent, $quotation->status);
    }

    public function test_reject_approval_returns_to_draft_with_reason_and_audit(): void
    {
        [$preparer, $quotation] = $this->quotationActors(totalAmount: 75000);
        $approver = $this->companyUser('Company Admin', ['quotations.approve', 'quotations.view']);

        app(QuotationApprovalService::class)->submit($quotation, $preparer->id);

        $this->actingAs($approver)
            ->post(route('admin.quotations.reject-approval', $quotation->fresh()), [
                'rejection_reason' => 'Pricing too aggressive',
            ])
            ->assertRedirect();

        $quotation->refresh();
        $run = app(ApprovalEnforcementEngine::class)->latestRun($quotation);

        $this->assertSame(QuotationStatus::Draft, $quotation->status);
        $this->assertSame(ApprovalChainRunStatus::Rejected, $run->status);
        $this->assertSame(
            'Pricing too aggressive',
            $run->stepRecords()->where('status', ApprovalChainStepStatus::Rejected)->value('notes'),
        );
    }

    public function test_reject_approval_requires_reason(): void
    {
        [$preparer, $quotation] = $this->quotationActors(totalAmount: 75000);
        $approver = $this->companyUser('Company Admin', ['quotations.approve', 'quotations.view']);

        app(QuotationApprovalService::class)->submit($quotation, $preparer->id);

        $this->actingAs($approver)
            ->post(route('admin.quotations.reject-approval', $quotation->fresh()), [])
            ->assertSessionHasErrors('rejection_reason');
    }

    public function test_delegate_can_approve_pending_quotation(): void
    {
        [$preparer, $quotation] = $this->quotationActors(totalAmount: 75000);
        $approver = $this->companyUser('Company Admin', ['quotations.approve', 'quotations.view']);

        $delegate = User::factory()->create([
            'company_id' => $this->company->id,
            'default_branch_id' => $this->branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        Role::findByName('Sales', 'web')->syncPermissions(['quotations.view']);
        $delegate->assignRole('Sales');

        ApprovalDelegation::query()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'delegator_user_id' => $approver->id,
            'delegate_user_id' => $delegate->id,
            'modules' => ['sales'],
            'approval_types' => [ApprovalRuleType::QuotationApproval->value],
            'reason' => 'annual_leave',
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addWeek()->toDateString(),
            'status' => DelegationStatus::Active,
            'created_by_user_id' => $approver->id,
        ]);

        app(QuotationApprovalService::class)->submit($quotation, $preparer->id);

        $this->actingAs($delegate)
            ->post(route('admin.quotations.approve', $quotation->fresh()))
            ->assertRedirect();

        $quotation->refresh();
        $this->assertSame(QuotationStatus::Approved, $quotation->status);
    }

    public function test_escalation_marks_overdue_quotation_chain_step(): void
    {
        [$preparer, $quotation] = $this->quotationActors(totalAmount: 75000);

        app(QuotationApprovalService::class)->submit($quotation, $preparer->id);

        $run = app(ApprovalEnforcementEngine::class)->latestRun($quotation);
        $record = ApprovalChainStepRecord::query()->where('approval_chain_run_id', $run->id)->firstOrFail();
        ApprovalChainStepRecord::query()->whereKey($record->id)->update(['created_at' => now()->subHours(25)]);
        $record->refresh();

        Role::findOrCreate('Operations Manager', 'web');

        WorkflowEscalationRule::query()->create([
            'company_id' => $this->company->id,
            'branch_id' => null,
            'name' => 'Quotation approval escalation',
            'workflow_key' => 'quotation',
            'waiting_hours' => 24,
            'escalation_target_role' => 'Operations Manager',
            'escalation_method' => EscalationMethod::AutoEscalate,
            'status' => EscalationRuleStatus::Active,
        ]);

        $stats = app(EscalationEngine::class)->processPendingSteps($this->company->id);

        $this->assertGreaterThanOrEqual(1, $stats['escalations']);
        $this->assertSame(ApprovalChainStepStatus::Escalated, $record->fresh()->status);
    }

    public function test_approval_notifies_preparer_on_rejection(): void
    {
        [$preparer, $quotation] = $this->quotationActors(totalAmount: 75000);
        $approver = $this->companyUser('Company Admin', ['quotations.approve', 'quotations.view']);

        app(QuotationApprovalService::class)->submit($quotation, $preparer->id);

        $this->actingAs($approver)
            ->post(route('admin.quotations.reject-approval', $quotation->fresh()), [
                'rejection_reason' => 'Margin too low',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('notifications', [
            'company_id' => $this->company->id,
            'recipient_user_id' => $preparer->id,
            'type' => 'quotation_rejected',
            'subject_type' => Quotation::class,
            'subject_id' => $quotation->id,
        ]);
    }

    public function test_viewer_cannot_approve_or_reject(): void
    {
        [$preparer, $quotation] = $this->quotationActors(totalAmount: 75000, extraPermissions: []);
        $viewer = $this->companyUser('Sales', ['quotations.view']);

        app(QuotationApprovalService::class)->submit($quotation, $preparer->id);

        $this->actingAs($viewer)
            ->post(route('admin.quotations.approve', $quotation->fresh()))
            ->assertForbidden();

        $this->actingAs($viewer)
            ->post(route('admin.quotations.reject-approval', $quotation->fresh()), [
                'rejection_reason' => 'Should fail',
            ])
            ->assertForbidden();
    }

    public function test_show_displays_approval_panel_for_pending_quotation(): void
    {
        [$preparer, $quotation] = $this->quotationActors(totalAmount: 75000);

        app(QuotationApprovalService::class)->submit($quotation, $preparer->id);

        $this->actingAs($preparer)
            ->get(route('admin.quotations.show', $quotation->fresh()))
            ->assertOk()
            ->assertSee(__('Approval status'), false)
            ->assertSee(__('Approval history'), false)
            ->assertDontSee(__('Send'), false);
    }

    /**
     * @param  list<string>  $extraPermissions
     * @return array{0: User, 1: Quotation}
     */
    protected function quotationActors(float $totalAmount, array $extraPermissions = []): array
    {
        $permissions = array_unique(array_merge(
            ['quotations.view', 'quotations.create', 'quotations.edit'],
            $extraPermissions,
        ));

        $preparer = $this->companyUser('Sales', $permissions);
        $customer = Customer::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
        ]);

        $quotation = Quotation::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $customer->id,
            'prepared_by' => $preparer->id,
            'status' => QuotationStatus::Draft,
            'subtotal' => $totalAmount,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => $totalAmount,
        ]);

        if ($totalAmount >= 50000) {
            ApprovalRule::query()->updateOrCreate(
                [
                    'company_id' => $this->company->id,
                    'branch_id' => $this->branch->id,
                    'rule_type' => ApprovalRuleType::QuotationApproval->value,
                ],
                [
                    'is_enabled' => true,
                    'min_approvers' => 1,
                    'settings_json' => [
                        'tiers' => [
                            ['threshold_amount' => 50000, 'approver_role' => 'Company Admin', 'approver_permission' => 'quotations.approve'],
                        ],
                    ],
                ],
            );

            $this->seedQuotationApprovalChain();
        }

        return [$preparer, $quotation];
    }

    protected function seedQuotationApprovalChain(): void
    {
        $chain = ApprovalChain::query()->updateOrCreate(
            [
                'company_id' => $this->company->id,
                'branch_id' => $this->branch->id,
                'name' => 'Quotation Approval Chain',
            ],
            [
                'module' => 'sales',
                'document_type' => 'quotation',
                'approval_rule_type' => ApprovalRuleType::QuotationApproval,
                'approval_mode' => ApprovalChainMode::Sequential,
                'status' => ApprovalChainStatus::Active,
                'description' => 'Test quotation approval chain',
            ],
        );

        $chain->steps()->delete();
        ApprovalChainStep::query()->create([
            'approval_chain_id' => $chain->id,
            'step_number' => 1,
            'approver_role' => 'Company Admin',
            'is_required' => true,
        ]);
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
