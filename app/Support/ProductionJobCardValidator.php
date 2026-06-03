<?php

namespace App\Support;

use App\Enums\ArtworkRequestStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Sales\SalesOrder;
use Illuminate\Validation\ValidationException;

class ProductionJobCardValidator
{
    public static function assertCanCreateFromSalesOrder(SalesOrder $salesOrder): void
    {
        if ($salesOrder->status !== SalesOrderStatus::Confirmed) {
            throw ValidationException::withMessages([
                'sales_order' => __('Sales order must be confirmed before creating a job card.'),
            ]);
        }

        if (! $salesOrder->customer_id || ! $salesOrder->quotation_id || ! $salesOrder->artwork_request_id) {
            throw ValidationException::withMessages([
                'sales_order' => __('Sales order is missing required traceability links.'),
            ]);
        }

        $artwork = $salesOrder->artworkRequest;

        if (! $artwork || $artwork->status !== ArtworkRequestStatus::Approved) {
            throw ValidationException::withMessages([
                'artwork' => __('Artwork must be approved before creating a job card.'),
            ]);
        }

        if ($salesOrder->jobCard()->exists()) {
            throw ValidationException::withMessages([
                'sales_order' => __('A job card already exists for this sales order.'),
            ]);
        }
    }
}
