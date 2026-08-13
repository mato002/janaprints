<?php

namespace App\Support\Production;

use App\Enums\ArtworkRequestStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Sales\SalesOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;

class ProductionJobCardEligibilityService
{
    /**
     * Sales orders that can still receive a manual job card.
     *
     * Released orders that already have a job card are excluded — that is the normal
     * Sales → Production handoff. This list is only for Confirmed/Ready orders that
     * somehow still need a job created from Production.
     *
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
                // Approved artwork request (quotation optional — many direct/confirmed orders have artwork only).
                $query->where(function (Builder $artworkApproved) {
                    $artworkApproved->whereNotNull('artwork_request_id')
                        ->whereHas('artworkRequest', fn (Builder $artwork) => $artwork->where(
                            'status',
                            ArtworkRequestStatus::Approved,
                        ));
                })->orWhere(function (Builder $directLibrary) {
                    $directLibrary->where('is_direct_order', true)
                        ->where('uses_existing_artwork', true)
                        ->whereNotNull('customer_artwork_id');
                })->orWhere(function (Builder $legacyQuoted) {
                    // Quoted path without a separate artwork request row yet — keep for older data.
                    $legacyQuoted->whereNotNull('quotation_id')
                        ->whereNull('artwork_request_id')
                        ->where('is_direct_order', false);
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

    /**
     * @return list<array{value: int|string, label: string}>
     */
    public function eligibleSalesOrderOptions(): array
    {
        return $this->eligibleSalesOrders()
            ->map(fn (SalesOrder $order) => [
                'value' => $order->id,
                'label' => trim($order->order_number.' — '.($order->customer?->company_name ?? '')),
            ])
            ->values()
            ->all();
    }

    /**
     * Counts useful for empty-state messaging on the create form.
     *
     * @return array{ready_without_job: int, already_have_job: int, blocked_artwork: int}
     */
    public function eligibilitySummary(): array
    {
        $base = $this->confirmedReadyBaseQuery();

        $alreadyHaveJob = (clone $base)->whereHas('jobCard')->count();
        $withoutJob = (clone $base)->whereDoesntHave('jobCard')->count();
        $readyWithoutJob = $this->eligibleSalesOrdersQuery()->count();

        return [
            'ready_without_job' => $readyWithoutJob,
            'already_have_job' => $alreadyHaveJob,
            'blocked_artwork' => max(0, $withoutJob - $readyWithoutJob),
        ];
    }

    /**
     * In-context actions so Production can unblock without leaving New job card.
     *
     * @return array{
     *     summary: array{ready_without_job: int, already_have_job: int, blocked_artwork: int},
     *     already_have_job: list<array{id: int, label: string, job_label: string, job_url: string|null}>,
     *     blocked_artwork: list<array{id: int, label: string, resolve_url: string|null, resolve_label: string}>
     * }
     */
    public function resolutionContext(int $limit = 5): array
    {
        $summary = $this->eligibilitySummary();

        $alreadyHaveJob = $this->confirmedReadyBaseQuery()
            ->whereHas('jobCard')
            ->with(['customer:id,company_name', 'jobCard:id,public_id,job_card_number,sales_order_id'])
            ->orderByDesc('order_date')
            ->limit($limit)
            ->get()
            ->map(function (SalesOrder $order) {
                $job = $order->jobCard;

                return [
                    'id' => $order->id,
                    'label' => trim($order->order_number.' — '.($order->customer?->company_name ?? '')),
                    'job_label' => $job?->job_card_number ?? __('Job card'),
                    'job_url' => $job && Route::has('admin.production.job-cards.show')
                        ? route('admin.production.job-cards.show', $job)
                        : null,
                ];
            })
            ->values()
            ->all();

        $eligibleIds = $this->eligibleSalesOrdersQuery()->pluck('id');

        $blockedArtwork = $this->confirmedReadyBaseQuery()
            ->whereDoesntHave('jobCard')
            ->when($eligibleIds->isNotEmpty(), fn (Builder $query) => $query->whereNotIn('id', $eligibleIds))
            ->with(['customer:id,company_name', 'artworkRequest:id,public_id,status'])
            ->orderByDesc('order_date')
            ->limit($limit)
            ->get()
            ->map(function (SalesOrder $order) {
                $artworkUrl = $order->artwork_request_id
                    && $order->artworkRequest
                    && Route::has('admin.artwork.show')
                        ? route('admin.artwork.show', $order->artworkRequest)
                        : null;

                $orderUrl = Route::has('admin.sales-orders.show')
                    ? route('admin.sales-orders.show', $order)
                    : null;

                return [
                    'id' => $order->id,
                    'label' => trim($order->order_number.' — '.($order->customer?->company_name ?? '')),
                    'resolve_url' => $artworkUrl ?: $orderUrl,
                    'resolve_label' => $artworkUrl
                        ? __('Approve artwork')
                        : __('Open sales order'),
                ];
            })
            ->values()
            ->all();

        return [
            'summary' => $summary,
            'already_have_job' => $alreadyHaveJob,
            'blocked_artwork' => $blockedArtwork,
        ];
    }

    /**
     * @return Builder<SalesOrder>
     */
    protected function confirmedReadyBaseQuery(): Builder
    {
        return SalesOrder::query()
            ->forTenant()
            ->whereIn('status', [
                SalesOrderStatus::Confirmed,
                SalesOrderStatus::ReadyForProduction,
            ])
            ->whereNotNull('customer_id');
    }
}
