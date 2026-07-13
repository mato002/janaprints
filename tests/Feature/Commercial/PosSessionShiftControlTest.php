<?php

namespace Tests\Feature\Commercial;

use App\Enums\PosPaymentMethod;
use App\Enums\PosSaleStatus;
use App\Enums\PosSessionStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Pos\PosSale;
use App\Models\Pos\PosSession;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PosSessionShiftControlTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_open_session_with_terminal_and_float(): void
    {
        [$company, $branch, $user] = $this->tenantUser($this->cashierPermissions());

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->post(route('admin.commercial.pos.sessions.store'), [
                'cashier_id' => $user->id,
                'opening_float' => 5000,
                'opening_cash' => 5000,
                'terminal' => 'Counter A',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('pos_sessions', [
            'cashier_id' => $user->id,
            'opening_float' => 5000,
            'terminal' => 'Counter A',
            'status' => PosSessionStatus::Open->value,
        ]);
    }

    public function test_prevent_duplicate_open_session(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['commercial.pos.sessions.open']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->createOpenSession($company, $branch, $user);

        $this->actingAs($user)
            ->post(route('admin.commercial.pos.sessions.store'), [
                'cashier_id' => $user->id,
                'opening_float' => 100,
                'opening_cash' => 0,
            ])
            ->assertSessionHasErrors('cashier_id');
    }

    public function test_checkout_without_session_blocked(): void
    {
        [$company, $branch, $user] = $this->tenantUser([
            'pos.counter_sales.create',
            'pos.counter_sales.complete',
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->post(route('admin.commercial.pos.store'), [
                'action' => 'pay',
                'is_walk_in' => true,
                'payment_method' => PosPaymentMethod::Cash->value,
                'lines' => [[
                    'description' => 'No session item',
                    'quantity' => 1,
                    'unit_price' => 100,
                    'discount_amount' => 0,
                    'tax_amount' => 0,
                ]],
            ])
            ->assertSessionHasErrors('session');
    }

    public function test_sales_linked_to_session(): void
    {
        [$company, $branch, $user] = $this->tenantUser($this->cashierPermissions());
        $session = $this->createOpenSession($company, $branch, $user);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->post(route('admin.commercial.pos.store'), [
                'action' => 'pay',
                'is_walk_in' => true,
                'payment_method' => PosPaymentMethod::Cash->value,
                'lines' => [[
                    'description' => 'Linked item',
                    'quantity' => 1,
                    'unit_price' => 250,
                    'discount_amount' => 0,
                    'tax_amount' => 0,
                ]],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('pos_sales', [
            'pos_session_id' => $session->id,
            'status' => PosSaleStatus::Paid->value,
        ]);
    }

    public function test_close_session_within_tolerance(): void
    {
        [$company, $branch, $user] = $this->tenantUser($this->managerPermissions());
        $session = $this->createOpenSession($company, $branch, $user, openingFloat: 1000);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->post(route('admin.commercial.pos.sessions.close.store', $session), [
                'actual_cash' => 1050,
            ])
            ->assertRedirect();

        $session->refresh();
        $this->assertSame(PosSessionStatus::Closed, $session->status);
        $this->assertSame('50.00', $session->variance);
        $this->assertDatabaseHas('pos_cash_reconciliations', ['pos_session_id' => $session->id]);
    }

    public function test_variance_calculation_exceeds_tolerance(): void
    {
        [$company, $branch, $user] = $this->tenantUser($this->managerPermissions());
        $session = $this->createOpenSession($company, $branch, $user, openingFloat: 1000);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->post(route('admin.commercial.pos.sessions.close.store', $session), [
                'actual_cash' => 850,
            ])
            ->assertRedirect();

        $session->refresh();
        $this->assertSame(PosSessionStatus::PendingApproval, $session->status);
        $this->assertTrue($session->variance_requires_approval);
        $this->assertSame('-150.00', $session->variance);
        $this->assertDatabaseMissing('pos_cash_reconciliations', ['pos_session_id' => $session->id]);
    }

    public function test_variance_approval_closes_session(): void
    {
        [$company, $branch, $cashier] = $this->tenantUser($this->managerPermissions());
        $manager = $this->managerUser($company, $branch);
        $session = $this->createOpenSession($company, $branch, $cashier, openingFloat: 1000);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($cashier)
            ->post(route('admin.commercial.pos.sessions.close.store', $session), ['actual_cash' => 800])
            ->assertRedirect();

        $session->refresh();
        $this->assertSame(PosSessionStatus::PendingApproval, $session->status);

        $this->actingAs($manager)
            ->post(route('admin.commercial.pos.sessions.approve-variance', $session))
            ->assertRedirect();

        $session->refresh();
        $this->assertSame(PosSessionStatus::Closed, $session->status);
        $this->assertNotNull($session->variance_approved_at);
        $this->assertDatabaseHas('pos_cash_reconciliations', ['pos_session_id' => $session->id]);
    }

    public function test_permission_enforcement(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['pos.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.commercial.pos.sessions.index'))
            ->assertForbidden();
    }

    public function test_session_summary_generation(): void
    {
        [$company, $branch, $user] = $this->tenantUser($this->cashierPermissions());
        $session = $this->createOpenSession($company, $branch, $user);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.commercial.pos.sessions.summary', $session))
            ->assertOk()
            ->assertSee($session->session_number, false)
            ->assertSee('Session summary', false)
            ->assertSee('Print summary', false);
    }

    public function test_session_export(): void
    {
        [$company, $branch, $user] = $this->tenantUser($this->managerPermissions());
        $session = $this->createOpenSession($company, $branch, $user);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $response = $this->actingAs($user)
            ->get(route('admin.commercial.pos.sessions.export', $session));

        $response->assertOk()
            ->assertHeader('content-disposition')
            ->assertHeader('content-type', 'application/pdf');

        $this->assertStringStartsWith('%PDF', $response->streamedContent());
    }

    public function test_dashboard_shows_session_widget(): void
    {
        [$company, $branch, $user] = $this->tenantUser(array_merge($this->cashierPermissions(), ['pos.view']));
        $this->createOpenSession($company, $branch, $user, openingFloat: 5000);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.commercial.pos.dashboard'))
            ->assertOk()
            ->assertSee('Current session', false)
            ->assertSee('5,000.00', false);
    }

    /**
     * @return list<string>
     */
    protected function cashierPermissions(): array
    {
        return [
            'commercial.pos.sessions.view',
            'commercial.pos.sessions.open',
            'commercial.pos.sessions.close',
            'pos.counter_sales.view',
            'pos.counter_sales.create',
            'pos.counter_sales.complete',
        ];
    }

    /**
     * @return list<string>
     */
    protected function managerPermissions(): array
    {
        return [
            'commercial.pos.sessions.view',
            'commercial.pos.sessions.open',
            'commercial.pos.sessions.close',
            'commercial.pos.sessions.audit',
            'commercial.pos.sessions.audit',
            'commercial.pos.reconciliation.view',
        ];
    }

    protected function createOpenSession(
        Company $company,
        Branch $branch,
        User $user,
        float $openingFloat = 1000,
    ): PosSession {
        return PosSession::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'cashier_id' => $user->id,
            'session_number' => 'SES-SHIFT-'.uniqid(),
            'terminal' => 'Counter 1',
            'opening_float' => $openingFloat,
            'opening_cash' => 0,
            'status' => PosSessionStatus::Open,
            'opened_at' => now(),
            'opened_by' => $user->id,
        ]);
    }

    protected function managerUser(Company $company, Branch $branch): User
    {
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        Role::findByName('Viewer', 'web')->syncPermissions($this->managerPermissions());
        $user->assignRole('Viewer');

        return $user;
    }

    /**
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
        Role::findByName('Viewer', 'web')->syncPermissions($permissions);
        $user->assignRole('Viewer');

        return [$company, $branch, $user];
    }
}
