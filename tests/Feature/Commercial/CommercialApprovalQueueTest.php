<?php

namespace Tests\Feature\Commercial;

use App\Enums\QuotationStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Sales\Quotation;
use App\Models\User;
use App\Support\Sales\SalesDeskViews;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CommercialApprovalQueueTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_queue_loads_and_shows_pending_quotation_without_mutation_on_get(): void
    {
        [$company, $branch, $user] = $this->tenantUser([
            'commercial.approvals.view', 'quotations.view', 'crm.customers.create', 'sales_orders.create',
        ]);

        $customer = Customer::factory()->create(['company_id' => $company->id, 'branch_id' => $branch->id]);

        $quotation = Quotation::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'prepared_by' => $user->id,
            'status' => QuotationStatus::PendingApproval,
            'total_amount' => 1500,
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $response = $this->actingAs($user)->get(SalesDeskViews::approvalsUrl(['tab' => 'pending']));

        $response->assertOk();
        $response->assertSee($quotation->quotation_number, false);
        $response->assertSee(__('Needs attention'), false);
        $response->assertSee(__('Pending'), false);
        $response->assertDontSee(__('Pending Quotations'), false);

        $quotation->refresh();
        $this->assertEquals(QuotationStatus::PendingApproval, $quotation->status);
    }

    public function test_approvals_search_finds_document_number(): void
    {
        [$company, $branch, $user] = $this->tenantUser([
            'commercial.approvals.view', 'quotations.view', 'crm.customers.create', 'sales_orders.create',
        ]);

        $customer = Customer::factory()->create(['company_id' => $company->id, 'branch_id' => $branch->id]);

        $match = Quotation::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'prepared_by' => $user->id,
            'quotation_number' => 'JANA-HQ-QUOTE-2026-000011',
            'status' => QuotationStatus::PendingApproval,
        ]);

        $other = Quotation::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'prepared_by' => $user->id,
            'quotation_number' => 'JANA-HQ-QUOTE-2026-000099',
            'status' => QuotationStatus::PendingApproval,
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $response = $this->actingAs($user)->get(SalesDeskViews::approvalsUrl([
            'tab' => 'pending',
            'q' => '000011',
        ]));

        $response->assertOk();
        $response->assertSee($match->quotation_number, false);
        $response->assertDontSee($other->quotation_number, false);
    }

    public function test_legacy_commercial_approvals_route_redirects_to_desk(): void
    {
        [$company, $branch, $user] = $this->tenantUser([
            'commercial.approvals.view', 'crm.customers.create', 'sales_orders.create',
        ]);
        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.commercial.approvals.index'))
            ->assertRedirect(SalesDeskViews::approvalsUrl());
    }

    public function test_queue_requires_permission(): void
    {
        [$company, $branch, $user] = $this->tenantUser([
            'quotations.view', 'crm.customers.create', 'sales_orders.create',
        ]);
        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)->get(SalesDeskViews::approvalsUrl())->assertForbidden();
    }

    /**
     * @param  list<string>  $permissions
     * @return array{0: Company, 1: Branch, 2: User}
     */
    protected function tenantUser(array $permissions): array
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $role = Role::findByName('Sales', 'web');
        $role->syncPermissions($permissions);
        $user->assignRole('Sales');

        return [$company, $branch, $user];
    }
}
