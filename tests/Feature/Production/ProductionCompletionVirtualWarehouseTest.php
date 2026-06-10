<?php

namespace Tests\Feature\Production;

use App\Enums\VirtualWarehouseRole;
use App\Services\Inventory\VirtualWarehouseResolverService;
use App\Services\Production\ProductionCompletionService;
use App\Support\InventoryStockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Production\Concerns\InteractsWithProductionCompletion;
use Tests\TestCase;

class ProductionCompletionVirtualWarehouseTest extends TestCase
{
    use InteractsWithProductionCompletion;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedProductionCompletionEnvironment();
    }

    public function test_output_goes_to_fg_virtual_warehouse(): void
    {
        [, , $user, , $finishedItem, $physicalWarehouse, $jobCard] = $this->readyJobForCompletion();
        $fgWarehouse = app(VirtualWarehouseResolverService::class)->finishedGoods($jobCard->company_id);

        app(ProductionCompletionService::class)->post($jobCard, [
            'finished_inventory_item_id' => $finishedItem->id,
            'quantity_completed' => 4,
        ], $user->id);

        $this->assertTrue($fgWarehouse->is_virtual);
        $this->assertSame(VirtualWarehouseRole::FinishedGoods, $fgWarehouse->virtual_role);
        $this->assertEquals(4, InventoryStockService::balance($finishedItem->id, $fgWarehouse->id));
        $this->assertEquals(0, InventoryStockService::balance($finishedItem->id, $physicalWarehouse->id));
    }

    public function test_finished_goods_virtual_location_balance_increases(): void
    {
        [$company, , $user, , $finishedItem, , $jobCard] = $this->readyJobForCompletion();
        $fgWarehouse = app(VirtualWarehouseResolverService::class)->finishedGoods($company->id);
        $before = InventoryStockService::getBalanceByVirtualRole(
            $finishedItem->id,
            $company->id,
            VirtualWarehouseRole::FinishedGoods,
        );

        app(ProductionCompletionService::class)->post($jobCard, [
            'finished_inventory_item_id' => $finishedItem->id,
            'quantity_completed' => 2.5,
        ], $user->id);

        $after = InventoryStockService::getBalanceByVirtualRole(
            $finishedItem->id,
            $company->id,
            VirtualWarehouseRole::FinishedGoods,
        );
        $this->assertEquals($before + 2.5, $after);
        $this->assertEquals(2.5, InventoryStockService::balance($finishedItem->id, $fgWarehouse->id));
    }

    public function test_cannot_use_physical_warehouse_for_completion_path(): void
    {
        [, , $user, , $finishedItem, $physicalWarehouse, $jobCard] = $this->readyJobForCompletion();

        $output = app(ProductionCompletionService::class)->post($jobCard, [
            'finished_inventory_item_id' => $finishedItem->id,
            'quantity_completed' => 1,
        ], $user->id);

        $this->assertNotSame($physicalWarehouse->id, $output->finished_warehouse_id);
        $this->assertTrue($output->finishedWarehouse->is_virtual);
    }
}
