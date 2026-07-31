<?php

namespace App\Support\Sales;

use App\Models\Sales\SalesOrder;
use App\Services\Production\ProductionReleaseReadinessService;

class SalesDeskActionPresenter
{
    public function __construct(
        protected ProductionReleaseReadinessService $releaseReadiness,
        protected SalesOrderWorkflowService $workflow,
        protected SalesOrderFinancialStatusService $financialStatus,
        protected SalesDeskProductionHandoffService $productionHandoff,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function presentOrder(SalesOrder $salesOrder): array
    {
        $salesOrder->loadMissing([
            'jobCard.queues.workCenter',
            'jobCard.productionSpecification',
            'customer:id,company_name,contact_person,customer_code,phone,email',
            'invoices:id,sales_order_id,invoice_number,status,total_amount,balance_due',
            'inventoryItem:id,item_name,sku',
            'items:id,sales_order_id,item_name,quantity,unit_price',
            'customerPrintSpecification:id,name,specification_code',
        ]);

        $readiness = $this->releaseReadiness->assess($salesOrder);
        $financial = $this->financialStatus->snapshot($salesOrder);
        $customer = $salesOrder->customer;
        $latestInvoice = $salesOrder->invoices->sortByDesc('id')->first();
        $line = $salesOrder->items->first();
        $quantity = $line?->quantity;

        return [
            'id' => $salesOrder->id,
            'order_number' => $salesOrder->order_number,
            'status' => $salesOrder->status->value,
            'status_label' => str_replace('_', ' ', ucfirst($salesOrder->status->value)),
            'total_amount' => number_format((float) $salesOrder->total_amount, 2),
            'quantity' => $quantity !== null ? rtrim(rtrim(number_format((float) $quantity, 3, '.', ','), '0'), '.') : null,
            'unit_price' => $line?->unit_price !== null
                ? number_format((float) $line->unit_price, 2)
                : null,
            'product_name' => $salesOrder->inventoryItem?->item_name
                ?? $line?->item_name
                ?? $salesOrder->customerPrintSpecification?->name,
            'specification_name' => $salesOrder->customerPrintSpecification?->name,
            'required_date' => ($salesOrder->required_date ?? null)?->format('d M Y'),
            'priority' => $salesOrder->priority?->value
                ? ucfirst($salesOrder->priority->value)
                : null,
            'customer' => $customer ? [
                'id' => $customer->id,
                'key' => $customer->getRouteKey(),
                'label' => $customer->name,
                'code' => $customer->customer_code,
                'phone' => $customer->phone,
                'email' => $customer->email,
            ] : null,
            'can_release' => $this->workflow->canRelease($salesOrder),
            'needs_production_queue' => $salesOrder->jobCard?->status === \App\Enums\ProductionJobCardStatus::Draft,
            'released_to_queue' => $salesOrder->jobCard !== null
                && $salesOrder->jobCard->status !== \App\Enums\ProductionJobCardStatus::Draft,
            'readiness' => $readiness,
            'financial' => $financial,
            'job_card_id' => $salesOrder->jobCard?->id,
            'job_card_number' => $salesOrder->jobCard?->job_card_number,
            'production' => $this->productionHandoff->present($salesOrder->jobCard),
            'latest_invoice' => $latestInvoice ? [
                'id' => $latestInvoice->id,
                'invoice_number' => $latestInvoice->invoice_number,
                'status' => str_replace('_', ' ', $latestInvoice->status->value),
                'total_amount' => number_format((float) $latestInvoice->total_amount, 2),
                'show_url' => route('admin.invoices.show', [$latestInvoice, 'from' => 'sales-desk']),
                'document_url' => route('admin.invoices.document', $latestInvoice),
            ] : null,
            'show_url' => route('admin.sales-orders.show', [$salesOrder, 'from' => 'sales-desk']),
            'edit_url' => route('admin.sales-orders.edit', [$salesOrder, 'from' => 'sales-desk']),
            'job_url' => $salesOrder->jobCard
                ? route('admin.production.job-cards.show', [$salesOrder->jobCard, 'from' => 'sales-desk'])
                : null,
            'payment_url' => $customer && auth()->user()?->can('create', \App\Models\Sales\CustomerPayment::class)
                ? route('admin.payments.create', [
                    'from' => 'sales-desk',
                    'customer_id' => $customer->id,
                    'sales_order_id' => $salesOrder->id,
                ])
                : null,
            'invoice_url' => auth()->user()?->can('create', \App\Models\Sales\CustomerInvoice::class)
                ? route('admin.invoices.from-sales-order', [
                    'salesOrder' => $salesOrder,
                    'from' => 'sales-desk',
                ])
                : null,
            'quotation_url' => $customer && auth()->user()?->can('create', \App\Models\Sales\Quotation::class)
                ? route('admin.quotations.create', [
                    'from' => 'sales-desk',
                    'customer_id' => $customer->id,
                ])
                : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function deskUrls(?\App\Models\Crm\Customer $customer, ?\App\Models\Crm\CustomerPrintSpecification $specification = null): array
    {
        $customerKey = $customer?->getRouteKey();

        return [
            'create_specification' => $customer
                ? route('admin.crm.customers.print-specifications.create', [$customer, 'from' => 'sales-desk'])
                : null,
            'edit_customer' => $customer
                ? route('admin.crm.customers.edit', [$customer, 'from' => 'sales-desk'])
                : null,
            'customer_360' => $customer
                ? route('admin.crm.customers.show', $customer)
                : null,
            'artwork_request' => $customer && auth()->user()?->can('create', \App\Models\Artwork\ArtworkRequest::class)
                ? route('admin.artwork.create', array_filter([
                    'from' => 'sales-desk',
                    'customer_id' => $customer->id,
                    'customer_print_specification_id' => $specification?->id,
                ]))
                : null,
            'quotation' => $customer && auth()->user()?->can('create', \App\Models\Sales\Quotation::class)
                ? route('admin.quotations.create', [
                    'from' => 'sales-desk',
                    'customer_id' => $customer->id,
                ])
                : null,
            'create_order' => $customer && auth()->user()?->can('create', SalesOrder::class)
                ? route('admin.sales-orders.create', array_filter([
                    'from' => 'sales-desk',
                    'tab' => 'direct',
                    'customer_id' => $customer->id,
                    'print_specification_id' => $specification?->id,
                ]))
                : null,
            'public_quote_requests' => auth()->user()?->can('viewAny', \App\Models\PublicQuoteRequest::class)
                ? route('admin.public-quote-requests.index')
                : null,
            'quotations_index' => route('admin.quotations.index'),
            'select_customer' => $customerKey
                ? route('admin.sales.desk', ['customer' => $customerKey, 'step' => 2])
                : null,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function fastActions(?\App\Models\Crm\Customer $customer, ?\App\Models\Crm\CustomerPrintSpecification $specification = null, ?SalesOrder $order = null): array
    {
        $user = auth()->user();
        $actions = [];

        if ($user?->can('create', \App\Models\Sales\Quotation::class)) {
            $actions[] = [
                'key' => 'new_quote',
                'label' => __('New quote'),
                'url' => $customer
                    ? route('admin.quotations.create', ['from' => 'sales-desk', 'customer_id' => $customer->id])
                    : route('admin.quotations.create', ['from' => 'sales-desk']),
                'modal' => true,
            ];
        }

        if ($customer && $user?->can('create', SalesOrder::class)) {
            $actions[] = [
                'key' => 'new_order',
                'label' => __('New order'),
                'url' => route('admin.sales-orders.create', array_filter([
                    'from' => 'sales-desk',
                    'tab' => 'direct',
                    'customer_id' => $customer->id,
                    'print_specification_id' => $specification?->id,
                ])),
                'modal' => true,
            ];
        }

        $actions[] = [
            'key' => 'walk_in',
            'label' => __('Record walk-in'),
            'url' => route('admin.sales.desk', ['step' => 1]),
            'modal' => false,
        ];

        if ($user?->can('viewAny', \App\Models\PublicQuoteRequest::class)) {
            $actions[] = [
                'key' => 'quote_requests',
                'label' => __('Quote requests'),
                'url' => route('admin.public-quote-requests.index'),
                'modal' => false,
            ];
        }

        if ($customer) {
            $actions[] = [
                'key' => 'customer_360',
                'label' => __('Customer 360'),
                'url' => route('admin.crm.customers.show', $customer),
                'modal' => false,
            ];
        }

        if ($order && $user?->can('view', $order)) {
            $actions[] = [
                'key' => 'open_order',
                'label' => __('Open order'),
                'url' => route('admin.sales-orders.show', [$order, 'from' => 'sales-desk']),
                'modal' => true,
            ];
        }

        return $actions;
    }
}
