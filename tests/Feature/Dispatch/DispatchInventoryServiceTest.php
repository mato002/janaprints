<?php

namespace Tests\Feature\Dispatch;

use App\Enums\InventoryMovementType;
use App\Enums\ProductionJobCardStatus;
use App\Enums\VirtualWarehouseRole;
use App\Models\Dispatch\DeliveryNoteItem;
use App\Models\Inventory\InventoryMovement;
use App\Services\Dispatch\DispatchInventoryService;
use App\Services\Dispatch\DeliveryNoteService;
use App\Services\Inventory\VirtualWarehouseResolverService;
use App\Services\Production\ProductionCompletionService;
use App\Support\InventoryStockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\Feature\Dispatch\Concerns\InteractsWithDispatchInventory;
use Tests\TestCase;

class DispatchInventoryServiceTest extends TestCase
{
    use InteractsWithDispatchInventory;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedDispatchInventoryEnvironment();
    }

    public function test_dispatch_moves_fg_to_transit(): void
    {
        [$note, $finishedItem, $user, $jobCard] = $this->prepareDraftNoteWithFg();

        app(DeliveryNoteService::class)->dispatch($note, $user->id);

        $companyId = $jobCard->company_id;
        $fg = app(VirtualWarehouseResolverService::class)->finishedGoods($companyId);
        $transit = app(VirtualWarehouseResolverService::class)->inTransit($companyId);

        $this->assertEquals(0, InventoryStockService::balance($finishedItem->id, $fg->id));
        $this->assertEquals(5, InventoryStockService::balance($finishedItem->id, $transit->id));
    }

    public function test_dispatch_creates_movement_records(): void
    {
        [$note, , $user] = $this->prepareDraftNoteWithFg();
        app(DeliveryNoteService::class)->dispatch($note, $user->id);

        $line = $note->fresh('items')->items->first();
        $this->assertDatabaseHas('inventory_movements', [
            'reference_type' => DeliveryNoteItem::class,
            'reference_id' => $line->id,
            'movement_type' => InventoryMovementType::DispatchToTransit->value,
        ]);
    }

    public function test_duplicate_dispatch_movements_blocked(): void
    {
        [$note, , $user] = $this->readyDispatchedDeliveryNote();
        $count = InventoryMovement::query()
            ->where('movement_type', InventoryMovementType::DispatchToTransit)
            ->count();

        app(DispatchInventoryService::class)->dispatch($note->fresh(['items']), $user->id);

        $this->assertSame($count, InventoryMovement::query()
            ->where('movement_type', InventoryMovementType::DispatchToTransit)
            ->count());
    }

    public function test_dispatch_fails_without_fg_inventory_lines(): void
    {
        [$note, , $user, $jobCard] = $this->prepareDraftNoteWithFg();
        $jobCard->productionOutputs()->delete();
        $note->items()->update([
            'inventory_item_id' => null,
            'production_output_id' => null,
            'unit_cost' => null,
            'total_cost' => null,
        ]);

        $this->expectException(ValidationException::class);
        app(DeliveryNoteService::class)->dispatch($note->fresh(['items']), $user->id);
    }
}
