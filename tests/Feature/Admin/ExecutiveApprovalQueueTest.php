<?php

namespace Tests\Feature\Admin;

use App\Enums\QuotationStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Sales\Quotation;
use App\Models\User;
use App\Support\Dashboard\ExecutiveApprovalQueueService;
use App\Support\Dashboard\ExecutiveDashboardPresenter;
use App\Support\TenantContext;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ExecutiveApprovalQueueTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected Branch $branch;

    protected User $ceo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);

        $this->company = Company::query()->where('code', 'JANA')->firstOrFail();
        $this->branch = Branch::query()->where('company_id', $this->company->id)->where('code', 'HQ')->firstOrFail();
        $this->ceo = User::factory()->create([
            'company_id' => $this->company->id,
            'default_branch_id' => $this->branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $this->ceo->assignRole('Company Admin');

        app()->instance(TenantContext::class, new TenantContext($this->company, $this->branch, false));
        session(['active_company_id' => $this->company->id, 'active_branch_id' => $this->branch->id]);
    }

    public function test_queue_aggregates_pending_quotation(): void
    {
        $quotation = $this->pendingQuotation();

        $payload = app(ExecutiveApprovalQueueService::class)->build($this->ceo);

        $this->assertTrue($payload['visible']);
        $this->assertSame(1, $payload['summary']['waiting']);
        $this->assertTrue(
            collect($payload['items'])->contains(
                fn (array $row) => $row['kind'] === 'quotation' && $row['document'] === $quotation->quotation_number,
            ),
        );
    }

    public function test_full_queue_page_loads_for_authorized_user(): void
    {
        $quotation = $this->pendingQuotation();

        $response = $this->actingAs($this->ceo)->get(route('admin.executive.approvals.index'));

        $response->assertOk();
        $response->assertSee($quotation->quotation_number, false);
        $response->assertSee(__('Executive Approval Queue'), false);
    }

    public function test_dashboard_surfaces_approval_attention_widgets(): void
    {
        $this->pendingQuotation();

        $this->actingAs($this->ceo);
        $dashboard = app(ExecutiveDashboardPresenter::class)->build();

        $this->assertTrue($dashboard['approvals']['visible']);
        $this->assertSame(1, $dashboard['approvals']['summary']['waiting']);

        $labels = collect($dashboard['attention'])->pluck('label')->all();
        $this->assertContains(__('Approvals Waiting'), $labels);
        $this->assertContains(__('Critical Approvals'), $labels);
        $this->assertContains(__('Aging Approvals'), $labels);
    }

    public function test_approve_action_processes_quotation(): void
    {
        $quotation = $this->pendingQuotation();

        $response = $this->actingAs($this->ceo)->post(
            route('admin.executive.approvals.approve', ['kind' => 'quotation', 'subjectId' => $quotation->id]),
        );

        $response->assertRedirect();
        $quotation->refresh();
        $this->assertEquals(QuotationStatus::Approved, $quotation->status);
    }

    public function test_reject_action_returns_quotation_to_draft(): void
    {
        $quotation = $this->pendingQuotation();

        $response = $this->actingAs($this->ceo)->post(
            route('admin.executive.approvals.reject', ['kind' => 'quotation', 'subjectId' => $quotation->id]),
            ['reason' => 'Executive review rejected.'],
        );

        $response->assertRedirect();
        $quotation->refresh();
        $this->assertEquals(QuotationStatus::Draft, $quotation->status);
    }

    public function test_delegate_redirects_to_delegation_workspace(): void
    {
        $quotation = $this->pendingQuotation();

        $response = $this->actingAs($this->ceo)->get(
            route('admin.executive.approvals.delegate', ['kind' => 'quotation', 'subjectId' => $quotation->id]),
        );

        $response->assertRedirect(route('admin.governance.delegations.create', [
            'context_kind' => 'quotation',
            'context_subject_id' => $quotation->id,
        ]));
    }

    public function test_escalation_requires_pending_chain(): void
    {
        $quotation = $this->pendingQuotation();

        $response = $this->actingAs($this->ceo)->post(
            route('admin.executive.approvals.escalate', ['kind' => 'quotation', 'subjectId' => $quotation->id]),
        );

        $response->assertSessionHasErrors('escalation');
    }

    public function test_queue_requires_visibility_permission(): void
    {
        $viewer = User::factory()->create([
            'company_id' => $this->company->id,
            'default_branch_id' => $this->branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $role = Role::findByName('Viewer', 'web');
        $role->syncPermissions(['quotations.view']);
        $viewer->assignRole('Viewer');

        $this->pendingQuotation();

        $this->actingAs($viewer)->get(route('admin.executive.approvals.index'))->assertForbidden();

        $payload = app(ExecutiveApprovalQueueService::class)->build($viewer);
        $this->assertFalse($payload['visible']);
    }

    protected function pendingQuotation(): Quotation
    {
        $customer = Customer::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
        ]);

        return Quotation::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $customer->id,
            'status' => QuotationStatus::PendingApproval,
            'total_amount' => 125000,
            'prepared_by' => $this->ceo->id,
        ]);
    }
}
