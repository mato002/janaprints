<?php

namespace Tests\Feature\Commercial;

use App\Enums\PosPaymentMethod;
use App\Enums\PosRefundMethod;
use App\Enums\PosReturnStatus;
use App\Enums\PosReturnType;
use App\Enums\PosSaleStatus;
use App\Enums\PosSessionStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Pos\PosReturn;
use App\Models\Pos\PosReturnEvent;
use App\Models\Pos\PosSale;
use App\Models\Pos\PosSaleItem;
use App\Models\Pos\PosSession;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PosReturnTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_full_return_workflow(): void
    {
        [$company, $branch, $user, $sale] = $this->paidSaleWithItems([
            ['description' => 'T-Shirt', 'quantity' => 2, 'unit_price' => 500, 'line_total' => 1000],
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)->post(route('admin.commercial.pos.returns.store'), [
            'sale_number' => $sale->sale_number,
            'return_type' => PosReturnType::FullReturn->value,
            'refund_method' => PosRefundMethod::Cash->value,
            'reason' => 'Customer changed mind',
            'lines' => [],
        ])->assertRedirect();

        $return = PosReturn::query()->first();
        $this->assertNotNull($return);
        $this->assertSame(PosReturnStatus::Pending, $return->status);
        $this->assertSame('1000.00', $return->refund_amount);
        $this->assertTrue($return->is_full_return);

        $approver = $this->userWithPermissions([
            'commercial.pos.returns.view',
            'commercial.pos.returns.approve',
            'commercial.pos.returns.audit',
        ], $company, $branch);

        $this->actingAs($approver)
            ->post(route('admin.commercial.pos.returns.approve', $return))
            ->assertRedirect();

        $return->refresh();
        $sale->refresh();

        $this->assertSame(PosReturnStatus::Completed, $return->status);
        $this->assertSame(PosSaleStatus::Refunded, $sale->status);
        $this->assertSame('1000.00', $sale->total_amount);
        $this->assertDatabaseCount('pos_return_events', 3);
    }

    public function test_partial_return(): void
    {
        [$company, $branch, $user, $sale] = $this->paidSaleWithItems([
            ['description' => 'Mug', 'quantity' => 4, 'unit_price' => 250, 'line_total' => 1000],
        ]);

        $item = $sale->items->first();

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)->post(route('admin.commercial.pos.returns.store'), [
            'sale_number' => $sale->sale_number,
            'return_type' => PosReturnType::PartialReturn->value,
            'refund_method' => PosRefundMethod::Mpesa->value,
            'reason' => 'Partial return',
            'lines' => [[
                'pos_sale_item_id' => $item->id,
                'quantity_returned' => 1,
            ]],
        ])->assertRedirect();

        $return = PosReturn::query()->first();
        $this->assertSame('250.00', $return->refund_amount);
        $this->assertFalse($return->is_full_return);

        $approver = $this->userWithPermissions([
            'commercial.pos.returns.view',
            'commercial.pos.returns.approve',
        ], $company, $branch);

        $this->actingAs($approver)->post(route('admin.commercial.pos.returns.approve', $return));

        $sale->refresh();
        $this->assertSame(PosSaleStatus::PartiallyRefunded, $sale->status);
        $this->assertSame('1000.00', $sale->total_amount);
    }

    public function test_multiple_items_refund_calculation(): void
    {
        [$company, $branch, $user, $sale] = $this->paidSaleWithItems([
            ['description' => 'Item A', 'quantity' => 1, 'unit_price' => 300, 'line_total' => 300],
            ['description' => 'Item B', 'quantity' => 2, 'unit_price' => 200, 'line_total' => 400],
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $lines = $sale->items->map(fn (PosSaleItem $item) => [
            'pos_sale_item_id' => $item->id,
            'quantity_returned' => $item->quantity,
        ])->all();

        $this->actingAs($user)->post(route('admin.commercial.pos.returns.store'), [
            'sale_number' => $sale->sale_number,
            'return_type' => PosReturnType::CustomerCancellation->value,
            'refund_method' => PosRefundMethod::Card->value,
            'reason' => 'Cancel order',
            'lines' => $lines,
        ]);

        $return = PosReturn::query()->first();
        $this->assertSame('700.00', $return->refund_amount);
        $this->assertCount(2, $return->items);
    }

    public function test_approval_flow_reject(): void
    {
        [$company, $branch, $user, $sale] = $this->paidSaleWithItems([
            ['description' => 'Poster', 'quantity' => 1, 'unit_price' => 150, 'line_total' => 150],
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)->post(route('admin.commercial.pos.returns.store'), [
            'sale_number' => $sale->sale_number,
            'return_type' => PosReturnType::WrongItem->value,
            'refund_method' => PosRefundMethod::NoRefund->value,
            'reason' => 'Wrong SKU',
            'lines' => [[
                'pos_sale_item_id' => $sale->items->first()->id,
                'quantity_returned' => 1,
            ]],
        ]);

        $return = PosReturn::query()->first();

        $approver = $this->userWithPermissions([
            'commercial.pos.returns.view',
            'commercial.pos.returns.approve',
        ], $company, $branch);

        $this->actingAs($approver)->post(route('admin.commercial.pos.returns.reject', $return), [
            'rejection_reason' => 'Insufficient proof',
        ])->assertRedirect();

        $return->refresh();
        $sale->refresh();

        $this->assertSame(PosReturnStatus::Rejected, $return->status);
        $this->assertSame(PosSaleStatus::Paid, $sale->status);
        $this->assertSame('0.00', $return->refund_amount);
    }

    public function test_audit_integrity(): void
    {
        [$company, $branch, $user, $sale] = $this->paidSaleWithItems([
            ['description' => 'Banner', 'quantity' => 1, 'unit_price' => 800, 'line_total' => 800],
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)->post(route('admin.commercial.pos.returns.store'), [
            'sale_number' => $sale->sale_number,
            'return_type' => PosReturnType::DamagedItem->value,
            'refund_method' => PosRefundMethod::StoreCredit->value,
            'reason' => 'Damaged on delivery',
            'lines' => [[
                'pos_sale_item_id' => $sale->items->first()->id,
                'quantity_returned' => 1,
            ]],
        ]);

        $return = PosReturn::query()->first();

        $this->assertDatabaseHas('pos_return_events', [
            'pos_return_id' => $return->id,
            'action' => 'created',
            'actor_id' => $user->id,
        ]);

        $originalSaleTotal = $sale->total_amount;
        $originalItemQty = $sale->items->first()->quantity;

        $approver = $this->userWithPermissions([
            'commercial.pos.returns.view',
            'commercial.pos.returns.approve',
            'commercial.pos.returns.audit',
        ], $company, $branch);

        $this->actingAs($approver)->post(route('admin.commercial.pos.returns.approve', $return));

        $sale->refresh();
        $this->assertSame($originalSaleTotal, $sale->total_amount);
        $this->assertSame($originalItemQty, $sale->items->first()->quantity);
        $this->assertGreaterThanOrEqual(3, PosReturnEvent::query()->where('pos_return_id', $return->id)->count());
    }

    public function test_permission_enforcement(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['pos.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.commercial.pos.returns.dashboard'))
            ->assertForbidden();
    }

    /**
     * @param  array<int, array{description: string, quantity: float|int, unit_price: float|int, line_total: float|int}>  $lines
     * @return array{0: Company, 1: Branch, 2: User, 3: PosSale}
     */
    protected function paidSaleWithItems(array $lines): array
    {
        [$company, $branch, $user] = $this->tenantUser([
            'commercial.pos.returns.view',
            'commercial.pos.returns.create',
        ]);

        $session = PosSession::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'cashier_id' => $user->id,
            'session_number' => 'SES-RET-'.uniqid(),
            'opening_float' => 1000,
            'opening_cash' => 0,
            'status' => PosSessionStatus::Open,
            'opened_at' => now(),
            'opened_by' => $user->id,
        ]);

        $total = array_sum(array_column($lines, 'line_total'));

        $sale = PosSale::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'cashier_id' => $user->id,
            'pos_session_id' => $session->id,
            'sale_number' => 'POS-TEST-'.uniqid(),
            'sale_date' => today(),
            'subtotal' => $total,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => $total,
            'amount_paid' => $total,
            'balance_due' => 0,
            'status' => PosSaleStatus::Paid,
            'is_walk_in' => true,
        ]);

        foreach ($lines as $line) {
            PosSaleItem::query()->create([
                'pos_sale_id' => $sale->id,
                'description' => $line['description'],
                'quantity' => $line['quantity'],
                'unit_price' => $line['unit_price'],
                'discount_amount' => 0,
                'tax_amount' => 0,
                'line_total' => $line['line_total'],
            ]);
        }

        $sale->payments()->create([
            'payment_method' => PosPaymentMethod::Cash,
            'amount' => $total,
        ]);

        return [$company, $branch, $user, $sale->fresh('items')];
    }

    protected function userWithPermissions(array $permissions, Company $company, Branch $branch): User
    {
        return $this->tenantUser($permissions, $company, $branch)[2];
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
