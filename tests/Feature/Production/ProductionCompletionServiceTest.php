<?php

namespace Tests\Feature\Production;

use App\Enums\InventoryMovementType;
use App\Enums\ProductionOutputStatus;
use App\Models\Production\ProductionOutput;
use App\Services\Inventory\VirtualWarehouseResolverService;
use App\Services\Production\ProductionCompletionService;
use App\Support\InventoryStockService;
use App\Support\Production\JobCostingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\Feature\Production\Concerns\InteractsWithProductionCompletion;
use Tests\TestCase;

class ProductionCompletionServiceTest extends TestCase
{
    use InteractsWithProductionCompletion;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedProductionCompletionEnvironment();
    }

    public function test_can_complete_job_to_finished_goods(): void
    {
        [, , $user, , $finishedItem, , $jobCard] = $this->readyJobForCompletion();
        $fgWarehouse = app(VirtualWarehouseResolverService::class)->finishedGoods($jobCard->company_id);
        $this->assertNotNull($fgWarehouse);

        $output = app(ProductionCompletionService::class)->post($jobCard, [
            'finished_inventory_item_id' => $finishedItem->id,
            'quantity_completed' => 2,
            'quantity_rejected' => 0,
        ], $user->id);

        $this->assertSame(ProductionOutputStatus::Posted, $output->completion_status);
        $this->assertSame($fgWarehouse->id, $output->finished_warehouse_id);
        $this->assertEquals(2, InventoryStockService::balance($finishedItem->id, $fgWarehouse->id));
    }

    public function test_creates_production_outputs_record(): void
    {
        [, , $user, , $finishedItem, , $jobCard] = $this->readyJobForCompletion();

        $output = app(ProductionCompletionService::class)->post($jobCard, [
            'finished_inventory_item_id' => $finishedItem->id,
            'quantity_completed' => 1,
        ], $user->id);

        $this->assertDatabaseHas('production_outputs', [
            'id' => $output->id,
            'production_job_card_id' => $jobCard->id,
            'finished_inventory_item_id' => $finishedItem->id,
            'completion_status' => ProductionOutputStatus::Posted->value,
        ]);
    }

    public function test_creates_inventory_movement_into_finished_goods_virtual_warehouse(): void
    {
        [, , $user, , $finishedItem, , $jobCard] = $this->readyJobForCompletion();
        $fgWarehouse = app(VirtualWarehouseResolverService::class)->finishedGoods($jobCard->company_id);

        $output = app(ProductionCompletionService::class)->post($jobCard, [
            'finished_inventory_item_id' => $finishedItem->id,
            'quantity_completed' => 3,
        ], $user->id);

        $this->assertDatabaseHas('inventory_movements', [
            'inventory_item_id' => $finishedItem->id,
            'warehouse_id' => $fgWarehouse->id,
            'movement_type' => InventoryMovementType::FinishedGoodsReceipt->value,
            'reference_type' => ProductionOutput::class,
            'reference_id' => $output->id,
            'quantity' => 3,
        ]);
    }

    public function test_calculates_unit_cost_from_job_cost_sheet(): void
    {
        [, , $user, , $finishedItem, , $jobCard] = $this->readyJobForCompletion();
        $sheet = JobCostingService::buildOrRefresh($jobCard);

        $output = app(ProductionCompletionService::class)->post($jobCard, [
            'finished_inventory_item_id' => $finishedItem->id,
            'quantity_completed' => 2,
        ], $user->id);

        $expectedUnitCost = round((float) $sheet->total_cost / 2, 4);
        $this->assertEquals($expectedUnitCost, (float) $output->unit_cost);
        $this->assertEquals(round($expectedUnitCost * 2, 2), (float) $output->total_cost);
    }

    public function test_fails_if_no_cost_and_no_manual_cost(): void
    {
        [, , $user, , $finishedItem, , $jobCard] = $this->readyJobForCompletion();

        $jobCard->materialConsumptions()->delete();

        $this->expectException(ValidationException::class);
        app(ProductionCompletionService::class)->post($jobCard, [
            'finished_inventory_item_id' => $finishedItem->id,
            'quantity_completed' => 1,
        ], $user->id, false);
    }

    public function test_eligibility_suggests_order_quantity_and_per_unit_cost(): void
    {
        [, , $user, , $finishedItem, , $jobCard] = $this->readyJobForCompletion();

        $salesOrder = \App\Models\Sales\SalesOrder::factory()->create([
            'company_id' => $jobCard->company_id,
            'branch_id' => $jobCard->branch_id,
            'customer_id' => $jobCard->customer_id,
            'inventory_item_id' => $finishedItem->id,
            'created_by' => $user->id,
        ]);

        $salesOrder->items()->create([
            'inventory_item_id' => $finishedItem->id,
            'item_name' => $finishedItem->item_name,
            'quantity' => 200,
            'unit_price' => 50,
            'line_total' => 10000,
            'sort_order' => 1,
        ]);

        $jobCard->update([
            'sales_order_id' => $salesOrder->id,
            'inventory_item_id' => $finishedItem->id,
        ]);

        $sheet = JobCostingService::buildOrRefresh($jobCard->fresh());
        $eligibility = app(ProductionCompletionService::class)->eligibility($jobCard->fresh());

        $this->assertEquals(200.0, $eligibility['suggested_quantity_completed']);
        $this->assertEquals((int) $finishedItem->id, $eligibility['suggested_finished_item_id']);
        $this->assertEquals(
            round((float) $sheet->total_cost / 200, 4),
            $eligibility['suggested_unit_cost'],
        );
    }

    public function test_is_idempotent_for_duplicate_post(): void
    {
        [, , $user, , $finishedItem, , $jobCard] = $this->readyJobForCompletion();
        $service = app(ProductionCompletionService::class);
        $payload = [
            'finished_inventory_item_id' => $finishedItem->id,
            'quantity_completed' => 1,
        ];

        $service->post($jobCard, $payload, $user->id);

        $this->expectException(ValidationException::class);
        $service->post($jobCard->fresh(), $payload, $user->id);

        $this->assertSame(1, ProductionOutput::query()->where('production_job_card_id', $jobCard->id)->count());
    }
}
