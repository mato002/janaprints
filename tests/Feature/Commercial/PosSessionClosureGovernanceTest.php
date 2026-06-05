<?php

namespace Tests\Feature\Commercial;

use App\Enums\PosRefundMethod;
use App\Enums\PosReturnStatus;
use App\Enums\PosReturnType;
use App\Enums\PosSaleStatus;
use App\Enums\PosSessionStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Pos\PosReturn;
use App\Models\Pos\PosSale;
use App\Models\Pos\PosSession;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PosSessionClosureGovernanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_close_blocked_with_held_sale(): void
    {
        [$company, $branch, $user] = $this->tenantUser([
            'commercial.pos.sessions.view',
            'commercial.pos.sessions.close',
        ]);

        $session = $this->openSession($company, $branch, $user);

        PosSale::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'cashier_id' => $user->id,
            'pos_session_id' => $session->id,
            'sale_number' => 'POS-HELD-0001',
            'sale_date' => today(),
            'subtotal' => 100,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 100,
            'amount_paid' => 0,
            'balance_due' => 100,
            'status' => PosSaleStatus::Held,
            'is_walk_in' => true,
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->post(route('admin.commercial.pos.sessions.close.store', $session), [
                'actual_cash' => 1000,
            ])
            ->assertSessionHasErrors('held_sales');

        $session->refresh();
        $this->assertSame(PosSessionStatus::Open, $session->status);
    }

    public function test_close_blocked_with_draft(): void
    {
        [$company, $branch, $user] = $this->tenantUser([
            'commercial.pos.sessions.view',
            'commercial.pos.sessions.close',
        ]);

        $session = $this->openSession($company, $branch, $user);

        PosSale::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'cashier_id' => $user->id,
            'pos_session_id' => $session->id,
            'sale_number' => 'POS-DRAFT-0001',
            'sale_date' => today(),
            'subtotal' => 50,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 50,
            'amount_paid' => 0,
            'balance_due' => 50,
            'status' => PosSaleStatus::Draft,
            'is_walk_in' => true,
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->post(route('admin.commercial.pos.sessions.close.store', $session), [
                'actual_cash' => 1000,
            ])
            ->assertSessionHasErrors('draft_sales');

        $session->refresh();
        $this->assertSame(PosSessionStatus::Open, $session->status);
    }

    public function test_close_allowed_after_resolution(): void
    {
        [$company, $branch, $user] = $this->tenantUser([
            'commercial.pos.sessions.view',
            'commercial.pos.sessions.close',
        ]);

        $session = $this->openSession($company, $branch, $user);

        $held = PosSale::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'cashier_id' => $user->id,
            'pos_session_id' => $session->id,
            'sale_number' => 'POS-RESOLVE-0001',
            'sale_date' => today(),
            'subtotal' => 200,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 200,
            'amount_paid' => 0,
            'balance_due' => 200,
            'status' => PosSaleStatus::Held,
            'is_walk_in' => true,
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->post(route('admin.commercial.pos.sessions.close.store', $session), [
                'actual_cash' => 1200,
            ])
            ->assertSessionHasErrors('held_sales');

        $held->update([
            'status' => PosSaleStatus::Cancelled,
            'balance_due' => 0,
        ]);

        $this->actingAs($user)
            ->post(route('admin.commercial.pos.sessions.close.store', $session), [
                'actual_cash' => 1000,
            ])
            ->assertRedirect(route('admin.commercial.pos.sessions.show', $session));

        $session->refresh();
        $this->assertSame(PosSessionStatus::Closed, $session->status);
    }

    public function test_close_blocked_with_unapproved_return(): void
    {
        [$company, $branch, $user] = $this->tenantUser([
            'commercial.pos.sessions.view',
            'commercial.pos.sessions.close',
        ]);

        $session = $this->openSession($company, $branch, $user);

        $sale = PosSale::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'cashier_id' => $user->id,
            'pos_session_id' => $session->id,
            'sale_number' => 'POS-PAID-0001',
            'sale_date' => today(),
            'subtotal' => 100,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 100,
            'amount_paid' => 100,
            'balance_due' => 0,
            'status' => PosSaleStatus::Paid,
            'is_walk_in' => true,
        ]);

        PosReturn::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'pos_sale_id' => $sale->id,
            'pos_session_id' => $session->id,
            'created_by' => $user->id,
            'return_number' => 'RET-TEST-0001',
            'return_type' => PosReturnType::PartialReturn,
            'status' => PosReturnStatus::Pending,
            'refund_method' => PosRefundMethod::Cash,
            'subtotal' => 50,
            'tax_amount' => 0,
            'refund_amount' => 50,
            'is_full_return' => false,
            'reason' => 'Test',
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->post(route('admin.commercial.pos.sessions.close.store', $session), [
                'actual_cash' => 1000,
            ])
            ->assertSessionHasErrors('unapproved_returns');
    }

    public function test_close_form_shows_checklist(): void
    {
        [$company, $branch, $user] = $this->tenantUser([
            'commercial.pos.sessions.view',
            'commercial.pos.sessions.close',
        ]);

        $session = $this->openSession($company, $branch, $user);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.commercial.pos.sessions.close', $session))
            ->assertOk()
            ->assertSee('Session Closure Checklist', false)
            ->assertSee('No Held Sales', false)
            ->assertSee('No Draft Sales', false);
    }

    protected function openSession(Company $company, Branch $branch, User $user): PosSession
    {
        return PosSession::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'cashier_id' => $user->id,
            'session_number' => 'SES-GOV-'.uniqid(),
            'opening_float' => 1000,
            'opening_cash' => 0,
            'status' => PosSessionStatus::Open,
            'opened_at' => now(),
            'opened_by' => $user->id,
        ]);
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
