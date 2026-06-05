<?php

namespace Tests\Feature\Commercial;

use App\Enums\PosPaymentMethod;
use App\Enums\PosSaleStatus;
use App\Enums\PosSessionStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Inventory\InventoryItem;
use App\Models\Pos\PosSale;
use App\Models\Pos\PosSaleHold;
use App\Models\Pos\PosSession;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PosCounterSalesUxTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_counter_sales_loads_without_active_session(): void
    {
        [$company, $branch, $user] = $this->tenantUser($this->permissions());

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.commercial.pos.counter-sales'))
            ->assertOk()
            ->assertSee('No active session', false)
            ->assertSee('Open session', false)
            ->assertSee('posCounterWorkstation', false);
    }

    public function test_open_session_modal_endpoint_works(): void
    {
        [$company, $branch, $user] = $this->tenantUser($this->permissions());

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->postJson(route('admin.commercial.pos.counter-sales.session.open'), [
                'cashier_id' => $user->id,
                'opening_float' => 5000,
                'opening_cash' => 0,
                'terminal' => 'Counter 1',
            ])
            ->assertOk()
            ->assertJsonPath('session.has_session', true);
    }

    public function test_session_state_updates_after_open(): void
    {
        [$company, $branch, $user] = $this->tenantUser($this->permissions());
        $this->openSession($company, $branch, $user);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->getJson(route('admin.commercial.pos.counter-sales.session'))
            ->assertOk()
            ->assertJsonPath('has_session', true)
            ->assertJsonStructure(['session' => ['session_number'], 'metrics' => ['sales_count']]);
    }

    public function test_checkout_blocked_without_session_json(): void
    {
        [$company, $branch, $user] = $this->tenantUser($this->permissions());

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $response = $this->actingAs($user)
            ->postJson(route('admin.commercial.pos.store'), [
                'action' => 'pay',
                'is_walk_in' => true,
                'payment_method' => PosPaymentMethod::Cash->value,
                'lines' => [[
                    'description' => 'Item',
                    'quantity' => 1,
                    'unit_price' => 100,
                    'discount_amount' => 0,
                    'tax_amount' => 0,
                ]],
            ]);

        $response->assertStatus(422);
        $this->assertArrayHasKey('session', (array) $response->json('errors'));
    }

    public function test_payment_completes_sale_and_returns_receipt_payload(): void
    {
        [$company, $branch, $user] = $this->tenantUser($this->permissions());
        $this->openSession($company, $branch, $user);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->postJson(route('admin.commercial.pos.store'), [
                'action' => 'pay',
                'is_walk_in' => true,
                'payment_method' => PosPaymentMethod::Cash->value,
                'lines' => [[
                    'description' => 'Receipt item',
                    'quantity' => 1,
                    'unit_price' => 99,
                    'discount_amount' => 0,
                    'tax_amount' => 0,
                ]],
            ])
            ->assertOk()
            ->assertJsonPath('receipt.total_amount', 99)
            ->assertJsonStructure(['receipt' => ['sale_number', 'items', 'payments', 'full_receipt_url']]);
    }

    public function test_held_sale_created_via_json_without_redirect(): void
    {
        [$company, $branch, $user] = $this->tenantUser($this->permissions());
        $this->openSession($company, $branch, $user);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->postJson(route('admin.commercial.pos.store'), [
                'action' => 'hold',
                'is_walk_in' => true,
                'lines' => [[
                    'description' => 'Held via JSON',
                    'quantity' => 1,
                    'unit_price' => 200,
                    'discount_amount' => 0,
                    'tax_amount' => 0,
                ]],
            ])
            ->assertOk()
            ->assertJsonStructure(['sale_id', 'message']);

        $this->assertSame(PosSaleStatus::Held, PosSale::query()->first()->status);
    }

    public function test_held_sale_resume_payload_without_full_page(): void
    {
        [$company, $branch, $user] = $this->tenantUser($this->permissions());
        $sale = $this->createHeldSale($company, $branch, $user);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->getJson(route('admin.commercial.pos.counter-sales.held-sales.resume', $sale))
            ->assertOk()
            ->assertJsonPath('cart.sale_number', $sale->sale_number)
            ->assertJsonPath('cart.lines.0.description', 'Banner print');
    }

    public function test_resume_route_redirects_to_counter_sales(): void
    {
        [$company, $branch, $user] = $this->tenantUser($this->permissions());
        $sale = $this->createHeldSale($company, $branch, $user);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.commercial.pos.resume', $sale))
            ->assertRedirect(route('admin.commercial.pos.counter-sales', ['resume' => $sale->id]));
    }

    public function test_close_session_preview_endpoint(): void
    {
        [$company, $branch, $user] = $this->tenantUser($this->permissions());
        $this->openSession($company, $branch, $user);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->getJson(route('admin.commercial.pos.counter-sales.session.close-preview'))
            ->assertOk()
            ->assertJsonStructure(['expected_cash', 'expected_mpesa', 'expected_total', 'variance_tolerance']);
    }

    public function test_receipt_payload_endpoint(): void
    {
        [$company, $branch, $user] = $this->tenantUser($this->permissions());
        $session = $this->openSession($company, $branch, $user);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)->postJson(route('admin.commercial.pos.store'), [
            'action' => 'pay',
            'is_walk_in' => true,
            'payment_method' => PosPaymentMethod::Cash->value,
            'lines' => [[
                'description' => 'Payload test',
                'quantity' => 1,
                'unit_price' => 50,
                'discount_amount' => 0,
                'tax_amount' => 0,
            ]],
        ]);

        $sale = PosSale::query()->first();

        $this->actingAs($user)
            ->getJson(route('admin.commercial.pos.counter-sales.receipt', $sale))
            ->assertOk()
            ->assertJsonPath('receipt.sale_number', $sale->sale_number);
    }

    public function test_fallback_full_page_receipt_still_works(): void
    {
        [$company, $branch, $user] = $this->tenantUser($this->permissions());
        $this->openSession($company, $branch, $user);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)->post(route('admin.commercial.pos.store'), [
            'action' => 'pay',
            'is_walk_in' => true,
            'payment_method' => PosPaymentMethod::Cash->value,
            'lines' => [[
                'description' => 'Full page',
                'quantity' => 1,
                'unit_price' => 10,
                'discount_amount' => 0,
                'tax_amount' => 0,
            ]],
        ]);

        $sale = PosSale::query()->first();

        $this->actingAs($user)
            ->get(route('admin.commercial.pos.receipt', $sale))
            ->assertOk()
            ->assertSee('Print receipt', false);
    }

    public function test_close_session_endpoint_works(): void
    {
        [$company, $branch, $user] = $this->tenantUser($this->permissions());
        $this->openSession($company, $branch, $user);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->postJson(route('admin.commercial.pos.counter-sales.session.close'), [
                'actual_cash' => 1000,
                'closing_notes' => 'End of shift',
            ])
            ->assertOk()
            ->assertJsonPath('session.has_session', false);
    }

    public function test_permission_enforcement_on_session_open(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['pos.counter_sales.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->postJson(route('admin.commercial.pos.counter-sales.session.open'), [
                'cashier_id' => $user->id,
                'opening_float' => 100,
                'opening_cash' => 0,
            ])
            ->assertForbidden();
    }

    /**
     * @return list<string>
     */
    protected function permissions(): array
    {
        return [
            'pos.counter_sales.view',
            'pos.counter_sales.create',
            'pos.counter_sales.hold',
            'pos.counter_sales.complete',
            'pos.counter_sales.cancel',
            'pos.receipts.reprint',
            'pos.sessions.view',
            'pos.sessions.open',
            'pos.sessions.close',
        ];
    }

    protected function openSession(Company $company, Branch $branch, User $user): PosSession
    {
        return PosSession::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'cashier_id' => $user->id,
            'session_number' => 'SES-UX-'.uniqid(),
            'opening_float' => 1000,
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
            'sale_number' => 'POS-UX-HOLD-'.uniqid(),
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
            'held_at' => now(),
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
