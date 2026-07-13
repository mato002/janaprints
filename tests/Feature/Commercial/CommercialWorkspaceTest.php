<?php

namespace Tests\Feature\Commercial;

use App\Enums\ActivityStatus;
use App\Enums\ActivityType;
use App\Enums\PosSaleStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Crm\CustomerActivity;
use App\Models\Inventory\InventoryItem;
use App\Models\Pos\PosSale;
use App\Models\User;
use App\Support\Commercial\PosSaleCalculator;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CommercialWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_activities_index_requires_permission(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['crm.customers.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.commercial.activities.index'))
            ->assertForbidden();
    }

    public function test_activity_creation_works_for_permitted_user(): void
    {
        [$company, $branch, $user] = $this->tenantUser([
            'commercial.activities.view', 'commercial.activities.create',
        ]);

        $customer = Customer::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)->post(route('admin.commercial.activities.store'), [
            'customer_id' => $customer->id,
            'activity_type' => ActivityType::Call->value,
            'status' => ActivityStatus::Completed->value,
            'subject' => 'Follow-up call',
            'activity_at' => now()->format('Y-m-d H:i:s'),
        ])->assertRedirect();

        $this->assertDatabaseHas('customer_activities', [
            'company_id' => $company->id,
            'customer_id' => $customer->id,
            'subject' => 'Follow-up call',
        ]);
    }

    public function test_activity_is_tenant_scoped(): void
    {
        $companyA = Company::factory()->create();
        $branchA = Branch::factory()->create(['company_id' => $companyA->id]);
        $companyB = Company::factory()->create();
        $branchB = Branch::factory()->create(['company_id' => $companyB->id]);

        $activityB = CustomerActivity::query()->create([
            'company_id' => $companyB->id,
            'branch_id' => $branchB->id,
            'user_id' => User::factory()->create(['company_id' => $companyB->id])->id,
            'activity_type' => ActivityType::Note,
            'status' => ActivityStatus::Completed,
            'subject' => 'Other tenant',
            'activity_at' => now(),
        ]);

        $user = $this->tenantUser([
            'commercial.activities.view',
        ], $companyA, $branchA)[2];

        session(['active_company_id' => $companyA->id, 'active_branch_id' => $branchA->id]);

        $this->actingAs($user)
            ->get(route('admin.commercial.activities.show', $activityB))
            ->assertNotFound();
    }

    public function test_pos_index_requires_permission(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['crm.customers.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.commercial.pos.dashboard'))
            ->assertForbidden();
    }

    public function test_pos_sale_can_be_created(): void
    {
        [$company, $branch, $user] = $this->tenantUser([
            'pos.view', 'pos.create',
            'commercial.pos.sessions.open',
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        \App\Models\Pos\PosSession::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'cashier_id' => $user->id,
            'session_number' => 'SES-TEST-WS-0001',
            'opening_float' => 0,
            'opening_cash' => 0,
            'status' => \App\Enums\PosSessionStatus::Open,
            'opened_at' => now(),
            'opened_by' => $user->id,
        ]);

        $this->actingAs($user)->post(route('admin.commercial.pos.store'), [
            'action' => 'save',
            'is_walk_in' => true,
            'lines' => [[
                'description' => 'Test product',
                'quantity' => 2,
                'unit_price' => 50,
                'discount_amount' => 0,
                'tax_amount' => 0,
            ]],
        ])->assertRedirect();

        $this->assertDatabaseHas('pos_sales', [
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'cashier_id' => $user->id,
            'status' => PosSaleStatus::Draft->value,
        ]);
    }

    public function test_pos_sale_totals_calculate_correctly(): void
    {
        $calculator = app(PosSaleCalculator::class);

        $totals = $calculator->totals([
            ['quantity' => 2, 'unit_price' => 100, 'discount_amount' => 10, 'tax_amount' => 5],
            ['quantity' => 1, 'unit_price' => 50, 'discount_amount' => 0, 'tax_amount' => 0],
        ], 5, 10);

        $this->assertSame('240.00', $totals['subtotal']);
        $this->assertSame('15.00', $totals['discount_amount']);
        $this->assertSame('15.00', $totals['tax_amount']);
        $this->assertSame('250.00', $totals['total_amount']);
    }

    public function test_pos_sale_is_tenant_scoped(): void
    {
        $companyA = Company::factory()->create();
        $branchA = Branch::factory()->create(['company_id' => $companyA->id]);
        $companyB = Company::factory()->create();
        $branchB = Branch::factory()->create(['company_id' => $companyB->id]);
        $cashier = User::factory()->create(['company_id' => $companyB->id]);

        $saleB = PosSale::query()->create([
            'company_id' => $companyB->id,
            'branch_id' => $branchB->id,
            'cashier_id' => $cashier->id,
            'sale_number' => 'POS-TEST-0001',
            'sale_date' => today(),
            'status' => PosSaleStatus::Paid,
        ]);

        $user = $this->tenantUser(['pos.view'], $companyA, $branchA)[2];

        session(['active_company_id' => $companyA->id, 'active_branch_id' => $branchA->id]);

        $this->actingAs($user)
            ->get(route('admin.commercial.pos.show', $saleB))
            ->assertNotFound();
    }

    public function test_crm_section_shows_activities_as_active(): void
    {
        [$company, $branch, $user] = $this->tenantUser([
            'commercial.activities.view',
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $response = $this->actingAs($user)->get(route('admin.workspaces.commercial.section', ['section' => 'crm']));

        $response->assertOk();
        $response->assertSee(__('Activities'), false);
        $response->assertSee(__('Active'), false);
        $response->assertSee(route('admin.commercial.activities.index'), false);
    }

    public function test_reports_section_links_sales_reports_and_keeps_other_tiles_coming_soon(): void
    {
        [$company, $branch, $user] = $this->tenantUser([
            'quotations.view',
            'commercial.reports.sales.view',
            'commercial.reports.quotations.view',
            'commercial.reports.sales_orders.view',
            'commercial.reports.customers.view',
            'commercial.reports.artwork.view',
            'commercial.reports.conversion.view',
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $response = $this->actingAs($user)
            ->get(route('admin.workspaces.commercial.section', ['section' => 'reports']));

        $response->assertOk();
        $response->assertSee(__('Sales Reports'), false);
        $response->assertSee(route('admin.commercial.reports.sales.index'), false);
        $response->assertSee(__('Quotation Reports'), false);
        $response->assertSee(route('admin.commercial.reports.quotations.index'), false);
        $response->assertSee(__('Sales Order Reports'), false);
        $response->assertSee(route('admin.commercial.reports.sales_orders.index'), false);
        $response->assertSee(__('Customer Reports'), false);
        $response->assertSee(route('admin.commercial.reports.customers.index'), false);
        $response->assertSee(__('Artwork Reports'), false);
        $response->assertSee(route('admin.commercial.reports.artwork.index'), false);
        $response->assertSee(__('Conversion Reports'), false);
        $response->assertSee(route('admin.commercial.reports.conversion.index'), false);
        $response->assertDontSee(__('Coming Soon'), false);
    }

    public function test_reports_section_renders_embedded_customer_reports_frame_src(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['commercial.reports.customers.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.workspaces.commercial.section', [
                'section' => 'reports',
                'tab' => 'customer-reports',
            ]))
            ->assertOk()
            ->assertSee(route('admin.commercial.reports.customers.index', ['embedded' => '1']), false)
            ->assertSee('module-workspace-content', false);
    }

    public function test_customer_reports_embedded_query_renders_workspace_frame(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['commercial.reports.customers.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.commercial.reports.customers.index', ['embedded' => '1']))
            ->assertOk()
            ->assertSee('id="module-workspace-content"', false);
    }

    public function test_conversion_reports_embedded_query_renders_workspace_frame(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['commercial.reports.conversion.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.commercial.reports.conversion.index', ['embedded' => '1']))
            ->assertOk()
            ->assertSee('id="module-workspace-content"', false);
    }

    public function test_user_without_pos_permission_cannot_see_pos_on_section(): void
    {
        [$company, $branch, $user] = $this->tenantUser([
            'commercial.activities.view', 'crm.customers.view',
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.workspaces.commercial.section', ['section' => 'point-of-sale']))
            ->assertForbidden();
    }

    public function test_customers_full_page_embedded_refresh_restores_crm_workspace(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['crm.customers.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.crm.customers.index', ['embedded' => '1']))
            ->assertRedirect()
            ->assertRedirectContains('/admin/workspaces/commercial/crm')
            ->assertRedirectContains('tab=customers');
    }

    public function test_customers_embedded_turbo_frame_request_stays_in_content_frame(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['crm.customers.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->withHeaders(['Turbo-Frame' => 'module-workspace-content'])
            ->get(route('admin.crm.customers.index', ['embedded' => '1']))
            ->assertOk()
            ->assertSee('id="module-workspace-content"', false)
            ->assertSee(__('Customers'));
    }

    /**
     * @param  list<string>  $permissions
     * @return array{0: Company, 1: Branch, 2: User}
     */
    protected function tenantUser(array $permissions, ?Company $company = null, ?Branch $branch = null): array
    {
        $company ??= Company::factory()->create();
        $branch ??= Branch::factory()->create(['company_id' => $company->id]);

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
