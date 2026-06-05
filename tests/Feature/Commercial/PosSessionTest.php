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

class PosSessionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_open_session(): void
    {
        [$company, $branch, $user] = $this->tenantUser([
            'commercial.pos.sessions.view',
            'commercial.pos.sessions.open',
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->post(route('admin.commercial.pos.sessions.store'), [
                'cashier_id' => $user->id,
                'opening_float' => 1000,
                'opening_cash' => 500,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('pos_sessions', [
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'cashier_id' => $user->id,
            'status' => PosSessionStatus::Open->value,
        ]);
    }

    public function test_prevent_duplicate_open_session(): void
    {
        [$company, $branch, $user] = $this->tenantUser([
            'commercial.pos.sessions.open',
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        PosSession::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'cashier_id' => $user->id,
            'session_number' => 'SES-TEST-0001',
            'opening_float' => 100,
            'opening_cash' => 100,
            'status' => PosSessionStatus::Open,
            'opened_at' => now(),
            'opened_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->post(route('admin.commercial.pos.sessions.store'), [
                'cashier_id' => $user->id,
                'opening_float' => 200,
                'opening_cash' => 200,
            ])
            ->assertSessionHasErrors('cashier_id');
    }

    public function test_attach_sale_to_session(): void
    {
        [$company, $branch, $user] = $this->tenantUser([
            'pos.view', 'pos.create',
            'commercial.pos.sessions.open',
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $session = PosSession::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'cashier_id' => $user->id,
            'session_number' => 'SES-TEST-0002',
            'opening_float' => 1000,
            'opening_cash' => 0,
            'status' => PosSessionStatus::Open,
            'opened_at' => now(),
            'opened_by' => $user->id,
        ]);

        $this->actingAs($user)->post(route('admin.commercial.pos.store'), [
            'action' => 'pay',
            'is_walk_in' => true,
            'payment_method' => PosPaymentMethod::Cash->value,
            'lines' => [[
                'description' => 'Walk-in item',
                'quantity' => 1,
                'unit_price' => 250,
                'discount_amount' => 0,
                'tax_amount' => 0,
            ]],
        ])->assertRedirect();

        $this->assertDatabaseHas('pos_sales', [
            'company_id' => $company->id,
            'pos_session_id' => $session->id,
            'status' => PosSaleStatus::Paid->value,
        ]);
    }

    public function test_close_session(): void
    {
        [$company, $branch, $user] = $this->tenantUser([
            'commercial.pos.sessions.view',
            'commercial.pos.sessions.close',
            'commercial.pos.reconciliation.view',
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $session = PosSession::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'cashier_id' => $user->id,
            'session_number' => 'SES-TEST-0003',
            'opening_float' => 1000,
            'opening_cash' => 0,
            'status' => PosSessionStatus::Open,
            'opened_at' => now(),
            'opened_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->post(route('admin.commercial.pos.sessions.close.store', $session), [
                'actual_cash' => 1000,
            ])
            ->assertRedirect();

        $session->refresh();
        $this->assertSame(PosSessionStatus::Closed, $session->status);
        $this->assertSame('1000.00', $session->actual_cash);
        $this->assertDatabaseHas('pos_cash_reconciliations', [
            'pos_session_id' => $session->id,
            'status' => 'pending',
        ]);
    }

    public function test_block_sales_after_close(): void
    {
        [$company, $branch, $user] = $this->tenantUser([
            'pos.view', 'pos.create',
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        PosSession::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'cashier_id' => $user->id,
            'session_number' => 'SES-TEST-0004',
            'opening_float' => 500,
            'opening_cash' => 0,
            'status' => PosSessionStatus::Closed,
            'opened_at' => now()->subHours(2),
            'closed_at' => now()->subHour(),
            'opened_by' => $user->id,
            'closed_by' => $user->id,
        ]);

        $this->actingAs($user)->post(route('admin.commercial.pos.store'), [
            'action' => 'save',
            'is_walk_in' => true,
            'lines' => [[
                'description' => 'Blocked item',
                'quantity' => 1,
                'unit_price' => 100,
                'discount_amount' => 0,
                'tax_amount' => 0,
            ]],
        ])->assertSessionHasErrors('session');
    }

    public function test_branch_scoping(): void
    {
        $company = Company::factory()->create();
        $branchA = Branch::factory()->create(['company_id' => $company->id]);
        $branchB = Branch::factory()->create(['company_id' => $company->id]);

        $sessionB = PosSession::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branchB->id,
            'cashier_id' => User::factory()->create(['company_id' => $company->id])->id,
            'session_number' => 'SES-TEST-0005',
            'opening_float' => 100,
            'opening_cash' => 0,
            'status' => PosSessionStatus::Open,
            'opened_at' => now(),
            'opened_by' => User::factory()->create(['company_id' => $company->id])->id,
        ]);

        $user = $this->tenantUser([
            'commercial.pos.sessions.view',
        ], $company, $branchA)[2];

        session(['active_company_id' => $company->id, 'active_branch_id' => $branchA->id]);

        $this->actingAs($user)
            ->get(route('admin.commercial.pos.sessions.show', $sessionB))
            ->assertNotFound();
    }

    public function test_permission_enforcement(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['pos.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.commercial.pos.sessions.index'))
            ->assertForbidden();
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
