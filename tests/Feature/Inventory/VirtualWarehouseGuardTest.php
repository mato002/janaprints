<?php

namespace Tests\Feature\Inventory;

use App\Enums\InventoryDocumentStatus;
use App\Enums\InventoryMovementType;
use App\Enums\StockReceiptSource;
use App\Enums\VirtualWarehouseRole;
use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\InventoryMovement;
use App\Models\Inventory\StockReceipt;
use App\Models\Inventory\Warehouse;
use App\Services\Inventory\VirtualWarehouseResolverService;
use App\Support\Inventory\VirtualWarehouseGuard;
use App\Support\InventoryMovementService;
use App\Support\StockReceiptService;
use Database\Seeders\InventoryFoundationSeeder;
use Database\Seeders\InventoryVirtualWarehouseSeeder;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class VirtualWarehouseGuardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        $this->seed(InventoryFoundationSeeder::class);
        $this->seed(InventoryVirtualWarehouseSeeder::class);
    }

    public function test_cannot_delete_virtual_warehouse_with_movements(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $user = $this->actingUser($company);
        $warehouse = app(VirtualWarehouseResolverService::class)->workInProgress($company->id);
        $this->assertNotNull($warehouse);

        $item = $this->sampleItem($company);
        $this->recordMovement($item, $warehouse, 5, $user->id);

        $this->expectException(ValidationException::class);
        VirtualWarehouseGuard::assertDeletable($warehouse);
    }

    public function test_cannot_change_virtual_role_with_movements(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $user = $this->actingUser($company);
        $warehouse = app(VirtualWarehouseResolverService::class)->workInProgress($company->id);
        $this->assertNotNull($warehouse);

        $item = $this->sampleItem($company);
        $this->recordMovement($item, $warehouse, 3, $user->id);

        $this->expectException(ValidationException::class);
        VirtualWarehouseGuard::assertVirtualRoleMutable($warehouse, VirtualWarehouseRole::FinishedGoods);
    }

    public function test_cannot_direct_receive_into_virtual_warehouse(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();
        $user = $this->actingUser($company);
        $warehouse = app(VirtualWarehouseResolverService::class)->finishedGoods($company->id);
        $item = $this->sampleItem($company);

        $receipt = StockReceipt::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'warehouse_id' => $warehouse->id,
            'receipt_number' => 'SR-VIRT-'.uniqid(),
            'source' => StockReceiptSource::Purchase,
            'receipt_date' => now()->toDateString(),
            'status' => InventoryDocumentStatus::Draft,
            'received_by' => $user->id,
        ]);
        $receipt->items()->create([
            'inventory_item_id' => $item->id,
            'quantity' => 10,
            'unit_cost' => 5,
        ]);

        $this->expectException(ValidationException::class);
        StockReceiptService::post($receipt, $user->id);
    }

    public function test_system_context_allows_virtual_receipt_guard_bypass(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $warehouse = app(VirtualWarehouseResolverService::class)->rawMaterials($company->id);

        VirtualWarehouseGuard::usingSystemContext(function () use ($warehouse) {
            VirtualWarehouseGuard::assertDirectReceiptAllowed($warehouse);
            $this->assertTrue(VirtualWarehouseGuard::isSystemContext());
        });

        $this->assertFalse(VirtualWarehouseGuard::isSystemContext());
    }

    protected function sampleItem(Company $company): InventoryItem
    {
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();
        $category = \App\Models\Inventory\InventoryCategory::query()->where('company_id', $company->id)->firstOrFail();
        $unit = \App\Models\Inventory\UnitOfMeasure::query()->where('company_id', $company->id)->firstOrFail();

        return InventoryItem::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'inventory_category_id' => $category->id,
            'unit_of_measure_id' => $unit->id,
            'sku' => 'VWG-'.uniqid(),
        ]);
    }

    protected function recordMovement(InventoryItem $item, Warehouse $warehouse, float $qty, int $userId): InventoryMovement
    {
        return InventoryMovementService::record([
            'company_id' => $item->company_id,
            'branch_id' => $item->branch_id,
            'inventory_item_id' => $item->id,
            'warehouse_id' => $warehouse->id,
            'movement_type' => InventoryMovementType::Adjustment,
            'quantity' => $qty,
            'unit_cost' => 10,
            'reference_type' => InventoryItem::class,
            'reference_id' => $item->id,
            'movement_date' => now()->toDateString(),
            'created_by' => $userId,
        ]);
    }

    protected function actingUser(Company $company): User
    {
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();

        return User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
    }
}
