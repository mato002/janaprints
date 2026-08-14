<?php

namespace App\Support\Sales;

use App\Enums\CustomerInvoiceStatus;
use App\Enums\QuotationStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\CustomerPayment;
use App\Models\Sales\Quotation;
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
            'edit_url' => auth()->user()?->can('update', $salesOrder)
                ? route('admin.sales-orders.edit', [$salesOrder, 'from' => 'sales-desk'])
                : null,
            'job_url' => $salesOrder->jobCard && auth()->user()?->can('view', $salesOrder->jobCard)
                ? route('admin.production.job-cards.show', [
                    $salesOrder->jobCard,
                    'tab' => 'materials',
                ])
                : null,
            'materials_handoff_url' => route('admin.sales.desk.materials', $salesOrder),
            'resume_url' => $customer
                ? route('admin.sales.desk', [
                    'customer' => $customer->getRouteKey(),
                    'order' => $salesOrder->getRouteKey(),
                    'step' => 4,
                ])
                : null,
            'park_url' => route('admin.sales.desk.park', $salesOrder),
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

    /**
     * Status-aware register actions the current user may take on this order.
     *
     * @return list<array{
     *     key: string,
     *     label: string,
     *     href?: string,
     *     action?: string,
     *     method?: string,
     *     confirm?: string|null,
     *     variant?: string,
     *     modal?: bool,
     *     new_tab?: bool
     * }>
     */
    public function rowActions(SalesOrder $salesOrder): array
    {
        $user = auth()->user();

        if (! $user?->can('view', $salesOrder)) {
            return [];
        }

        $salesOrder->loadMissing(['jobCard', 'invoices', 'customer']);

        $workflow = $this->workflow->present($salesOrder);
        $from = ['from' => 'sales-desk'];
        $actions = [];
        $notDraftOrCancelled = ! in_array($salesOrder->status, [
            SalesOrderStatus::Draft,
            SalesOrderStatus::Cancelled,
        ], true);

        $actions[] = [
            'key' => 'view',
            'label' => __('View'),
            'href' => route('admin.sales-orders.show', [$salesOrder, ...$from]),
        ];

        if (($workflow['can_confirm'] ?? false) && $user->can('confirm', $salesOrder)) {
            $actions[] = [
                'key' => 'confirm',
                'label' => __('Confirm order'),
                'action' => route('admin.sales-orders.confirm', $salesOrder),
                'method' => 'POST',
                'confirm' => __('Confirm this sales order?'),
            ];
        }

        if (($workflow['can_release'] ?? false) && $user->can('production', $salesOrder)) {
            $actions[] = [
                'key' => 'release',
                'label' => $salesOrder->production_destination?->sendToLabel() ?? __('Send to production'),
                'action' => route('admin.sales-orders.release-to-production', $salesOrder),
                'method' => 'POST',
                'confirm' => __('Send this order to production?'),
            ];
        }

        if ($user->can('update', $salesOrder)) {
            $actions[] = [
                'key' => 'edit',
                'label' => __('Edit'),
                'href' => route('admin.sales-orders.edit', [$salesOrder, ...$from]),
            ];
        }

        if ($user->can('create', CustomerInvoice::class)
            && $notDraftOrCancelled
            && $this->remainingInvoiceable($salesOrder) > 0
        ) {
            $actions[] = [
                'key' => 'invoice',
                'label' => __('Generate invoice'),
                'href' => route('admin.invoices.from-sales-order', ['salesOrder' => $salesOrder, ...$from]),
                'modal' => true,
            ];
        }

        $latestInvoice = $salesOrder->invoices->sortByDesc('id')->first();
        if ($latestInvoice && $user->can('view', $latestInvoice)) {
            $actions[] = [
                'key' => 'view_invoice',
                'label' => __('View invoice'),
                'href' => route('admin.invoices.show', [$latestInvoice, ...$from]),
            ];
        }

        $customer = $salesOrder->customer;
        if ($customer && $notDraftOrCancelled && $user->can('create', CustomerPayment::class)) {
            $actions[] = [
                'key' => 'payment',
                'label' => __('Record payment'),
                'href' => route('admin.payments.create', [
                    ...$from,
                    'customer_id' => $customer->id,
                    'sales_order_id' => $salesOrder->id,
                ]),
            ];
        }

        $jobCard = $salesOrder->jobCard;
        if ($jobCard && $user->can('view', $jobCard)) {
            $actions[] = [
                'key' => 'job_card',
                'label' => __('Open job card'),
                'href' => route('admin.production.job-cards.show', $jobCard),
            ];
            $actions[] = [
                'key' => 'dispatch',
                'label' => __('Delivery / dispatch'),
                'href' => route('admin.production.job-cards.show', [$jobCard, 'tab' => 'dispatch']),
            ];
        }

        $actions[] = [
            'key' => 'print',
            'label' => __('Print specifications'),
            'href' => route('admin.sales-orders.specifications.print', $salesOrder),
            'new_tab' => true,
        ];

        if ($customer && $user->can('create', SalesOrder::class) && $salesOrder->status !== SalesOrderStatus::Draft) {
            $actions[] = [
                'key' => 'repeat',
                'label' => __('Repeat order'),
                'action' => route('admin.crm.customers.repeat-order', [$customer, $salesOrder]),
                'method' => 'POST',
                'confirm' => __('Create a new order from this one?'),
            ];
        }

        $canTransition = $user->can('transition', $salesOrder);

        if ($canTransition && $salesOrder->status->canTransitionTo(SalesOrderStatus::OnHold)) {
            $actions[] = [
                'key' => 'hold',
                'label' => __('On hold'),
                'action' => route('admin.sales-orders.hold', $salesOrder),
                'method' => 'POST',
                'confirm' => __('Put this order on hold?'),
            ];
        }

        if ($salesOrder->status === SalesOrderStatus::OnHold && $canTransition) {
            $actions[] = [
                'key' => 'resume',
                'label' => __('Resume'),
                'action' => route('admin.sales-orders.resume', $salesOrder),
                'method' => 'POST',
            ];
        }

        if (($workflow['can_close'] ?? false) && $user->can('close', $salesOrder)) {
            $actions[] = [
                'key' => 'close',
                'label' => __('Close order'),
                'action' => route('admin.sales-orders.close', $salesOrder),
                'method' => 'POST',
                'confirm' => __('Close this sales order?'),
            ];
        }

        if ($canTransition && $salesOrder->status->canTransitionTo(SalesOrderStatus::Cancelled)) {
            $actions[] = [
                'key' => 'cancel',
                'label' => __('Cancel'),
                'action' => route('admin.sales-orders.cancel', $salesOrder),
                'method' => 'POST',
                'confirm' => __('Cancel this sales order?'),
                'variant' => 'danger',
            ];
        }

        if ($user->can('delete', $salesOrder)) {
            $actions[] = [
                'key' => 'delete',
                'label' => __('Delete'),
                'action' => route('admin.sales-orders.destroy', $salesOrder),
                'method' => 'DELETE',
                'confirm' => __('Delete this draft sales order?'),
                'variant' => 'danger',
            ];
        }

        return $actions;
    }

    /**
     * Status-aware register actions the current user may take on this quotation.
     *
     * @return list<array{
     *     key: string,
     *     label: string,
     *     href?: string,
     *     action?: string,
     *     method?: string,
     *     confirm?: string|null,
     *     variant?: string,
     *     modal?: bool,
     *     new_tab?: bool
     * }>
     */
    public function quotationRowActions(Quotation $quotation): array
    {
        $user = auth()->user();

        if (! $user?->can('view', $quotation)) {
            return [];
        }

        $quotation->loadMissing(['customer', 'salesOrder']);

        $from = request()->routeIs('admin.sales.desk') || request('from') === 'sales-desk' || request('view') === SalesDeskViews::QUOTES
            ? ['from' => 'sales-desk']
            : [];
        $status = $quotation->status;
        $canTransition = $user->can('transition', $quotation);
        $actions = [];

        $actions[] = [
            'key' => 'view',
            'label' => __('View'),
            'href' => route('admin.quotations.show', [$quotation, ...$from]),
        ];

        if ($user->can('update', $quotation)) {
            $actions[] = [
                'key' => 'edit',
                'label' => __('Edit'),
                'href' => route('admin.quotations.edit', [$quotation, ...$from]),
                'modal' => true,
            ];
        }

        if ($canTransition && $status->canTransitionTo(QuotationStatus::PendingApproval)) {
            $actions[] = [
                'key' => 'submit_approval',
                'label' => __('Submit for approval'),
                'action' => route('admin.quotations.submit-approval', $quotation),
                'method' => 'POST',
                'confirm' => __('Submit this quotation for approval?'),
            ];
        }

        if ($user->can('approve', $quotation)) {
            $actions[] = [
                'key' => 'approve',
                'label' => __('Approve & send'),
                'action' => route('admin.quotations.approve', $quotation),
                'method' => 'POST',
                'confirm' => __('Approve this quotation and send it to the customer?'),
            ];
        }

        if ($user->can('send', $quotation) && $status->canTransitionTo(QuotationStatus::Sent)) {
            $actions[] = [
                'key' => 'send',
                'label' => __('Send'),
                'action' => route('admin.quotations.send', $quotation),
                'method' => 'POST',
                'confirm' => __('Mark this quotation as sent to the customer?'),
            ];
        }

        if ($canTransition && $status->canTransitionTo(QuotationStatus::Viewed)) {
            $actions[] = [
                'key' => 'mark_viewed',
                'label' => __('Mark viewed'),
                'action' => route('admin.quotations.mark-viewed', $quotation),
                'method' => 'POST',
            ];
        }

        if ($canTransition && $status->canTransitionTo(QuotationStatus::Accepted)) {
            $actions[] = [
                'key' => 'accept',
                'label' => __('Accept'),
                'action' => route('admin.quotations.accept', $quotation),
                'method' => 'POST',
                'confirm' => __('Mark this quotation as accepted by the customer?'),
            ];
        }

        if ($user->can('convert', $quotation) && $status === QuotationStatus::Accepted) {
            $actions[] = [
                'key' => 'convert',
                'label' => __('Convert to sales order'),
                'href' => route('admin.sales-orders.create', [
                    'quotation_id' => $quotation->id,
                    'tab' => 'quotation',
                    'customer_id' => $quotation->customer_id,
                    ...$from,
                ]),
                'modal' => true,
            ];
            $actions[] = [
                'key' => 'quick_convert',
                'label' => __('Quick convert'),
                'action' => route('admin.quotations.convert', $quotation),
                'method' => 'POST',
                'confirm' => __('Convert this quotation to a sales order now?'),
            ];
        }

        if ($canTransition && $status->canTransitionTo(QuotationStatus::Rejected)) {
            $actions[] = [
                'key' => 'reject',
                'label' => __('Reject'),
                'action' => route('admin.quotations.reject', $quotation),
                'method' => 'POST',
                'confirm' => __('Reject this quotation?'),
                'variant' => 'danger',
            ];
        }

        if ($canTransition && $status->canTransitionTo(QuotationStatus::Expired)) {
            $actions[] = [
                'key' => 'expire',
                'label' => __('Mark expired'),
                'action' => route('admin.quotations.expire', $quotation),
                'method' => 'POST',
                'confirm' => __('Mark this quotation as expired?'),
                'variant' => 'danger',
            ];
        }

        $salesOrder = $quotation->salesOrder;
        if ($salesOrder && $user->can('view', $salesOrder)) {
            $actions[] = [
                'key' => 'view_sales_order',
                'label' => __('View sales order'),
                'href' => route('admin.sales-orders.show', [$salesOrder, ...$from]),
            ];
        }

        $actions[] = [
            'key' => 'document',
            'label' => __('View document'),
            'href' => route('admin.quotations.document', $quotation),
            'new_tab' => true,
        ];
        $actions[] = [
            'key' => 'pdf',
            'label' => __('Download PDF'),
            'href' => route('admin.quotations.document.pdf', $quotation),
            'new_tab' => true,
        ];

        if ($user->can('delete', $quotation)) {
            $actions[] = [
                'key' => 'delete',
                'label' => __('Delete'),
                'action' => route('admin.quotations.destroy', $quotation),
                'method' => 'DELETE',
                'confirm' => __('Delete this draft quotation?'),
                'variant' => 'danger',
            ];
        }

        return $actions;
    }

    protected function remainingInvoiceable(SalesOrder $salesOrder): float
    {
        if (! $salesOrder->relationLoaded('invoices')) {
            return $salesOrder->remainingInvoiceTotal();
        }

        $pending = (float) $salesOrder->invoices
            ->filter(fn (CustomerInvoice $invoice) => in_array($invoice->status, [
                CustomerInvoiceStatus::Draft,
                CustomerInvoiceStatus::Approved,
            ], true))
            ->sum('total_amount');

        return round(max(0, (float) $salesOrder->total_amount - (float) $salesOrder->invoiced_total - $pending), 2);
    }
}
