<?php

namespace Tests\Feature\Commercial;

use App\Enums\PosPaymentMethod;
use App\Enums\PosSaleStatus;
use App\Enums\PosSessionStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Pos\PosPayment;
use App\Models\Pos\PosSale;
use App\Models\Pos\PosSession;
use App\Models\User;
use App\Support\Commercial\Reports\CommercialPosReportQueries;
use App\Support\Commercial\Reports\CommercialPosReportScope;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\Feature\Commercial\Concerns\GetsEmbeddedWorkspaceReports;
use Tests\TestCase;

class CommercialPosReportTest extends TestCase
{
    use GetsEmbeddedWorkspaceReports;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_pos_intelligence_requires_permission(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['pos.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->getEmbeddedReport($user, 'admin.commercial.pos.reports.index')
            ->assertForbidden();
    }

    public function test_pos_intelligence_index_loads(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['commercial.pos.reports.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->getEmbeddedReport($user, 'admin.commercial.pos.reports.index')
            ->assertOk()
            ->assertSee(__('POS Intelligence'), false)
            ->assertSee(__('Open Sessions'), false)
            ->assertSee(__('Sales By Cashier'), false);
    }

    public function test_report_accuracy_for_paid_sales(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['commercial.pos.reports.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $session = $this->createSession($company, $branch, $user);
        $this->createPaidSale($company, $branch, $user, $session, 500.00);

        $this->getEmbeddedReport($user, 'admin.commercial.pos.reports.index')
            ->assertOk()
            ->assertSee(__('Open Sessions'), false)
            ->assertSee('500.00', false)
            ->assertSee(e($user->name), false);
    }

    public function test_filter_accuracy_by_cashier(): void
    {
        [$company, $branch, $userA] = $this->tenantUser(['commercial.pos.reports.view']);
        $userB = User::factory()->create(['company_id' => $company->id, 'default_branch_id' => $branch->id]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $sessionA = $this->createSession($company, $branch, $userA);
        $sessionB = $this->createSession($company, $branch, $userB, 'SES-B-0001');
        $this->createPaidSale($company, $branch, $userA, $sessionA, 300.00, 'POS-A-0001');
        $this->createPaidSale($company, $branch, $userB, $sessionB, 900.00, 'POS-B-0001');

        $queries = app(CommercialPosReportQueries::class);
        $scope = new CommercialPosReportScope(
            companyId: $company->id,
            branchId: $branch->id,
            fromDate: now()->toDateString(),
            toDate: now()->toDateString(),
            cashierId: $userB->id,
        );

        $this->assertSame(1, $queries->todaySalesCount($scope));
        $this->assertSame(900.0, $queries->todaySalesValue($scope));

        $this->getEmbeddedReport($userA, 'admin.commercial.pos.reports.index', ['cashier_id' => $userB->id])
            ->assertOk()
            ->assertSee(e($userB->name), false)
            ->assertSee('900.00', false);
    }

    public function test_export_requires_permission(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['commercial.pos.reports.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->post(route('admin.commercial.pos.reports.export', ['format' => 'csv']))
            ->assertForbidden();
    }

    public function test_export_streams_file(): void
    {
        [$company, $branch, $user] = $this->tenantUser([
            'commercial.pos.reports.view',
            'commercial.pos.reports.export',
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $response = $this->actingAs($user)
            ->post(route('admin.commercial.pos.reports.export', ['format' => 'csv', 'tab' => 'sales_by_cashier']));

        $response->assertOk();
        $response->assertHeader('X-Erp-Export', 'direct');
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment', (string) $response->headers->get('Content-Disposition'));
    }

    public function test_branch_scoping_hides_other_branch_sales(): void
    {
        $company = Company::factory()->create();
        $branchA = Branch::factory()->create(['company_id' => $company->id]);
        $branchB = Branch::factory()->create(['company_id' => $company->id]);
        $userA = User::factory()->create(['company_id' => $company->id, 'default_branch_id' => $branchA->id]);
        $userB = User::factory()->create(['company_id' => $company->id, 'default_branch_id' => $branchB->id]);

        $role = Role::create(['name' => 'pos-scope-'.uniqid(), 'guard_name' => 'web']);
        $role->givePermissionTo(['commercial.pos.reports.view']);
        $userA->assignRole($role);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branchA->id]);

        $sessionB = $this->createSession($company, $branchB, $userB, 'SES-BR-B');
        $this->createPaidSale($company, $branchB, $userB, $sessionB, 750.00, 'POS-BR-B-001');

        $queries = app(CommercialPosReportQueries::class);
        $scopeA = new CommercialPosReportScope(
            companyId: $company->id,
            branchId: $branchA->id,
            fromDate: now()->toDateString(),
            toDate: now()->toDateString(),
        );

        $this->assertSame(0, $queries->todaySalesCount($scopeA));

        $this->getEmbeddedReport($userA, 'admin.commercial.pos.reports.index')
            ->assertOk()
            ->assertDontSee('POS-BR-B-001', false);
    }

    public function test_performance_validation_uses_aggregate_queries(): void
    {
        DB::enableQueryLog();

        [$company, $branch, $user] = $this->tenantUser(['commercial.pos.reports.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $session = $this->createSession($company, $branch, $user);
        foreach (range(1, 5) as $i) {
            $this->createPaidSale($company, $branch, $user, $session, 100.00 * $i, 'POS-PERF-'.str_pad((string) $i, 4, '0', STR_PAD_LEFT));
        }

        $queries = app(CommercialPosReportQueries::class);
        $scope = new CommercialPosReportScope(
            companyId: $company->id,
            branchId: $branch->id,
            fromDate: now()->toDateString(),
            toDate: now()->toDateString(),
        );

        DB::flushQueryLog();
        $queries->paginateSalesByCashier($scope);
        $queryCount = count(DB::getQueryLog());

        $this->assertLessThanOrEqual(5, $queryCount);
    }

    protected function createSession(Company $company, Branch $branch, User $cashier, string $number = 'SES-TEST-0001'): PosSession
    {
        return PosSession::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'cashier_id' => $cashier->id,
            'session_number' => $number,
            'opening_float' => 1000,
            'opening_cash' => 0,
            'status' => PosSessionStatus::Open,
            'opened_at' => now(),
            'opened_by' => $cashier->id,
        ]);
    }

    protected function createPaidSale(
        Company $company,
        Branch $branch,
        User $cashier,
        PosSession $session,
        float $total,
        string $saleNumber = 'POS-TEST-0001',
    ): PosSale {
        $sale = PosSale::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'cashier_id' => $cashier->id,
            'pos_session_id' => $session->id,
            'sale_number' => $saleNumber,
            'sale_date' => now()->toDateString(),
            'subtotal' => $total,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => $total,
            'amount_paid' => $total,
            'balance_due' => 0,
            'status' => PosSaleStatus::Paid,
            'is_walk_in' => true,
        ]);

        PosPayment::query()->create([
            'pos_sale_id' => $sale->id,
            'payment_method' => PosPaymentMethod::Cash,
            'amount' => $total,
        ]);

        return $sale;
    }

    /**
     * @return array{0: Company, 1: Branch, 2: User}
     */
    protected function tenantUser(array $permissions): array
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $user = User::factory()->create(['company_id' => $company->id, 'default_branch_id' => $branch->id]);

        $role = Role::create(['name' => 'test-role-'.uniqid(), 'guard_name' => 'web']);
        $role->givePermissionTo($permissions);
        $user->assignRole($role);

        return [$company, $branch, $user];
    }
}
