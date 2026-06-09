<?php

namespace App\Listeners\Dispatch;

use App\Events\Dispatch\DeliveryNoteDelivered;
use App\Support\Production\SalesOrderProductionBridgeService;

class SyncSalesOrderFromDeliveryNote
{
    public function __construct(
        protected SalesOrderProductionBridgeService $bridge,
    ) {}

    public function handle(DeliveryNoteDelivered $event): void
    {
        $this->bridge->syncSalesOrderFromDelivery($event->deliveryNote);
    }
}
