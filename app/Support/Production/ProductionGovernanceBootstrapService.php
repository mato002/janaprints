<?php

namespace App\Support\Production;

use App\Models\Inventory\InventoryItem;
use App\Models\Production\ProductionJobCard;
use App\Models\Sales\SalesOrder;
use App\Services\Production\ProductionAutoSchedulingService;

class ProductionGovernanceBootstrapService
{
    public function __construct(
        protected ProductionRouteService $routes,
        protected SerialNumberGovernanceService $serials,
        protected RouteStepQueueService $routeQueues,
        protected ProductionAutoSchedulingService $autoScheduling,
        protected MaterialRequirementsService $materialRequirements,
    ) {}

    public function bootstrapFromSalesOrder(ProductionJobCard $jobCard, SalesOrder $salesOrder): void
    {
        $salesOrder->loadMissing('items');

        $jobCard->update([
            'required_date' => $salesOrder->required_date ?? $jobCard->planned_end_date,
        ]);

        $item = $this->resolveInventoryItem($salesOrder);

        if ($item) {
            $jobCard->update([
                'inventory_item_id' => $item->id,
                'customer_artwork_id' => $salesOrder->customer_artwork_id,
            ]);

            $this->routes->snapshotRouteToJobCard($jobCard, $item);
            $this->routeQueues->bootstrapQueuesForJobCard($jobCard->fresh());

            $quantity = (int) ceil((float) $salesOrder->items->sum('quantity'));
            if ($quantity <= 0) {
                $quantity = 1;
            }

            $this->serials->allocateForJobCard($jobCard, $item->fresh(), $quantity);
        } elseif ($salesOrder->customer_artwork_id) {
            $jobCard->update(['customer_artwork_id' => $salesOrder->customer_artwork_id]);
        }

        $this->autoScheduling->scheduleIfEnabled($jobCard->fresh(), (int) ($jobCard->created_by ?? 0));

        $this->materialRequirements->snapshotForJobCard($jobCard->fresh(), (int) ($jobCard->created_by ?? $salesOrder->created_by ?? 0));
    }

    protected function resolveInventoryItem(SalesOrder $salesOrder): ?InventoryItem
    {
        if ($salesOrder->inventory_item_id) {
            return InventoryItem::query()->find($salesOrder->inventory_item_id);
        }

        $salesOrder->loadMissing('items');

        return null;
    }
}
