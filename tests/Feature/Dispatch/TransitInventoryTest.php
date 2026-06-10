<?php

namespace Tests\Feature\Dispatch;

use App\Enums\VirtualWarehouseRole;
use App\Services\Dispatch\DeliveryNoteService;
use App\Services\Inventory\VirtualWarehouseResolverService;
use App\Support\InventoryStockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Dispatch\Concerns\InteractsWithDispatchInventory;
use Tests\TestCase;

class TransitInventoryTest extends TestCase
{
    use InteractsWithDispatchInventory;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedDispatchInventoryEnvironment();
    }

    public function test_transit_balances_visible_after_dispatch(): void
    {
        [$note, $finishedItem, $user, $jobCard] = $this->readyDispatchedDeliveryNote();
        $transit = app(VirtualWarehouseResolverService::class)->inTransit($jobCard->company_id);

        $this->assertEquals(5, InventoryStockService::getBalanceByVirtualRole(
            $finishedItem->id,
            $jobCard->company_id,
            VirtualWarehouseRole::InTransit,
        ));
        $this->assertEquals(5, InventoryStockService::balance($finishedItem->id, $transit->id));
    }

    public function test_fg_balance_reduced_after_dispatch(): void
    {
        [$note, $finishedItem, $user, $jobCard] = $this->readyDispatchedDeliveryNote();
        $fg = app(VirtualWarehouseResolverService::class)->finishedGoods($jobCard->company_id);

        $this->assertEquals(0, InventoryStockService::balance($finishedItem->id, $fg->id));
    }

    public function test_transit_report_lists_dispatched_stock(): void
    {
        [$note, $finishedItem, $user, $jobCard] = $this->readyDispatchedDeliveryNote();

        session(['active_company_id' => $jobCard->company_id, 'active_branch_id' => $jobCard->branch_id]);

        $this->actingAs($user)
            ->get(route('admin.dispatch.reports.transit-inventory'))
            ->assertOk()
            ->assertSee($finishedItem->sku, false)
            ->assertSee($note->delivery_note_number, false);
    }
}
