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

class PosCounterSalesWorkstationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_counter_sales_workstation_loads(): void
    {
        [$company, $branch, $user] = $this->tenantUser($this->cashierPermissions());

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.commercial.pos.counter-sales'))
            ->assertOk()
            ->assertSee('Shopping cart', false)
            ->assertSee('Barcode scan', false)
            ->assertSee('Sale summary', false);
    }

    public function test_product_search_by_name(): void
    {
        [$company, $branch, $user] = $this->tenantUser($this->cashierPermissions());

        $item = InventoryItem::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'item_name' => 'Glossy A4 Paper',
            'sku' => 'GLOSS-A4',
            'standard_cost' => 45.50,
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->getJson(route('admin.commercial.pos.counter-sales.products.search', ['q' => 'Glossy']))
            ->assertOk()
            ->assertJsonPath('products.0.id', $item->id)
            ->assertJsonPath('products.0.name', 'Glossy A4 Paper');
    }

    public function test_product_search_by_barcode(): void
    {
        [$company, $branch, $user] = $this->tenantUser($this->cashierPermissions());

        $item = InventoryItem::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'sku' => 'SKU-998877',
            'standard_cost' => 120,
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->getJson(route('admin.commercial.pos.counter-sales.products.search', ['barcode' => 'SKU-998877']))
            ->assertOk()
            ->assertJsonPath('exact', true)
            ->assertJsonPath('products.0.id', $item->id);
    }

    public function test_create_sale_add_item_and_complete(): void
    {
        [$company, $branch, $user] = $this->tenantUser($this->cashierPermissions());
        $this->openSession($company, $branch, $user);

        InventoryItem::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'item_name' => 'Business Cards',
            'standard_cost' => 250,
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $response = $this->actingAs($user)
            ->post(route('admin.commercial.pos.store'), [
                'action' => 'pay',
                'is_walk_in' => true,
                'payment_method' => PosPaymentMethod::Cash->value,
                'lines' => [[
                    'description' => 'Business Cards',
                    'quantity' => 1,
                    'unit_price' => 250,
                    'discount_amount' => 0,
                    'tax_amount' => 0,
                ]],
            ]);

        $response->assertRedirect();

        $sale = PosSale::query()->first();
        $this->assertNotNull($sale);
        $this->assertSame(PosSaleStatus::Paid, $sale->status);
        $this->assertCount(1, $sale->items);
        $this->assertSame('Business Cards', $sale->items->first()->description);
    }

    public function test_update_quantity_on_complete_sale(): void
    {
        [$company, $branch, $user] = $this->tenantUser($this->cashierPermissions());
        $this->openSession($company, $branch, $user);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->post(route('admin.commercial.pos.store'), [
                'action' => 'pay',
                'is_walk_in' => true,
                'payment_method' => PosPaymentMethod::Mpesa->value,
                'lines' => [[
                    'description' => 'Sticker pack',
                    'quantity' => 5,
                    'unit_price' => 20,
                    'discount_amount' => 0,
                    'tax_amount' => 0,
                ]],
            ])
            ->assertRedirect();

        $sale = PosSale::query()->first();
        $this->assertSame('100.00', $sale->total_amount);
        $this->assertEquals(5, (float) $sale->items->first()->quantity);
    }

    public function test_add_multiple_items_to_sale(): void
    {
        [$company, $branch, $user] = $this->tenantUser($this->cashierPermissions());
        $this->openSession($company, $branch, $user);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->post(route('admin.commercial.pos.store'), [
                'action' => 'pay',
                'is_walk_in' => true,
                'payment_method' => PosPaymentMethod::Card->value,
                'lines' => [
                    [
                        'description' => 'Business cards',
                        'quantity' => 1,
                        'unit_price' => 75,
                        'discount_amount' => 0,
                        'tax_amount' => 0,
                    ],
                    [
                        'description' => 'Flyers',
                        'quantity' => 2,
                        'unit_price' => 50,
                        'discount_amount' => 0,
                        'tax_amount' => 0,
                    ],
                ],
            ])
            ->assertRedirect();

        $sale = PosSale::query()->first();
        $this->assertCount(2, $sale->items);
        $this->assertSame('175.00', $sale->total_amount);
    }

    public function test_hold_sale(): void
    {
        [$company, $branch, $user] = $this->tenantUser($this->cashierPermissions());
        $this->openSession($company, $branch, $user);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->post(route('admin.commercial.pos.store'), [
                'action' => 'hold',
                'is_walk_in' => true,
                'hold_label' => 'Counter A',
                'lines' => [[
                    'description' => 'Held banner',
                    'quantity' => 1,
                    'unit_price' => 500,
                    'discount_amount' => 0,
                    'tax_amount' => 0,
                ]],
            ])
            ->assertRedirect(route('admin.commercial.pos.counter-sales'));

        $sale = PosSale::query()->first();
        $this->assertSame(PosSaleStatus::Held, $sale->status);
        $this->assertDatabaseHas('pos_sale_holds', ['pos_sale_id' => $sale->id]);
    }

    public function test_resume_held_sale(): void
    {
        [$company, $branch, $user] = $this->tenantUser($this->cashierPermissions());
        $sale = $this->createHeldSale($company, $branch, $user);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.commercial.pos.resume', $sale))
            ->assertRedirect(route('admin.commercial.pos.counter-sales', ['resume' => $sale->id]));

        $this->actingAs($user)
            ->get(route('admin.commercial.pos.counter-sales', ['resume' => $sale->id]))
            ->assertOk()
            ->assertSee('posCounterWorkstation', false)
            ->assertSee('Complete sale', false);

        $this->actingAs($user)
            ->getJson(route('admin.commercial.pos.counter-sales.held-sales.resume', $sale))
            ->assertOk()
            ->assertJsonPath('cart.sale_number', $sale->sale_number);
    }

    public function test_complete_held_sale(): void
    {
        [$company, $branch, $user] = $this->tenantUser($this->cashierPermissions());
        $sale = $this->createHeldSale($company, $branch, $user);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->post(route('admin.commercial.pos.pay', $sale), [
                'is_walk_in' => true,
                'payment_method' => PosPaymentMethod::Bank->value,
                'lines' => [[
                    'description' => 'Banner print',
                    'quantity' => 2,
                    'unit_price' => 150,
                    'discount_amount' => 0,
                    'tax_amount' => 0,
                ]],
            ])
            ->assertRedirect();

        $sale->refresh();
        $this->assertSame(PosSaleStatus::Paid, $sale->status);
    }

    public function test_cancel_held_sale(): void
    {
        [$company, $branch, $user] = $this->tenantUser($this->cashierPermissions());
        $sale = $this->createHeldSale($company, $branch, $user);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->post(route('admin.commercial.pos.cancel', $sale))
            ->assertRedirect();

        $sale->refresh();
        $this->assertSame(PosSaleStatus::Cancelled, $sale->status);
        $this->assertDatabaseMissing('pos_sale_holds', ['pos_sale_id' => $sale->id]);
    }

    public function test_receipt_generation(): void
    {
        [$company, $branch, $user] = $this->tenantUser($this->cashierPermissions());
        $this->openSession($company, $branch, $user);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->post(route('admin.commercial.pos.store'), [
                'action' => 'pay',
                'is_walk_in' => true,
                'payment_method' => PosPaymentMethod::Cash->value,
                'lines' => [[
                    'description' => 'Receipt test item',
                    'quantity' => 1,
                    'unit_price' => 99,
                    'discount_amount' => 0,
                    'tax_amount' => 0,
                ]],
            ]);

        $sale = PosSale::query()->first();

        $this->actingAs($user)
            ->get(route('admin.commercial.pos.receipt', $sale))
            ->assertOk()
            ->assertSee($sale->sale_number, false)
            ->assertSee('Receipt test item', false)
            ->assertSee('Print receipt', false)
            ->assertSee('Reprint receipt', false)
            ->assertSee($user->name, false);
    }

    public function test_permission_enforcement(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['crm.customers.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.commercial.pos.counter-sales'))
            ->assertForbidden();

        $this->actingAs($user)
            ->getJson(route('admin.commercial.pos.counter-sales.products.search', ['q' => 'test']))
            ->assertForbidden();
    }

    public function test_checkout_without_session_blocked(): void
    {
        [$company, $branch, $user] = $this->tenantUser([
            'pos.counter_sales.view',
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
                    'description' => 'Blocked item',
                    'quantity' => 1,
                    'unit_price' => 50,
                    'discount_amount' => 0,
                    'tax_amount' => 0,
                ]],
            ])
            ->assertSessionHasErrors('session');
    }

    public function test_counter_sales_workstation_is_reachable(): void
    {
        [$company, $branch, $user] = $this->tenantUser($this->cashierPermissions());

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.commercial.pos.counter-sales'))
            ->assertOk();
    }

    /**
     * @return list<string>
     */
    protected function cashierPermissions(): array
    {
        return [
            'pos.counter_sales.view',
            'pos.counter_sales.create',
            'pos.counter_sales.hold',
            'pos.counter_sales.complete',
            'pos.counter_sales.cancel',
            'commercial.pos.sessions.open',
        ];
    }

    protected function openSession(Company $company, Branch $branch, User $user): PosSession
    {
        return PosSession::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'cashier_id' => $user->id,
            'session_number' => 'SES-CS-'.uniqid(),
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
            'sale_number' => 'POS-CS-HOLD-'.uniqid(),
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
            'held_at' => now()->subMinutes(5),
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
