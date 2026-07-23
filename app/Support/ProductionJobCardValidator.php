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
                'sales_order_id' => __('Sales order must be confirmed or ready for production before creating a job card.'),
            ]);
        }

        if ($salesOrder->is_direct_order) {
            if (! $salesOrder->customer_id) {
                throw ValidationException::withMessages([
                    'sales_order_id' => __('Direct order is missing a customer.'),
                ]);
            }

            $salesOrder->loadMissing('inventoryItem');

            $artworkOk = ($salesOrder->uses_existing_artwork && $salesOrder->customer_artwork_id)
                || ($salesOrder->artworkRequest && $salesOrder->artworkRequest->status === ArtworkRequestStatus::Approved);

            if (! $artworkOk) {
                $requiresArtwork = app(\App\Support\Sales\DirectCustomerSalesOrderService::class)
                    ->productRequiresArtwork($salesOrder->inventoryItem);

                if ($requiresArtwork) {
                    throw ValidationException::withMessages([
                        'sales_order_id' => __('This specification has no active artwork version. Job card cannot be created.'),
                    ]);
                }
            }
        } else {
            if (! $salesOrder->customer_id || ! $salesOrder->quotation_id) {
                throw ValidationException::withMessages([
                    'sales_order_id' => __('Sales order is missing required traceability links.'),
                ]);
            }

            if ($salesOrder->artwork_request_id) {
                $artwork = $salesOrder->artworkRequest;

                if (! $artwork || $artwork->status !== ArtworkRequestStatus::Approved) {
                    throw ValidationException::withMessages([
                        'sales_order_id' => __('Artwork must be approved before creating a job card.'),
                    ]);
                }
            }
        }

        if ($salesOrder->jobCard()->exists()) {
            throw ValidationException::withMessages([
                'sales_order_id' => __('A job card already exists for this sales order.'),
            ]);
        }
    }
}
