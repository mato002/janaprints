<?php

namespace Tests\Feature\Inventory;

use App\Enums\InventoryMovementType;
use App\Enums\VirtualWarehouseRole;
use App\Models\Inventory\InventoryMovement;
use App\Support\Inventory\InventoryLifecycleInspector;
use Database\Seeders\InventoryFoundationSeeder;
use Database\Seeders\InventoryVirtualWarehouseSeeder;
use Database\Seeders\OrganizationFoundationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Production\Concerns\InteractsWithProductionCompletion;
use Tests\TestCase;

class ArchitectureGovernanceTest extends TestCase
{
    use InteractsWithProductionCompletion;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(OrganizationFoundationSeeder::class);
        $this->seed(InventoryFoundationSeeder::class);
        $this->seed(InventoryVirtualWarehouseSeeder::class);
    }

    public function test_wip_documented_as_accounting_only_in_config(): void
    {
        $this->assertTrue(config('inventory_lifecycle.wip.accounting_only'));
        $this->assertFalse(config('inventory_lifecycle.wip.virtual_warehouse_active'));
        $this->assertSame('production_material_consumption', config('inventory_lifecycle.wip.wip_posting_source'));
        $this->assertNotContains('wip', config('inventory_lifecycle.inventory_stages'));
        $this->assertContains('wip', config('inventory_lifecycle.accounting_stages'));
    }

    public function test_wip_virtual_role_is_accounting_only_layer(): void
    {
        $this->assertTrue(VirtualWarehouseRole::Wip->isAccountingOnlyLayer());
        $this->assertFalse(VirtualWarehouseRole::Wip->tracksPhysicalInventory());
        $this->assertTrue(VirtualWarehouseRole::FinishedGoods->tracksPhysicalInventory());
    }

    public function test_material_consumption_does_not_create_wip_inventory_movement(): void
    {
        $this->seedProductionCompletionEnvironment();

        [, , $user, $rawItem, , $warehouse, $jobCard] = $this->readyJobForCompletion();

        $physicalConsumption = InventoryMovement::query()
            ->where('warehouse_id', $warehouse->id)
            ->where('movement_type', InventoryMovementType::ProductionConsumption)
            ->count();

        $wipMovements = InventoryMovement::query()
            ->whereHas('warehouse', fn ($q) => $q->where('virtual_role', VirtualWarehouseRole::Wip))
            ->where('movement_type', InventoryMovementType::ProductionConsumption)
            ->count();

        $this->assertSame(1, $physicalConsumption);
        $this->assertSame(0, $wipMovements);
    }

    public function test_lifecycle_inspector_passes_after_hardening(): void
    {
        $report = app(InventoryLifecycleInspector::class)->inspect();

        $this->assertSame(0, $report['failed'], json_encode($report['checks'], JSON_PRETTY_PRINT));
    }
}
