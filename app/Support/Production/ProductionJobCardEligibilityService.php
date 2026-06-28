<?php

namespace App\Support\Production;

use App\Enums\ArtworkRequestStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Sales\SalesOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ProductionJobCardEligibilityService
{
    /**
     * @return Builder<SalesOrder>
     */
    public function eligibleSalesOrdersQuery(): Builder
    {
        return SalesOrder::query()
            ->forTenant()
            ->whereIn('status', [
                SalesOrderStatus::Confirmed,
                SalesOrderStatus::ReadyForProduction,
            ])
            ->whereDoesntHave('jobCard')
            ->whereNotNull('customer_id')
            ->where(function (Builder $query) {
                $query->where(function (Builder $standard) {
                    $standard->whereNotNull('quotation_id')
                        ->whereNotNull('artwork_request_id')
                        ->whereHas('artworkRequest', fn (Builder $artwork) => $artwork->where(
                            'status',
                            ArtworkRequestStatus::Approved,
                        ));
                })->orWhere(function (Builder $direct) {
                    $direct->where('is_direct_order', true)
                        ->where(function (Builder $artworkPath) {
                            $artworkPath->where(function (Builder $library) {
                                $library->where('uses_existing_artwork', true)
                                    ->whereNotNull('customer_artwork_id');
                            })->orWhereHas('artworkRequest', fn (Builder $artwork) => $artwork->where(
                                'status',
                                ArtworkRequestStatus::Approved,
                            ));
                        });
                });
            });
    }

    /**
     * @return Collection<int, SalesOrder>
     */
    public function eligibleSalesOrders(): Collection
    {
        return $this->eligibleSalesOrdersQuery()
            ->with(['customer', 'artworkRequest'])
            ->orderByDesc('order_date')
            ->get();
    }
}
