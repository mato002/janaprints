<?php

namespace App\Support\Sales;

use App\Models\Sales\SalesOrder;
use App\Support\Platform\SystemSettingsService;

/**
 * Company/branch switch for the final-invoice production and fulfilment gate.
 *
 * When on, standard and partial invoices need posted finished goods or
 * collection/delivery. When off, those steps stay optional and do not block billing.
 */
class InvoiceBillingControlSettings
{
    public const KEY = 'invoices_require_production_or_fulfilment';

    public function __construct(
        protected SystemSettingsService $settings,
    ) {}

    public function productionOrFulfilmentRequired(?int $companyId, ?int $branchId = null): bool
    {
        if ($companyId === null) {
            return true;
        }

        return filter_var(
            $this->settings->get(
                self::KEY,
                true,
                $companyId,
                $branchId,
            ),
            FILTER_VALIDATE_BOOLEAN,
        );
    }

    public function requiredForOrder(SalesOrder $order): bool
    {
        return $this->productionOrFulfilmentRequired($order->company_id, $order->branch_id);
    }
}
