<?php

namespace App\Support\Documents\Presenters;

use App\Enums\CustomerInvoiceStatus;
use App\Enums\Dispatch\DeliveryNoteStatus;
use App\Enums\ProductionJobCardStatus;
use App\Models\Artwork\ArtworkRequest;
use App\Models\Dispatch\DeliveryNote;
use App\Models\Sales\CustomerInvoice;
use App\Support\Documents\Presenters\Concerns\BuildsDocumentBlocks;
use App\Support\Platform\FormCustomFieldService;
use Carbon\Carbon;

class InvoiceDocumentPresenter
{
    use BuildsDocumentBlocks;

    public function __construct(
        protected FormCustomFieldService $customFields,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function present(CustomerInvoice $invoice): array
    {
        $invoice->loadMissing([
            'customer',
            'company',
            'branch',
            'salesOrder.artworkRequest',
            'jobCard.artworkRequest',
            'lines',
            'paymentAllocations.payment',
        ]);

        $invoiceCustom = $this->customFields->valuesFor($invoice, 'customer_invoice');

        $currency = $invoice->currency ?: 'KES';
        $paymentState = $this->paymentState($invoice);
        [$statusLabel, $statusVariant] = $this->statusPresentation($invoice, $paymentState);

        $balanceDue = $invoice->status === CustomerInvoiceStatus::Posted
            ? max(0, (float) $invoice->balance_due)
            : (float) $invoice->total_amount;

        return [
            'logoDataUri' => $this->documentsLogoDataUri(),
            'documentType' => 'invoice',
            'title' => __('INVOICE'),
            'documentNumber' => $invoice->invoice_number,
            'documentNumberLabel' => __('No.'),
            'currency' => $currency,
            'headerHighlight' => $this->headerHighlightBlock(
                __('Balance Due'),
                $this->formatMoney($balanceDue, $currency),
            ),
            'status' => [
                'label' => $statusLabel,
                'variant' => $statusVariant,
            ],
            'paymentState' => $paymentState,
            'dates' => $this->filterMetaRows([
                ['label' => __('Invoice Date'), 'value' => $invoice->invoice_date?->format('d M Y')],
                ['label' => __('Terms'), 'value' => $invoiceCustom['payment_terms'] ?? __('Due on Receipt')],
                ['label' => __('Due Date'), 'value' => $invoice->due_date?->format('d M Y')],
            ]),
            'company' => $this->companyBlock($invoice->company),
            'customer' => $this->customerBlock($invoice->customer, compact: true),
            'customerLabel' => __('Bill To'),
            'meta' => [],
            'summary' => $this->presentSummary($invoice, $currency, $paymentState),
            'columns' => $this->itemColumns(),
            'items' => $this->presentItems($invoice, $currency),
            'totals' => $this->presentTotals($invoice, $currency),
            'paymentFooter' => $this->paymentFooterBlock($invoice->company_id),
            'documentFooter' => $this->documentFooterBlock($invoice->company_id),
            'notesTerms' => [
                'title' => __('Notes'),
                'body' => $this->resolveNotesTerms($invoice, $invoiceCustom),
            ],
        ];
    }

    /**
     * @return array{label: string, variant: string, overdueDays: ?int}
     */
    protected function paymentState(CustomerInvoice $invoice): array
    {
        if ($invoice->status === CustomerInvoiceStatus::Cancelled) {
            return ['label' => __('Cancelled'), 'variant' => 'warning', 'overdueDays' => null];
        }

        if ($invoice->status === CustomerInvoiceStatus::Draft) {
            return ['label' => __('Draft'), 'variant' => 'neutral', 'overdueDays' => null];
        }

        if ($invoice->status === CustomerInvoiceStatus::Approved) {
            return ['label' => __('Issued'), 'variant' => 'info', 'overdueDays' => null];
        }

        $balanceDue = (float) $invoice->balance_due;
        $amountPaid = (float) $invoice->amount_paid;
        $overdueDays = $this->overdueDays($invoice);

        if ($balanceDue <= 0) {
            return ['label' => __('Paid'), 'variant' => 'success', 'overdueDays' => null];
        }

        if ($overdueDays !== null && $overdueDays > 0) {
            return ['label' => __('Overdue'), 'variant' => 'danger', 'overdueDays' => $overdueDays];
        }

        if ($amountPaid > 0) {
            return ['label' => __('Partially Paid'), 'variant' => 'warning', 'overdueDays' => null];
        }

        return ['label' => __('Unpaid'), 'variant' => 'warning', 'overdueDays' => null];
    }

    /**
     * @param  array{label: string, variant: string, overdueDays: ?int}  $paymentState
     * @return array{0: string, 1: string}
     */
    protected function statusPresentation(CustomerInvoice $invoice, array $paymentState): array
    {
        if ($invoice->status === CustomerInvoiceStatus::Posted) {
            return [$paymentState['label'], $paymentState['variant']];
        }

        return match ($invoice->status) {
            CustomerInvoiceStatus::Draft => [__('Draft'), 'neutral'],
            CustomerInvoiceStatus::Approved => [__('Issued'), 'info'],
            CustomerInvoiceStatus::Cancelled => [__('Cancelled'), 'warning'],
            CustomerInvoiceStatus::Posted => [$paymentState['label'], $paymentState['variant']],
        };
    }

    /**
     * @param  array{label: string, variant: string, overdueDays: ?int}  $paymentState
     * @return array<string, mixed>
     */
    protected function presentSummary(CustomerInvoice $invoice, string $currency, array $paymentState): array
    {
        $balanceDue = $invoice->status === CustomerInvoiceStatus::Posted
            ? max(0, (float) $invoice->balance_due)
            : (float) $invoice->total_amount;

        $amountPaid = $invoice->status === CustomerInvoiceStatus::Posted
            ? (float) $invoice->amount_paid
            : 0.0;

        $rows = [
            ['label' => __('Invoice Total'), 'value' => $this->formatMoney((float) $invoice->total_amount, $currency)],
            ['label' => __('Amount Paid'), 'value' => $this->formatMoney($amountPaid, $currency)],
            [
                'label' => __('Balance Due'),
                'value' => $this->formatMoney($balanceDue, $currency),
                'highlight' => true,
                'emphasis' => true,
            ],
            ['label' => __('Due Date'), 'value' => $invoice->due_date?->format('d M Y') ?? '—'],
            [
                'label' => __('Status'),
                'value' => $paymentState['label'],
                'badge' => [
                    'label' => $paymentState['label'],
                    'variant' => $paymentState['variant'],
                ],
            ],
        ];

        return [
            'title' => __('Invoice Summary'),
            'rows' => $rows,
            'overdueDays' => $paymentState['overdueDays'],
        ];
    }

    /**
     * @param  array<string, string|null>  $invoiceCustom
     * @param  array<string, string|null>  $salesOrderCustom
     * @return list<array{label: string, value: string}>
     */
    protected function commercialMeta(
        CustomerInvoice $invoice,
        ?DeliveryNote $deliveryNote,
        array $invoiceCustom,
        array $salesOrderCustom,
    ): array {
        return $this->filterMetaRows([
            ['label' => __('Sales Order'), 'value' => $invoice->salesOrder?->order_number],
            ['label' => __('Job Card'), 'value' => $invoice->jobCard?->job_card_number],
            ['label' => __('Delivery Note'), 'value' => $deliveryNote?->delivery_note_number],
            ['label' => __('Customer PO'), 'value' => $this->resolveCustomerPo($invoiceCustom, $salesOrderCustom)],
            ['label' => __('Artwork reference'), 'value' => $this->resolveArtworkReference($invoice)],
            ['label' => __('Production status'), 'value' => $this->productionStatusLabel($invoice->jobCard?->status)],
            ['label' => __('Dispatch status'), 'value' => $deliveryNote?->status?->label()],
            ['label' => __('Delivery date'), 'value' => $deliveryNote?->delivery_date?->format('d M Y')],
        ]);
    }

    /**
     * @param  array<string, string|null>  $invoiceCustom
     * @param  array<string, string|null>  $salesOrderCustom
     */
    protected function resolveCustomerPo(array $invoiceCustom, array $salesOrderCustom): ?string
    {
        foreach (['customer_po', 'customer_po_number', 'po_reference', 'purchase_order_reference'] as $key) {
            if (filled($invoiceCustom[$key] ?? null)) {
                return $invoiceCustom[$key];
            }

            if (filled($salesOrderCustom[$key] ?? null)) {
                return $salesOrderCustom[$key];
            }
        }

        return null;
    }

    protected function resolveArtworkReference(CustomerInvoice $invoice): ?string
    {
        $artwork = $invoice->jobCard?->artworkRequest
            ?? $invoice->salesOrder?->artworkRequest;

        if (! $artwork instanceof ArtworkRequest) {
            return null;
        }

        return $artwork->request_number.($artwork->title ? ' — '.$artwork->title : '');
    }

    protected function productionStatusLabel(?ProductionJobCardStatus $status): ?string
    {
        if ($status === null) {
            return null;
        }

        return match ($status) {
            ProductionJobCardStatus::Draft => __('Draft'),
            ProductionJobCardStatus::Queued => __('Queued'),
            ProductionJobCardStatus::InProduction => __('In Production'),
            ProductionJobCardStatus::QualityCheck => __('Quality Check'),
            ProductionJobCardStatus::Completed => __('Completed'),
            ProductionJobCardStatus::ReadyForDispatch => __('Ready for Dispatch'),
            ProductionJobCardStatus::OnHold => __('On Hold'),
            ProductionJobCardStatus::Cancelled => __('Cancelled'),
            ProductionJobCardStatus::Rework => __('Rework'),
        };
    }

    protected function resolveDeliveryNote(CustomerInvoice $invoice): ?DeliveryNote
    {
        $deliveryNoteId = $invoice->getAttribute('delivery_note_id');

        if ($deliveryNoteId) {
            return DeliveryNote::query()->find($deliveryNoteId);
        }

        if ($invoice->production_job_card_id) {
            return DeliveryNote::query()
                ->where('production_job_card_id', $invoice->production_job_card_id)
                ->whereNot('status', DeliveryNoteStatus::Cancelled)
                ->latest('id')
                ->first();
        }

        return null;
    }

    protected function overdueDays(CustomerInvoice $invoice): ?int
    {
        if ($invoice->status !== CustomerInvoiceStatus::Posted || (float) $invoice->balance_due <= 0) {
            return null;
        }

        if ($invoice->due_date === null) {
            return null;
        }

        $dueDate = $invoice->due_date instanceof Carbon
            ? $invoice->due_date->startOfDay()
            : Carbon::parse($invoice->due_date)->startOfDay();

        if ($dueDate->gte(now()->startOfDay())) {
            return null;
        }

        return (int) $dueDate->diffInDays(now()->startOfDay());
    }

    /**
     * @return list<array<string, string>>
     */
    protected function itemColumns(): array
    {
        return [
            ['key' => 'index', 'label' => __('No'), 'align' => 'left', 'width' => '6%'],
            ['key' => 'description', 'label' => __('Item & Description'), 'align' => 'left', 'width' => '44%'],
            ['key' => 'quantity', 'label' => __('Qty'), 'align' => 'right', 'width' => '14%'],
            ['key' => 'rate', 'label' => __('Rate'), 'align' => 'right', 'width' => '16%'],
            ['key' => 'amount', 'label' => __('Amount'), 'align' => 'right', 'width' => '20%'],
        ];
    }

    /**
     * @return list<array<string, string>>
     */
    protected function presentItems(CustomerInvoice $invoice, string $currency): array
    {
        return $invoice->lines->values()->map(function ($line, int $index) use ($currency) {
            $description = $line->item_name;
            if ($line->description) {
                $description .= ' — '.$line->description;
            }

            return [
                'index' => (string) ($index + 1),
                'description' => $description,
                'quantity' => number_format((float) $line->quantity, 0),
                'rate' => number_format((float) $line->unit_price, 2),
                'amount' => $this->formatMoney((float) $line->line_total, $currency),
            ];
        })->all();
    }

    /**
     * @return list<array{label: string, value: string, highlight?: bool}>
     */
    protected function presentTotals(CustomerInvoice $invoice, string $currency): array
    {
        $lines = [
            ['label' => __('Subtotal'), 'value' => $this->formatMoney((float) $invoice->subtotal, $currency)],
        ];

        if ((float) $invoice->discount_amount > 0) {
            $lines[] = [
                'label' => __('Discount'),
                'value' => $this->formatMoney((float) $invoice->discount_amount, $currency),
            ];
        }

        if ((float) $invoice->tax_amount > 0) {
            $lines[] = [
                'label' => $this->documentTaxLabel($invoice->company_id),
                'value' => $this->formatMoney((float) $invoice->tax_amount, $currency),
            ];
        }

        $lines[] = [
            'label' => __('Total'),
            'value' => $this->formatMoney((float) $invoice->total_amount, $currency),
            'highlight' => true,
        ];

        if ($invoice->status === CustomerInvoiceStatus::Posted) {
            $lines[] = [
                'label' => __('Amount Paid'),
                'value' => $this->formatMoney((float) $invoice->amount_paid, $currency),
            ];
            $lines[] = [
                'label' => __('Balance Due'),
                'value' => $this->formatMoney(max(0, (float) $invoice->balance_due), $currency),
                'highlight' => true,
                'balanceBar' => true,
            ];
        }

        return $lines;
    }

    /**
     * @param  array<string, string|null>  $invoiceCustom
     */
    protected function resolveNotesTerms(CustomerInvoice $invoice, array $invoiceCustom): string
    {
        $parts = array_filter([
            $invoice->notes,
            $this->documentTerm('invoice', $invoice->company_id),
        ]);

        return implode("\n\n", $parts);
    }

}
