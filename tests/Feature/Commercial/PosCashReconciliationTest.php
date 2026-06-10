<?php

namespace Tests\Feature\Commercial;

use App\Enums\PosPaymentMethod;
use App\Enums\PosReconciliationStatus;
use App\Enums\PosSaleStatus;
use App\Enums\PosSessionStatus;
use App\Enums\PosVarianceType;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Pos\PosCashReconciliation;
use App\Models\Pos\PosPayment;
use App\Models\Pos\PosSale;
use App\Models\Pos\PosSession;
use App\Models\User;
use App\Support\Commercial\PosCashReconciliationService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PosCashReconciliationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_balanced_session_reconciliation(): void
    {
        [$company, $branch, $cashier] = $this->tenantUser([
            'commercial.pos.sessions.close',
            'commercial.pos.reconciliation.view',
            'commercial.pos.reconciliation.create',
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $session = $this->closedSession($company, $branch, $cashier, expected: 1500, actual: 1500);

        $reconciliation = app(PosCashReconciliationService::class)->createFromSession($session, $cashier->id);

        $this->assertSame(PosVarianceType::Balanced, $reconciliation->variance_type);
        $this->assertSame(PosReconciliationStatus::Pending, $reconciliation->status);

        $this->actingAs($cashier)
            ->post(route('admin.commercial.pos.reconciliation.submit', $reconciliation))
            ->assertRedirect();

        $reconciliation->refresh();
        $this->assertSame(PosReconciliationStatus::Balanced, $reconciliation->status);
    }

    public function test_short_cash_variance(): void
    {
        [$company, $branch, $cashier] = $this->tenantUser([
            'commercial.pos.reconciliation.view',
            'commercial.pos.reconciliation.create',
        ]);

        $session = $this->closedSession($company, $branch, $cashier, expected: 2000, actual: 1850);
        $reconciliation = app(PosCashReconciliationService::class)->createFromSession($session, $cashier->id);

        $this->assertSame(PosVarianceType::Short, $reconciliation->variance_type);
        $this->assertSame('-150.00', $reconciliation->variance);

        $this->actingAs($cashier)
            ->post(route('admin.commercial.pos.reconciliation.submit', $reconciliation));

        $reconciliation->refresh();
        $this->assertSame(PosReconciliationStatus::VarianceFound, $reconciliation->status);
    }

    public function test_excess_cash_variance(): void
    {
        [$company, $branch, $cashier] = $this->tenantUser([
            'commercial.pos.reconciliation.view',
            'commercial.pos.reconciliation.create',
        ]);

        $session = $this->closedSession($company, $branch, $cashier, expected: 1000, actual: 1150);
        $reconciliation = app(PosCashReconciliationService::class)->createFromSession($session, $cashier->id);

        $this->assertSame(PosVarianceType::Over, $reconciliation->variance_type);
        $this->assertSame('150.00', $reconciliation->variance);
    }

    public function test_approval_workflow(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);

        $supervisor = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $supervisor->givePermissionTo([
            'commercial.pos.reconciliation.view',
            'commercial.pos.reconciliation.approve',
        ]);

        [$company, $branch, $cashier] = $this->tenantUser([
            'commercial.pos.reconciliation.view',
            'commercial.pos.reconciliation.create',
        ], $company, $branch);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $session = $this->closedSession($company, $branch, $cashier, expected: 500, actual: 500);
        $reconciliation = app(PosCashReconciliationService::class)->createFromSession($session, $cashier->id);

        $this->actingAs($cashier)
            ->post(route('admin.commercial.pos.reconciliation.submit', $reconciliation))
            ->assertRedirect();

        $this->actingAs($supervisor)
            ->post(route('admin.commercial.pos.reconciliation.review', $reconciliation), [
                'review_notes' => 'Counts verified.',
            ])
            ->assertRedirect();

        $this->actingAs($supervisor)
            ->post(route('admin.commercial.pos.reconciliation.approve', $reconciliation), [
                'approval_notes' => 'Approved.',
            ])
            ->assertRedirect();

        $reconciliation->refresh();
        $this->assertSame(PosReconciliationStatus::Approved, $reconciliation->status);
        $this->assertDatabaseHas('pos_cash_reconciliation_logs', [
            'pos_cash_reconciliation_id' => $reconciliation->id,
            'action' => 'approved',
        ]);
    }

    public function test_permission_enforcement(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['pos.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.commercial.pos.reconciliation.index'))
            ->assertForbidden();
    }

    public function test_branch_isolation(): void
    {
        $company = Company::factory()->create();
        $branchA = Branch::factory()->create(['company_id' => $company->id]);
        $branchB = Branch::factory()->create(['company_id' => $company->id]);

        $cashierB = User::factory()->create(['company_id' => $company->id, 'default_branch_id' => $branchB->id]);
        $sessionB = $this->closedSession($company, $branchB, $cashierB, expected: 100, actual: 100);
        $reconciliation = app(PosCashReconciliationService::class)->createFromSession($sessionB, $cashierB->id);

        $userA = $this->tenantUser([
            'commercial.pos.reconciliation.view',
        ], $company, $branchA)[2];

        session(['active_company_id' => $company->id, 'active_branch_id' => $branchA->id]);

        $this->actingAs($userA)
            ->get(route('admin.commercial.pos.reconciliation.show', $reconciliation))
            ->assertNotFound();
    }

    public function test_dashboard_loads(): void
    {
        [$company, $branch, $user] = $this->tenantUser([
            'commercial.pos.reconciliation.view',
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.commercial.pos.reconciliation.index'))
            ->assertOk()
            ->assertSee(__('Cash Reconciliation'), false)
            ->assertSee(__('Pending Reviews'), false);
    }

    protected function closedSession(Company $company, Branch $branch, User $cashier, float $expected, float $actual): PosSession
    {
        $session = PosSession::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'cashier_id' => $cashier->id,
            'session_number' => 'SES-REC-'.fake()->unique()->numerify('####'),
            'opening_float' => 500,
            'opening_cash' => 0,
            'expected_cash' => $expected,
            'actual_cash' => $actual,
            'variance' => $actual - $expected,
            'status' => PosSessionStatus::Closed,
            'opened_at' => now()->subHours(4),
            'closed_at' => now(),
            'opened_by' => $cashier->id,
            'closed_by' => $cashier->id,
        ]);

        $sale = PosSale::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'cashier_id' => $cashier->id,
            'pos_session_id' => $session->id,
            'sale_number' => 'POS-REC-'.fake()->unique()->numerify('####'),
            'sale_date' => now()->toDateString(),
            'subtotal' => 1000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 1000,
            'amount_paid' => 1000,
            'balance_due' => 0,
            'status' => PosSaleStatus::Paid,
            'is_walk_in' => true,
        ]);

        PosPayment::query()->create([
            'pos_sale_id' => $sale->id,
            'payment_method' => PosPaymentMethod::Cash,
            'amount' => 1000,
        ]);

        return $session->fresh();
    }

    /**
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
        Role::findByName('Viewer', 'web')->syncPermissions($permissions);
        $user->assignRole('Viewer');

        return [$company, $branch, $user];
    }
}
