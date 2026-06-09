<?php

namespace App\Listeners\Production;

use App\Events\Production\JobCardStatusChanged;
use App\Support\Production\SalesOrderProductionBridgeService;

class SyncSalesOrderFromJobCard
{
    public function __construct(
        protected SalesOrderProductionBridgeService $bridge,
    ) {}

    public function handle(JobCardStatusChanged $event): void
    {
        $this->bridge->syncSalesOrderStatus($event->jobCard, $event->status);
    }
}
