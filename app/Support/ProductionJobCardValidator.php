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
        if (! in_array($salesOrder->status, [SalesOrderStatus::Confirmed, SalesOrderStatus::ReadyForProduction], true)) {
            throw ValidationException::withMessages([
                'sales_order' => __('Sales order must be confirmed or ready for production before creating a job card.'),
            ]);
        }

        if ($salesOrder->is_direct_order) {
            if (! $salesOrder->customer_id) {
                throw ValidationException::withMessages([
                    'sales_order' => __('Direct order is missing a customer.'),
                ]);
            }

            $artworkOk = ($salesOrder->uses_existing_artwork && $salesOrder->customer_artwork_id)
                || ($salesOrder->artworkRequest && $salesOrder->artworkRequest->status === ArtworkRequestStatus::Approved);

            if (! $artworkOk) {
                throw ValidationException::withMessages([
                    'artwork' => __('Artwork must be confirmed or approved before creating a job card.'),
                ]);
            }
        } else {
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
        }

        if ($salesOrder->jobCard()->exists()) {
            throw ValidationException::withMessages([
                'sales_order' => __('A job card already exists for this sales order.'),
            ]);
        }
    }
}
