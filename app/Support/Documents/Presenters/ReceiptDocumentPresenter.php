<?php

namespace App\Support\Documents\Presenters;

use App\Models\Sales\CustomerPayment;
use App\Support\Documents\Presenters\Concerns\BuildsDocumentBlocks;
use App\Support\Sales\CustomerPaymentReceiptService;

class ReceiptDocumentPresenter
{
    use BuildsDocumentBlocks;

    public function __construct(
        protected CustomerPaymentReceiptService $receipts,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function present(CustomerPayment $payment, bool $includeInternalMeta = true): array
    {
        $receipt = $this->receipts->build($payment);

        $payment->loadMissing(['customer', 'company', 'branch', 'poster', 'allocations.invoice']);

        $currency = $receipt['currency'] ?: 'KES';
        $balanceAfter = round((float) $receipt['balance_remaining'], 2);
        $accountSettled = $balanceAfter <= 0;

        return [
            'logoDataUri' => $this->documentsLogoDataUri(),
            'documentType' => 'receipt',
            'title' => __('PAYMENT RECEIPT'),
            'documentNumber' => $receipt['receipt_number'],
            'documentNumberLabel' => __('No.'),
            'currency' => $currency,
            'headerHighlight' => $this->headerHighlightBlock(
                __('Amount Received'),
                $this->formatMoney(round((float) $receipt['amount'], 2), $currency),
            ),
            'status' => [
                'label' => $accountSettled ? __('Account Settled') : __('Posted'),
                'variant' => $accountSettled ? 'success' : 'info',
            ],
            'dates' => $this->filterMetaRows([
                ['label' => __('Receipt Date'), 'value' => $payment->payment_date?->format('d M Y')],
                ['label' => __('Payment Method'), 'value' => $receipt['payment_method']],
                ['label' => __('Payment Reference'), 'value' => $receipt['reference'] ?? null],
            ]),
            'company' => $this->companyBlock($payment->company),
            'customer' => $this->customerBlock($payment->customer),
            'customerLabel' => __('Received From'),
            'meta' => $this->paymentMeta($payment, $receipt),
            'summary' => $this->presentSummary($payment, $receipt, $currency, $accountSettled),
            'allocations' => $this->presentAllocations($payment, $currency),
            'totals' => $this->presentTotals($receipt, $currency),
            'notesTerms' => [
                'title' => __('Notes'),
                'body' => $this->resolveNotesTerms($receipt),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $receipt
     * @return list<array{label: string, value: string}>
     */
    protected function paymentMeta(CustomerPayment $payment, array $receipt): array
    {
        return $this->filterMetaRows([
            ['label' => __('Payment number'), 'value' => $receipt['payment_number']],
            ['label' => __('Received by'), 'value' => $receipt['received_by'] ?? null],
            ['label' => __('Branch'), 'value' => $receipt['branch_name'] ?? null],
        ]);
    }

    /**
     * @param  array<string, mixed>  $receipt
     * @return array<string, mixed>
     */
    protected function presentSummary(
        CustomerPayment $payment,
        array $receipt,
        string $currency,
        bool $accountSettled,
    ): array {
        $amountReceived = round((float) $receipt['amount'], 2);
        $balanceBefore = round((float) $receipt['balance_before'], 2);
        $balanceAfter = round((float) $receipt['balance_remaining'], 2);

        $statusLabel = $accountSettled ? __('Account Settled') : __('Posted');
        $statusVariant = $accountSettled ? 'success' : 'info';

        return [
            'title' => __('Receipt Summary'),
            'accountSettled' => $accountSettled,
            'rows' => [
                [
                    'label' => __('Amount Received'),
                    'value' => $this->formatMoney($amountReceived, $currency),
                    'emphasis' => true,
                ],
                ['label' => __('Payment Method'), 'value' => $receipt['payment_method']],
                ['label' => __('Payment Reference'), 'value' => $receipt['reference'] ?? '—'],
                ['label' => __('Balance Before'), 'value' => $this->formatMoney($balanceBefore, $currency)],
                ['label' => __('Balance After'), 'value' => $this->formatMoney($balanceAfter, $currency)],
                [
                    'label' => __('Status'),
                    'value' => $statusLabel,
                    'badge' => [
                        'label' => $statusLabel,
                        'variant' => $statusVariant,
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function presentAllocations(CustomerPayment $payment, string $currency): array
    {
        $rows = $payment->allocations->map(function ($allocation) use ($currency) {
            $invoice = $allocation->invoice;

            return [
                'invoice_number' => $invoice?->invoice_number ?? '—',
                'invoice_date' => $invoice?->invoice_date?->format('d M Y') ?? '—',
                'amount_applied' => $this->formatMoney((float) $allocation->amount, $currency),
                'balance_remaining' => $this->formatMoney((float) ($invoice?->balance_due ?? 0), $currency),
            ];
        })->values()->all();

        return [
            'title' => __('Invoices Settled'),
            'columns' => [
                ['key' => 'invoice_number', 'label' => __('Invoice Number'), 'align' => 'left', 'width' => '28%'],
                ['key' => 'invoice_date', 'label' => __('Invoice Date'), 'align' => 'left', 'width' => '22%'],
                ['key' => 'amount_applied', 'label' => __('Amount Applied'), 'align' => 'right', 'width' => '25%'],
                ['key' => 'balance_remaining', 'label' => __('Balance Remaining'), 'align' => 'right', 'width' => '25%'],
            ],
            'rows' => $rows,
            'emptyMessage' => __('Payment has not been allocated to a specific invoice.'),
        ];
    }

    /**
     * @param  array<string, mixed>  $receipt
     * @return list<array{label: string, value: string, highlight?: bool}>
     */
    protected function presentTotals(array $receipt, string $currency): array
    {
        $lines = [
            [
                'label' => __('Balance Before Payment'),
                'value' => $this->formatMoney((float) $receipt['balance_before'], $currency),
            ],
            [
                'label' => __('Amount Received'),
                'value' => $this->formatMoney((float) $receipt['amount'], $currency),
                'highlight' => true,
                'balanceBar' => true,
            ],
            [
                'label' => __('Balance After Payment'),
                'value' => $this->formatMoney((float) $receipt['balance_remaining'], $currency),
            ],
        ];

        if ((float) $receipt['unallocated_amount'] > 0) {
            $lines[] = [
                'label' => __('Unallocated Amount'),
                'value' => $this->formatMoney((float) $receipt['unallocated_amount'], $currency),
            ];
        }

        return $lines;
    }

    /**
     * @param  array<string, mixed>  $receipt
     */
    protected function resolveNotesTerms(array $receipt): string
    {
        $parts = array_filter([
            $receipt['notes'] ?? null,
            config('documents.terms.receipt_acknowledgement'),
        ]);

        return implode("\n\n", $parts);
    }

}
