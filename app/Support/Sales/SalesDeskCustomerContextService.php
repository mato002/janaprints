<?php

namespace App\Support\Sales;

use App\Enums\ArtworkRequestStatus;
use App\Enums\ProductionJobCardStatus;
use App\Enums\QuotationStatus;
use App\Models\Artwork\ArtworkRequest;
use App\Models\Crm\Customer;
use App\Models\Crm\CustomerPrintSpecification;
use App\Models\Production\ProductionJobCard;
use App\Models\Sales\Quotation;
use App\Models\Sales\SalesOrder;
use App\Services\Crm\CustomerTimelineService;

class SalesDeskCustomerContextService
{
    public function __construct(
        protected CustomerOrderContextService $orderContext,
        protected CustomerFinancialIntelligenceService $financialIntelligence,
        protected CustomerTimelineService $timeline,
    ) {}

    /**
     * @return array<string, mixed>|null
     */
    public function present(?Customer $customer, ?CustomerPrintSpecification $specification = null): ?array
    {
        if (! $customer) {
            return null;
        }

        $recentOrders = $this->orderContext->previousOrders($customer, 5)
            ->map(fn (SalesOrder $order) => [
                'id' => $order->id,
                'key' => $order->getRouteKey(),
                'order_number' => $order->order_number,
                'status' => str_replace('_', ' ', $order->status->value),
                'total_amount' => number_format((float) $order->total_amount, 2),
                'order_date' => $order->order_date?->format('d M Y'),
                'product' => $order->inventoryItem?->item_name ?? $order->items->first()?->item_name,
                'desk_url' => route('admin.sales.desk', [
                    'customer' => $customer->getRouteKey(),
                    'order' => $order->getRouteKey(),
                    'step' => 4,
                ]),
                'repeat_url' => route('admin.crm.customers.repeat-order', [$customer, $order]),
            ])
            ->values()
            ->all();

        $openQuotes = $this->openQuotations($customer);
        $financial = $this->financialIntelligence->profile($customer);
        $frequentProducts = $this->orderContext->frequentlyOrderedProducts($customer, 5)->values()->all();
        $timeline = $this->recentTimeline($customer);
        $lastOrder = $recentOrders[0] ?? null;

        $openJobsCount = ProductionJobCard::query()
            ->where('customer_id', $customer->id)
            ->whereNotIn('status', [ProductionJobCardStatus::Completed, ProductionJobCardStatus::Cancelled])
            ->count();

        $artworkPendingCount = ArtworkRequest::query()
            ->where('customer_id', $customer->id)
            ->whereIn('status', [
                ArtworkRequestStatus::Requested,
                ArtworkRequestStatus::InDesign,
                ArtworkRequestStatus::Submitted,
                ArtworkRequestStatus::RevisionRequested,
            ])
            ->count();

        $warnings = $this->warnings($customer, $financial, $specification);

        return [
            'id' => $customer->id,
            'key' => $customer->getRouteKey(),
            'name' => $customer->name,
            'code' => $customer->customer_code,
            'phone' => $customer->phone,
            'email' => $customer->email,
            'contact_person' => $customer->contact_person,
            'customer_type' => $customer->customer_type?->label() ?? null,
            'credit_limit' => $customer->credit_limit > 0 ? number_format((float) $customer->credit_limit, 2) : null,
            'outstanding_balance' => $financial['outstanding'] > 0 ? number_format((float) $financial['outstanding'], 2) : null,
            'overdue_amount' => ($financial['overdue_amount'] ?? 0) > 0 ? number_format((float) $financial['overdue_amount'], 2) : null,
            'credit_balance' => ($financial['credit_balance'] ?? 0) > 0 ? number_format((float) $financial['credit_balance'], 2) : null,
            'collection_risk' => $financial['collection_risk'] ?? null,
            'last_payment' => $financial['last_payment'] ?? null,
            'open_jobs_count' => $openJobsCount,
            'open_quotes_count' => count($openQuotes),
            'artwork_pending_count' => $artworkPendingCount,
            'last_order' => $lastOrder,
            'recent_orders' => $recentOrders,
            'open_quotations' => $openQuotes,
            'frequent_products' => $frequentProducts,
            'timeline' => $timeline,
            'warnings' => $warnings,
            'edit_url' => route('admin.crm.customers.edit', [$customer, 'from' => 'sales-desk']),
            'show_url' => route('admin.crm.customers.show', $customer),
        ];
    }

    /**
     * @param  array<string, mixed>  $financial
     * @return list<array{severity: string, message: string}>
     */
    protected function warnings(Customer $customer, array $financial, ?CustomerPrintSpecification $specification): array
    {
        $warnings = [];

        $outstanding = (float) ($financial['outstanding'] ?? 0);
        $creditLimit = (float) $customer->credit_limit;

        if ($creditLimit > 0 && $outstanding > $creditLimit) {
            $warnings[] = [
                'severity' => 'danger',
                'message' => __('Customer exceeds credit limit.'),
            ];
        }

        if (($financial['overdue_amount'] ?? 0) > 0) {
            $warnings[] = [
                'severity' => 'warning',
                'message' => __('Previous invoice(s) overdue.'),
            ];
        }

        if ($specification) {
            $specification->loadMissing(['inventoryItem', 'activeArtworkVersion']);
            $product = $specification->inventoryItem;
            $artworkRequired = $product && $product->stock_role === \App\Enums\InventoryStockRole::FinishedGood;

            if ($artworkRequired && ! $specification->activeArtworkVersion) {
                $warnings[] = [
                    'severity' => 'warning',
                    'message' => __('Artwork not approved on selected specification.'),
                ];
            }
        }

        if (($financial['collection_risk'] ?? '') === 'high') {
            $warnings[] = [
                'severity' => 'warning',
                'message' => __('High collection risk profile.'),
            ];
        }

        return $warnings;
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function recentTimeline(Customer $customer): array
    {
        $page = $this->timeline->paginate($customer, null, null, 1);
        $events = $page['events'] ?? collect();

        return collect($events->items())
            ->take(5)
            ->map(fn ($event) => [
                'title' => $event->title,
                'description' => $event->description,
                'at' => $event->eventDatetime->format('d M Y H:i'),
                'url' => $event->sourceUrl,
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function openQuotations(Customer $customer): array
    {
        return Quotation::query()
            ->where('customer_id', $customer->id)
            ->whereIn('status', [
                QuotationStatus::Draft,
                QuotationStatus::Sent,
                QuotationStatus::Accepted,
            ])
            ->latest('quotation_date')
            ->limit(5)
            ->get(['id', 'quotation_number', 'status', 'total_amount', 'quotation_date'])
            ->map(fn (Quotation $quote) => [
                'id' => $quote->id,
                'quotation_number' => $quote->quotation_number,
                'status' => str_replace('_', ' ', $quote->status->value),
                'total_amount' => number_format((float) $quote->total_amount, 2),
                'create_url' => route('admin.quotations.show', [$quote, 'from' => 'sales-desk']),
            ])
            ->values()
            ->all();
    }
}
