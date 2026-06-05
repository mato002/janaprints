<?php

namespace Tests\Feature\Commercial;

use App\Enums\PosPaymentMethod;
use App\Enums\PosSaleStatus;
use App\Enums\PosSessionStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Pos\PosSale;
use App\Models\Pos\PosSaleHold;
use App\Models\Pos\PosSession;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PosHeldSaleWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_create_hold(): void
    {
        [$company, $branch, $user] = $this->tenantUser([
            'pos.view', 'pos.create',
            'commercial.pos.sessions.open',
        ]);

        $this->openSession($company, $branch, $user);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)->post(route('admin.commercial.pos.store'), [
            'action' => 'hold',
            'is_walk_in' => true,
            'hold_label' => 'Counter 1',
            'lines' => [[
                'description' => 'Banner print',
                'quantity' => 2,
                'unit_price' => 150,
                'discount_amount' => 0,
                'tax_amount' => 0,
            ]],
        ])->assertRedirect(route('admin.commercial.pos.counter-sales'));

        $sale = PosSale::query()->where('company_id', $company->id)->first();
        $this->assertSame(PosSaleStatus::Held, $sale->status);
        $this->assertDatabaseHas('pos_sale_holds', [
            'pos_sale_id' => $sale->id,
            'cashier_id' => $user->id,
        ]);
    }

    public function test_resume_hold_loads_checkout(): void
    {
        [$company, $branch, $user] = $this->tenantUser([
            'pos.view', 'pos.create', 'pos.edit',
            'commercial.pos.sessions.open',
        ]);

        $sale = $this->createHeldSale($company, $branch, $user);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $response = $this->actingAs($user)
            ->get(route('admin.commercial.pos.resume', $sale));

        $response->assertOk()
            ->assertSee($sale->sale_number, false)
            ->assertSee('Complete sale', false);
        $response->assertSee('Banner print', false);
    }

    public function test_pay_hold_converts_to_paid_without_new_sale(): void
    {
        [$company, $branch, $user] = $this->tenantUser([
            'pos.view', 'pos.create', 'pos.edit',
            'commercial.pos.sessions.open',
        ]);

        $sale = $this->createHeldSale($company, $branch, $user);
        $saleId = $sale->id;

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)->post(route('admin.commercial.pos.pay', $sale), [
            'is_walk_in' => true,
            'payment_method' => PosPaymentMethod::Cash->value,
            'lines' => [[
                'description' => 'Banner print',
                'quantity' => 2,
                'unit_price' => 150,
                'discount_amount' => 0,
                'tax_amount' => 0,
            ]],
        ])->assertRedirect(route('admin.commercial.pos.receipt', $sale));

        $this->assertSame(1, PosSale::query()->where('company_id', $company->id)->count());

        $sale->refresh();
        $this->assertSame($saleId, $sale->id);
        $this->assertSame(PosSaleStatus::Paid, $sale->status);
        $this->assertSame('300.00', $sale->total_amount);
        $this->assertDatabaseMissing('pos_sale_holds', ['pos_sale_id' => $saleId]);
        $this->assertDatabaseHas('pos_payments', [
            'pos_sale_id' => $saleId,
            'payment_method' => PosPaymentMethod::Cash->value,
            'amount' => 300,
        ]);
    }

    public function test_prevent_resume_paid_sale(): void
    {
        [$company, $branch, $user] = $this->tenantUser([
            'pos.view', 'pos.edit',
        ]);

        $sale = PosSale::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'cashier_id' => $user->id,
            'sale_number' => 'POS-TEST-PAID-0001',
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

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.commercial.pos.resume', $sale))
            ->assertNotFound();

        $this->actingAs($user)
            ->post(route('admin.commercial.pos.pay', $sale), [
                'is_walk_in' => true,
                'payment_method' => PosPaymentMethod::Cash->value,
                'lines' => [[
                    'description' => 'Item',
                    'quantity' => 1,
                    'unit_price' => 100,
                    'discount_amount' => 0,
                    'tax_amount' => 0,
                ]],
            ])
            ->assertNotFound();
    }

    public function test_held_sales_queue_on_dashboard(): void
    {
        [$company, $branch, $user] = $this->tenantUser([
            'pos.view', 'pos.create', 'pos.edit',
            'commercial.pos.sessions.open',
        ]);

        $sale = $this->createHeldSale($company, $branch, $user);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.commercial.pos.dashboard'))
            ->assertOk()
            ->assertSee('Held sales queue', false)
            ->assertSee($sale->sale_number, false)
            ->assertSee('Resume', false);
    }

    protected function openSession(Company $company, Branch $branch, User $user): PosSession
    {
        return PosSession::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'cashier_id' => $user->id,
            'session_number' => 'SES-HOLD-'.uniqid(),
            'opening_float' => 0,
            'opening_cash' => 0,
            'status' => PosSessionStatus::Open,
            'opened_at' => now(),
            'opened_by' => $user->id,
        ]);
    }

    protected function createHeldSale(Company $company, Branch $branch, User $user): PosSale
    {
        $session = $this->openSession($company, $branch, $user);

        $sale = PosSale::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'cashier_id' => $user->id,
            'pos_session_id' => $session->id,
            'sale_number' => 'POS-TEST-HOLD-'.uniqid(),
            'sale_date' => today(),
            'subtotal' => 300,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 300,
            'amount_paid' => 0,
            'balance_due' => 300,
            'status' => PosSaleStatus::Held,
            'is_walk_in' => true,
        ]);

        $sale->items()->create([
            'description' => 'Banner print',
            'quantity' => 2,
            'unit_price' => 150,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'line_total' => 300,
        ]);

        PosSaleHold::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'cashier_id' => $user->id,
            'pos_sale_id' => $sale->id,
            'held_at' => now()->subMinutes(10),
        ]);

        return $sale->fresh(['items', 'hold']);
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
