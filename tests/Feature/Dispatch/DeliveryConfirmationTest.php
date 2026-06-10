<?php

namespace Tests\Feature\Dispatch;

use App\Enums\InventoryMovementType;
use App\Services\Dispatch\DeliveryNoteService;
use App\Services\Inventory\VirtualWarehouseResolverService;
use App\Support\InventoryStockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\Feature\Dispatch\Concerns\InteractsWithDispatchInventory;
use Tests\TestCase;

class DeliveryConfirmationTest extends TestCase
{
    use InteractsWithDispatchInventory;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedDispatchInventoryEnvironment();
    }

    public function test_delivery_removes_transit_stock(): void
    {
        [$note, $finishedItem, $user, $jobCard] = $this->readyDispatchedDeliveryNote();

        app(DeliveryNoteService::class)->deliver($note, $user->id);
        $transit = app(VirtualWarehouseResolverService::class)->inTransit($jobCard->company_id);

        $this->assertEquals(0, InventoryStockService::balance($finishedItem->id, $transit->id));
    }

    public function test_delivery_creates_cogs_movement(): void
    {
        [$note, , $user] = $this->readyDispatchedDeliveryNote();

        app(DeliveryNoteService::class)->deliver($note, $user->id);

        $this->assertDatabaseHas('inventory_movements', [
            'movement_type' => InventoryMovementType::DeliveryCogs->value,
        ]);
    }

    public function test_duplicate_delivery_blocked(): void
    {
        [$note, , $user] = $this->readyDispatchedDeliveryNote();
        app(DeliveryNoteService::class)->deliver($note, $user->id);

        $this->expectException(ValidationException::class);
        app(DeliveryNoteService::class)->deliver($note->fresh(), $user->id);
    }
}
