<?php

namespace Tests\Feature\Commercial;

use App\Enums\InventoryDocumentStatus;
use App\Enums\InventoryMovementType;
use App\Enums\PosPaymentMethod;
use App\Enums\PosRefundMethod;
use App\Enums\PosReturnType;
use App\Enums\PosSaleStatus;
use App\Enums\PosSessionStatus;
use App\Enums\StockReceiptSource;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\InventoryMovement;
use App\Models\Inventory\StockReceipt;
use App\Models\Inventory\Warehouse;
use App\Models\Pos\PosReturn;
use App\Models\Pos\PosSale;
use App\Models\Pos\PosSession;
use App\Models\User;
use App\Support\Commercial\PosInventoryService;
use App\Support\InventoryStockService;
use App\Support\StockReceiptService;
use Database\Seeders\InventoryFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PosInventoryTruthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_paid_sale_reduces_stock(): void
    {
        [$company, $branch, $user, $item, $warehouse] = $this->posInventoryContext();
        $this->seedStock($company, $branch, $user, $item, $warehouse, 50);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);
        $this->openSession($company, $branch, $user);

        $this->actingAs($user)->post(route('admin.commercial.pos.store'), [
            'action' => 'pay',
            'is_walk_in' => true,
            'payment_method' => PosPaymentMethod::Cash->value,
            'lines' => [[
                'inventory_item_id' => $item->id,
                'description' => $item->item_name,
                'quantity' => 3,
                'unit_price' => 100,
                'discount_amount' => 0,
                'tax_amount' => 0,
            ]],
        ])->assertRedirect();

        $sale = PosSale::query()->where('company_id', $company->id)->first();
        $this->assertSame(PosSaleStatus::Paid, $sale->status);
        $this->assertEquals(47, InventoryStockService::balance($item->id, $warehouse->id));

        $this->assertDatabaseHas('inventory_movements', [
            'inventory_item_id' => $item->id,
            'warehouse_id' => $warehouse->id,
            'movement_type' => InventoryMovementType::Issue->value,
            'reference_type' => PosInventoryService::REFERENCE_TYPE_POS_SALE,
            'reference_id' => $sale->id,
            'quantity' => -3,
        ]);
    }

    public function test_return_restores_stock(): void
    {
        [$company, $branch, $user, $item, $warehouse] = $this->posInventoryContext([
            'pos.view', 'pos.create', 'pos.edit',
            'commercial.pos.sessions.open',
            'commercial.pos.returns.view',
            'commercial.pos.returns.create',
            'commercial.pos.returns.approve',
        ]);

        $this->seedStock($company, $branch, $user, $item, $warehouse, 20);
        $sale = $this->createPaidSale($company, $branch, $user, $item, 5);
        $this->assertEquals(15, InventoryStockService::balance($item->id, $warehouse->id));

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)->post(route('admin.commercial.pos.returns.store'), [
            'sale_number' => $sale->sale_number,
            'return_type' => PosReturnType::FullReturn->value,
            'refund_method' => PosRefundMethod::Cash->value,
            'reason' => 'Defective print',
            'lines' => [],
        ])->assertRedirect();

        $return = PosReturn::query()->first();
        $approver = $this->userWithPermissions([
            'commercial.pos.returns.view',
            'commercial.pos.returns.approve',
        ], $company, $branch);

        $this->actingAs($approver)
            ->post(route('admin.commercial.pos.returns.approve', $return))
            ->assertRedirect();

        $this->assertEquals(20, InventoryStockService::balance($item->id, $warehouse->id));
        $this->assertDatabaseHas('inventory_movements', [
            'inventory_item_id' => $item->id,
            'warehouse_id' => $warehouse->id,
            'movement_type' => InventoryMovementType::Receipt->value,
            'reference_type' => PosInventoryService::REFERENCE_TYPE_POS_RETURN,
            'reference_id' => $return->id,
            'quantity' => 5,
        ]);
    }

    public function test_held_sale_has_no_inventory_movement(): void
    {
        [$company, $branch, $user, $item, $warehouse] = $this->posInventoryContext();
        $this->seedStock($company, $branch, $user, $item, $warehouse, 30);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);
        $this->openSession($company, $branch, $user);

        $this->actingAs($user)->post(route('admin.commercial.pos.store'), [
            'action' => 'hold',
            'is_walk_in' => true,
            'hold_label' => 'Counter A',
            'lines' => [[
                'inventory_item_id' => $item->id,
                'description' => $item->item_name,
                'quantity' => 4,
                'unit_price' => 50,
                'discount_amount' => 0,
                'tax_amount' => 0,
            ]],
        ])->assertRedirect();

        $sale = PosSale::query()->where('company_id', $company->id)->first();
        $this->assertSame(PosSaleStatus::Held, $sale->status);
        $this->assertEquals(30, InventoryStockService::balance($item->id, $warehouse->id));
        $this->assertDatabaseMissing('inventory_movements', [
            'reference_type' => PosInventoryService::REFERENCE_TYPE_POS_SALE,
            'reference_id' => $sale->id,
        ]);
    }

    public function test_cancelled_sale_has_no_inventory_movement(): void
    {
        [$company, $branch, $user, $item, $warehouse] = $this->posInventoryContext([
            'pos.view', 'pos.create', 'pos.edit', 'pos.cancel',
            'commercial.pos.sessions.open',
        ]);

        $this->seedStock($company, $branch, $user, $item, $warehouse, 25);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);
        $this->openSession($company, $branch, $user);

        $this->actingAs($user)->post(route('admin.commercial.pos.store'), [
            'action' => 'hold',
            'is_walk_in' => true,
            'lines' => [[
                'inventory_item_id' => $item->id,
                'description' => $item->item_name,
                'quantity' => 2,
                'unit_price' => 75,
                'discount_amount' => 0,
                'tax_amount' => 0,
            ]],
        ]);

        $sale = PosSale::query()->where('company_id', $company->id)->first();

        $this->actingAs($user)
            ->post(route('admin.commercial.pos.cancel', $sale))
            ->assertRedirect();

        $sale->refresh();
        $this->assertSame(PosSaleStatus::Cancelled, $sale->status);
        $this->assertEquals(25, InventoryStockService::balance($item->id, $warehouse->id));
        $this->assertDatabaseMissing('inventory_movements', [
            'reference_type' => PosInventoryService::REFERENCE_TYPE_POS_SALE,
            'reference_id' => $sale->id,
        ]);
    }

    public function test_paid_sale_blocked_when_insufficient_stock(): void
    {
        [$company, $branch, $user, $item, $warehouse] = $this->posInventoryContext();
        $this->seedStock($company, $branch, $user, $item, $warehouse, 2);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);
        $this->openSession($company, $branch, $user);

        $this->actingAs($user)->post(route('admin.commercial.pos.store'), [
            'action' => 'pay',
            'is_walk_in' => true,
            'payment_method' => PosPaymentMethod::Cash->value,
            'lines' => [[
                'inventory_item_id' => $item->id,
                'description' => $item->item_name,
                'quantity' => 5,
                'unit_price' => 100,
                'discount_amount' => 0,
                'tax_amount' => 0,
            ]],
        ])->assertSessionHasErrors('quantity');

        $this->assertDatabaseCount('pos_sales', 0);
        $this->assertEquals(2, InventoryStockService::balance($item->id, $warehouse->id));
    }

    /**
     * @return array{0: Company, 1: Branch, 2: User, 3: InventoryItem, 4: Warehouse}
     */
    protected function posInventoryContext(array $permissions = ['pos.view', 'pos.create', 'commercial.pos.sessions.open']): array
    {
        [$company, $branch, $user] = $this->tenantUser($permissions);
        $this->seed(InventoryFoundationSeeder::class);

        $warehouse = Warehouse::query()
            ->where('company_id', $company->id)
            ->where('branch_id', $branch->id)
            ->where('code', 'MAIN')
            ->first();

        $category = \App\Models\Inventory\InventoryCategory::query()
            ->where('company_id', $company->id)
            ->where('branch_id', $branch->id)
            ->first();

        $unit = \App\Models\Inventory\UnitOfMeasure::query()
            ->where('company_id', $company->id)
            ->where('branch_id', $branch->id)
            ->first();

        $item = InventoryItem::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'inventory_category_id' => $category->id,
            'unit_of_measure_id' => $unit->id,
            'sku' => 'POS-SKU-'.uniqid(),
            'is_active' => true,
        ]);

        return [$company, $branch, $user, $item, $warehouse];
    }

    protected function seedStock(
        Company $company,
        Branch $branch,
        User $user,
        InventoryItem $item,
        Warehouse $warehouse,
        float $qty,
    ): void {
        $receipt = StockReceipt::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'warehouse_id' => $warehouse->id,
            'receipt_number' => 'SR-POS-'.uniqid(),
            'source' => StockReceiptSource::Purchase,
            'receipt_date' => now()->toDateString(),
            'status' => InventoryDocumentStatus::Draft,
            'received_by' => $user->id,
        ]);
        $receipt->items()->create([
            'inventory_item_id' => $item->id,
            'quantity' => $qty,
            'unit_cost' => 10,
        ]);
        StockReceiptService::post($receipt, $user->id);
    }

    protected function openSession(Company $company, Branch $branch, User $user): PosSession
    {
        return PosSession::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'cashier_id' => $user->id,
            'session_number' => 'SES-INV-'.uniqid(),
            'opening_float' => 0,
            'opening_cash' => 0,
            'status' => PosSessionStatus::Open,
            'opened_at' => now(),
            'opened_by' => $user->id,
        ]);
    }

    protected function createPaidSale(
        Company $company,
        Branch $branch,
        User $user,
        InventoryItem $item,
        float $qty,
    ): PosSale {
        $session = $this->openSession($company, $branch, $user);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)->post(route('admin.commercial.pos.store'), [
            'action' => 'pay',
            'is_walk_in' => true,
            'payment_method' => PosPaymentMethod::Cash->value,
            'lines' => [[
                'inventory_item_id' => $item->id,
                'description' => $item->item_name,
                'quantity' => $qty,
                'unit_price' => 100,
                'discount_amount' => 0,
                'tax_amount' => 0,
            ]],
        ])->assertRedirect();

        return PosSale::query()->where('company_id', $company->id)->first();
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
