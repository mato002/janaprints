<?php

namespace App\Support\Sales;

use App\Enums\CustomerInvoiceStatus;
use App\Enums\CustomerInvoiceType;
use App\Enums\DocumentType;
use App\Enums\PostingEventCode;
use App\Enums\SalesOrderStatus;
use App\Models\Production\ProductionJobCard;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\CustomerInvoiceLine;
use App\Models\Sales\CustomerInvoiceTaxLine;
use App\Models\Sales\SalesOrder;
use App\Models\Sales\SalesOrderItem;
use App\Support\Accounting\AccountingPostingService;
use App\Support\Platform\NumberingService;
use App\Support\Tax\TaxCalculationService;
use App\Support\Tax\TaxTransactionRecorder;
use App\Enums\TaxDocumentType;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CustomerInvoiceService
{
    public function __construct(
        protected NumberingService $numbering,
        protected AccountingPostingService $posting,
        protected TaxCalculationService $taxCalculator,
        protected TaxTransactionRecorder $taxRecorder,
        protected SalesOrderBillingEligibilityService $billingEligibility,
        protected SalesOrderFinancialStatusService $financialStatus,
        protected ?\App\Support\Communications\CommunicationEventDispatcher $communications = null,
    ) {}

    /**
     * @param  array{
     *     invoice_type?: CustomerInvoiceType,
     *     invoice_date?: string,
     *     due_date?: ?string,
     *     notes?: ?string,
     *     billing_percent?: ?float,
     *     deposit_amount?: ?float,
     *     lines?: array<int, array{sales_order_item_id?: int, quantity?: float, unit_price?: float, discount?: float, tax_rate?: float}>,
     *     header_discount?: float
     * }  $options
     */
    public function createFromSalesOrder(SalesOrder $order, int $userId, array $options = []): CustomerInvoice
    {
        $type = $options['invoice_type'] ?? CustomerInvoiceType::Standard;
        $this->billingEligibility->assertCanInvoice($order, $type);
        $this->assertOrderBillable($order);

        $lines = $this->resolveLinesFromSalesOrder($order, $type, $options);

        $dueDate = $options['due_date'] ?? null;
        if ($dueDate === null && $order->payment_terms_days) {
            $dueDate = now()->parse($options['invoice_date'] ?? now())->addDays((int) $order->payment_terms_days)->toDateString();
        }

        return $this->createInvoice([
            'company_id' => $order->company_id,
            'branch_id' => $order->branch_id,
            'customer_id' => $order->customer_id,
            'sales_order_id' => $order->id,
            'invoice_type' => $type,
            'invoice_date' => $options['invoice_date'] ?? now()->toDateString(),
            'due_date' => $dueDate,
            'notes' => $options['notes'] ?? null,
            'billing_percent' => $type === CustomerInvoiceType::Progress ? ($options['billing_percent'] ?? null) : null,
            'deposit_amount' => $type === CustomerInvoiceType::Deposit ? ($options['deposit_amount'] ?? null) : null,
            'currency' => 'KES',
        ], $lines, $userId, (float) ($options['header_discount'] ?? 0));
    }

    /**
     * @param  array<string, mixed>  $options
     */
    public function createFromJobCard(ProductionJobCard $jobCard, int $userId, array $options = []): CustomerInvoice
    {
        $order = $jobCard->salesOrder;

        if (! $order) {
            throw ValidationException::withMessages([
                'job_card' => __('Job card is not linked to a sales order.'),
            ]);
        }

        $invoice = $this->createFromSalesOrder($order, $userId, $options);
        $invoice->update(['production_job_card_id' => $jobCard->id]);

        return $invoice->fresh(['lines', 'taxLines', 'customer', 'salesOrder', 'jobCard']);
    }

    /**
     * @param  array{
     *     invoice_date?: string,
     *     due_date?: ?string,
     *     notes?: ?string,
     *     lines: array<int, array<string, mixed>>,
     *     header_discount?: float
     * }  $data
     */
    public function createCreditNote(CustomerInvoice $postedInvoice, int $userId, array $data): CustomerInvoice
    {
        if ($postedInvoice->status !== CustomerInvoiceStatus::Posted) {
            throw ValidationException::withMessages([
                'invoice' => __('Only posted invoices can be credited.'),
            ]);
        }

        if ($postedInvoice->invoice_type === CustomerInvoiceType::CreditNote) {
            throw ValidationException::withMessages([
                'invoice' => __('Cannot issue a credit note against another credit note.'),
            ]);
        }

        $lines = $data['lines'] ?? $postedInvoice->lines->map(fn (CustomerInvoiceLine $line) => [
            'sales_order_item_id' => $line->sales_order_item_id,
            'item_name' => $line->item_name,
            'description' => $line->description,
            'quantity' => $line->quantity,
            'unit_price' => $line->unit_price,
            'discount' => $line->discount,
            'tax_rate' => $line->tax_rate,
        ])->all();

        $invoice = $this->createInvoice([
            'company_id' => $postedInvoice->company_id,
            'branch_id' => $postedInvoice->branch_id,
            'customer_id' => $postedInvoice->customer_id,
            'sales_order_id' => $postedInvoice->sales_order_id,
            'production_job_card_id' => $postedInvoice->production_job_card_id,
            'credited_invoice_id' => $postedInvoice->id,
            'invoice_type' => CustomerInvoiceType::CreditNote,
            'invoice_date' => $data['invoice_date'] ?? now()->toDateString(),
            'due_date' => $data['due_date'] ?? null,
            'notes' => $data['notes'] ?? __('Credit note for :number', ['number' => $postedInvoice->invoice_number]),
            'currency' => $postedInvoice->currency,
        ], $lines, $userId, (float) ($data['header_discount'] ?? 0));

        return $invoice;
    }

    /**
     * @param  array<string, mixed>  $header
     * @param  array<int, array<string, mixed>>  $lineItems
     */
    public function createInvoice(array $header, array $lineItems, int $userId, float $headerDiscount = 0): CustomerInvoice
    {
        if ($lineItems === []) {
            throw ValidationException::withMessages([
                'lines' => __('At least one invoice line is required.'),
            ]);
        }

        $type = $header['invoice_type'] ?? CustomerInvoiceType::Standard;
        $documentDate = $header['invoice_date'] ?? now()->toDateString();
        $taxDocType = $type->isCredit() ? TaxDocumentType::CustomerCreditNote : TaxDocumentType::CustomerInvoice;
        $calculated = $this->taxCalculator->calculate(
            (int) $header['company_id'],
            $taxDocType,
            $lineItems,
            $documentDate,
            $headerDiscount,
        );

        return DB::transaction(function () use ($header, $lineItems, $calculated, $userId, $type) {
            $invoice = CustomerInvoice::query()->create([
                ...$header,
                'invoice_number' => $this->numbering->next(
                    $type->documentType(),
                    (int) $header['company_id'],
                    isset($header['branch_id']) ? (int) $header['branch_id'] : null,
                ),
                'invoice_type' => $type,
                'status' => CustomerInvoiceStatus::Draft,
                'subtotal' => $calculated['subtotal'],
                'tax_amount' => $calculated['tax_amount'],
                'discount_amount' => $calculated['discount_amount'],
                'total_amount' => $calculated['total_amount'],
                'created_by' => $userId,
            ]);

            $this->syncLines($invoice, $lineItems, $calculated);
            $this->syncTaxLines($invoice, $calculated['tax_summary']);

            if ($invoice->sales_order_id && ! $type->isCredit()) {
                $this->validateSalesOrderCap($invoice->salesOrder);
            }

            return $invoice->load(['lines', 'taxLines', 'customer', 'salesOrder']);
        });
    }

    /**
     * @param  array<int, array<string, mixed>>  $lineItems
     */
    public function updateDraft(CustomerInvoice $invoice, array $header, array $lineItems, float $headerDiscount = 0): CustomerInvoice
    {
        if (! $invoice->status->isEditable()) {
            throw ValidationException::withMessages([
                'invoice' => __('Only draft invoices can be edited.'),
            ]);
        }

        $documentDate = $header['invoice_date'] ?? $invoice->invoice_date->toDateString();
        $taxDocType = $invoice->invoice_type->isCredit() ? TaxDocumentType::CustomerCreditNote : TaxDocumentType::CustomerInvoice;
        $calculated = $this->taxCalculator->calculate(
            $invoice->company_id,
            $taxDocType,
            $lineItems,
            $documentDate,
            $headerDiscount,
        );

        return DB::transaction(function () use ($invoice, $header, $lineItems, $calculated) {
            $invoice->update([
                ...$header,
                'subtotal' => $calculated['subtotal'],
                'tax_amount' => $calculated['tax_amount'],
                'discount_amount' => $calculated['discount_amount'],
                'total_amount' => $calculated['total_amount'],
            ]);

            $invoice->lines()->delete();
            $invoice->taxLines()->delete();
            $this->syncLines($invoice, $lineItems, $calculated);
            $this->syncTaxLines($invoice, $calculated['tax_summary']);

            if ($invoice->sales_order_id) {
                $this->validateSalesOrderCap($invoice->salesOrder()->first());
            }

            return $invoice->fresh(['lines', 'taxLines', 'customer', 'salesOrder']);
        });
    }

    public function approve(CustomerInvoice $invoice, int $userId): CustomerInvoice
    {
        if (! $invoice->status->canTransitionTo(CustomerInvoiceStatus::Approved)) {
            throw ValidationException::withMessages([
                'status' => __('Invoice cannot be approved from its current status.'),
            ]);
        }

        if ($invoice->total_amount <= 0 && $invoice->invoice_type !== CustomerInvoiceType::CreditNote) {
            throw ValidationException::withMessages([
                'total_amount' => __('Invoice total must be greater than zero.'),
            ]);
        }

        $invoice->update([
            'status' => CustomerInvoiceStatus::Approved,
            'approved_by' => $userId,
            'approved_at' => now(),
        ]);

        return $invoice->fresh(['approver']);
    }

    public function post(CustomerInvoice $invoice, int $userId): CustomerInvoice
    {
        if (! $invoice->status->canTransitionTo(CustomerInvoiceStatus::Posted)) {
            throw ValidationException::withMessages([
                'status' => __('Only approved invoices can be posted.'),
            ]);
        }

        if ($invoice->sales_order_id) {
            $this->validateSalesOrderCap($invoice->salesOrder, including: $invoice);
        }

        return DB::transaction(function () use ($invoice, $userId) {
            $event = $invoice->invoice_type->isCredit()
                ? PostingEventCode::InvoiceCreditNotePosted
                : PostingEventCode::InvoicePosted;

            $journal = $this->posting->postEvent(
                $event,
                $invoice->company_id,
                $userId,
                'customer_invoice',
                $invoice->id,
                $invoice->invoice_date->toDateString(),
                [
                    'subtotal' => (float) $invoice->subtotal,
                    'tax_amount' => (float) $invoice->tax_amount,
                    'total_amount' => (float) $invoice->total_amount,
                ],
                $invoice->branch_id,
                reference: $invoice->invoice_number,
                description: $invoice->notes ?? $invoice->invoice_type->label().' '.$invoice->invoice_number,
            );

            $invoice->update([
                'status' => CustomerInvoiceStatus::Posted,
                'posted_by' => $userId,
                'posted_at' => now(),
                'posted_journal_id' => $journal->id,
                'balance_due' => $invoice->total_amount,
                'amount_paid' => 0,
            ]);

            if ($invoice->sales_order_id && ! $invoice->invoice_type->isCredit()) {
                $this->applyInvoicedAmounts($invoice);
            } elseif ($invoice->sales_order_id && $invoice->invoice_type->isCredit()) {
                $this->reverseInvoicedAmounts($invoice);
            }

            $invoice = $invoice->fresh(['postedJournal', 'poster', 'taxLines', 'customer', 'salesOrder']);
            $this->taxRecorder->recordCustomerInvoice($invoice);

            if ($invoice->salesOrder) {
                $this->financialStatus->syncDepositAmounts($invoice->salesOrder);
            }

            $this->communications()?->dispatch(
                \App\Enums\DomainCommunicationEvent::InvoiceGenerated,
                $invoice,
                auth()->user(),
            );

            return $invoice;
        });
    }

    protected function communications(): ?\App\Support\Communications\CommunicationEventDispatcher
    {
        return $this->communications ??= app(\App\Support\Communications\CommunicationEventDispatcher::class);
    }

    public function cancel(CustomerInvoice $invoice, int $userId, ?string $reason = null): CustomerInvoice
    {
        if (! $invoice->status->canTransitionTo(CustomerInvoiceStatus::Cancelled)) {
            throw ValidationException::withMessages([
                'status' => __('Posted invoices cannot be cancelled. Issue a credit note instead.'),
            ]);
        }

        $invoice->update([
            'status' => CustomerInvoiceStatus::Cancelled,
            'cancelled_by' => $userId,
            'cancelled_at' => now(),
            'cancel_reason' => $reason,
        ]);

        return $invoice;
    }

    public function deleteDraft(CustomerInvoice $invoice): void
    {
        if (! $invoice->status->isEditable()) {
            throw ValidationException::withMessages([
                'invoice' => __('Only draft invoices can be deleted.'),
            ]);
        }

        $invoice->delete();
    }

    protected function applyInvoicedAmounts(CustomerInvoice $invoice): void
    {
        $order = $invoice->salesOrder;
        $order->update([
            'invoiced_subtotal' => round((float) $order->invoiced_subtotal + (float) $invoice->subtotal, 2),
            'invoiced_tax_amount' => round((float) $order->invoiced_tax_amount + (float) $invoice->tax_amount, 2),
            'invoiced_total' => round((float) $order->invoiced_total + (float) $invoice->total_amount, 2),
        ]);
    }

    protected function reverseInvoicedAmounts(CustomerInvoice $invoice): void
    {
        $order = $invoice->salesOrder;
        $order->update([
            'invoiced_subtotal' => round(max(0, (float) $order->invoiced_subtotal - (float) $invoice->subtotal), 2),
            'invoiced_tax_amount' => round(max(0, (float) $order->invoiced_tax_amount - (float) $invoice->tax_amount), 2),
            'invoiced_total' => round(max(0, (float) $order->invoiced_total - (float) $invoice->total_amount), 2),
        ]);
    }

    protected function validateSalesOrderCap(?SalesOrder $order, ?CustomerInvoice $including = null): void
    {
        if (! $order || ($including && $including->invoice_type->isCredit())) {
            return;
        }

        $pendingTotal = CustomerInvoice::query()
            ->where('sales_order_id', $order->id)
            ->whereIn('status', [
                CustomerInvoiceStatus::Draft->value,
                CustomerInvoiceStatus::Approved->value,
            ])
            ->when($including, fn ($q) => $q->where('id', '!=', $including->id))
            ->sum('total_amount');

        $projected = (float) $order->invoiced_total + (float) $pendingTotal + (float) ($including?->total_amount ?? 0);

        if ($projected > (float) $order->total_amount + 0.01) {
            throw ValidationException::withMessages([
                'total_amount' => __('Invoiced amount would exceed the sales order total (:max).', [
                    'max' => number_format($order->total_amount, 2),
                ]),
            ]);
        }
    }

    protected function assertOrderBillable(SalesOrder $order): void
    {
        if (in_array($order->status, [SalesOrderStatus::Draft, SalesOrderStatus::Cancelled], true)) {
            throw ValidationException::withMessages([
                'sales_order' => __('Sales order must be confirmed before invoicing.'),
            ]);
        }
    }

    protected function resolveOrderTaxRate(SalesOrder $order): float
    {
        $defaultTaxRate = (float) config('settings_registry.sections.company.settings.default_tax_rate.default', 16);
        $subtotal = (float) $order->subtotal;

        if ($subtotal <= 0) {
            return $defaultTaxRate;
        }

        return round(((float) $order->tax_amount / $subtotal) * 100, 4);
    }

    /**
     * @param  array<string, mixed>  $options
     * @return list<array<string, mixed>>
     */
    protected function resolveLinesFromSalesOrder(SalesOrder $order, CustomerInvoiceType $type, array $options): array
    {
        $order->load('items');
        $taxRate = $this->resolveOrderTaxRate($order);

        if ($type === CustomerInvoiceType::Progress) {
            $percent = (float) ($options['billing_percent'] ?? 0);
            if ($percent <= 0 || $percent > 100) {
                throw ValidationException::withMessages([
                    'billing_percent' => __('Progress billing requires a percent between 1 and 100.'),
                ]);
            }

            $remaining = max(0, (float) $order->total_amount - (float) $order->invoiced_total);
            $targetTotal = round(((float) $order->total_amount * $percent / 100), 2);
            $billAmount = min($targetTotal, $remaining);

            if ($billAmount <= 0) {
                throw ValidationException::withMessages([
                    'billing_percent' => __('Nothing left to bill on this sales order.'),
                ]);
            }

            return [[
                'item_name' => __('Progress billing :percent%', ['percent' => $percent]),
                'description' => __('Progress invoice for order :number', ['number' => $order->order_number]),
                'quantity' => 1,
                'unit_price' => $billAmount / (1 + ($taxRate / 100)),
                'discount' => 0,
                'tax_rate' => $taxRate,
            ]];
        }

        if ($type === CustomerInvoiceType::Deposit) {
            $deposit = (float) ($options['deposit_amount'] ?? 0);
            if ($deposit <= 0) {
                throw ValidationException::withMessages([
                    'deposit_amount' => __('Deposit amount is required.'),
                ]);
            }

            return [[
                'item_name' => __('Customer deposit'),
                'description' => __('Deposit for order :number', ['number' => $order->order_number]),
                'quantity' => 1,
                'unit_price' => $deposit / (1 + ($taxRate / 100)),
                'discount' => 0,
                'tax_rate' => $taxRate,
            ]];
        }

        if (! empty($options['lines'])) {
            return $this->mapPartialLines($order, $options['lines'], $taxRate);
        }

        return $order->items->map(fn (SalesOrderItem $item) => [
            'sales_order_item_id' => $item->id,
            'item_name' => $item->item_name,
            'description' => $item->description,
            'quantity' => (float) $item->quantity,
            'unit_price' => (float) $item->unit_price,
            'discount' => 0,
            'tax_rate' => $taxRate,
        ])->all();
    }

    /**
     * @param  array<int, array<string, mixed>>  $partialLines
     * @return list<array<string, mixed>>
     */
    protected function mapPartialLines(SalesOrder $order, array $partialLines, float $taxRate): array
    {
        $mapped = [];

        foreach ($partialLines as $partial) {
            $item = $order->items->firstWhere('id', $partial['sales_order_item_id'] ?? null);
            if (! $item) {
                continue;
            }

            $qty = (float) ($partial['quantity'] ?? $item->quantity);
            if ($qty <= 0 || $qty > (float) $item->quantity) {
                throw ValidationException::withMessages([
                    'lines' => __('Invalid quantity for line :name.', ['name' => $item->item_name]),
                ]);
            }

            $mapped[] = [
                'sales_order_item_id' => $item->id,
                'item_name' => $item->item_name,
                'description' => $item->description,
                'quantity' => $qty,
                'unit_price' => (float) ($partial['unit_price'] ?? $item->unit_price),
                'discount' => (float) ($partial['discount'] ?? 0),
                'tax_rate' => (float) ($partial['tax_rate'] ?? $taxRate),
            ];
        }

        if ($mapped === []) {
            throw ValidationException::withMessages([
                'lines' => __('Select at least one sales order line to invoice.'),
            ]);
        }

        return $mapped;
    }

    /**
     * @param  array<int, array<string, mixed>>  $lineItems
     * @param  array{lines: array<int, array<string, float>>, tax_summary: array<int, array<string, float>>}  $calculated
     */
    protected function syncLines(CustomerInvoice $invoice, array $lineItems, array $calculated): void
    {
        foreach ($lineItems as $index => $item) {
            $lineCalc = $calculated['lines'][$index];
            $invoice->lines()->create([
                'sales_order_item_id' => $item['sales_order_item_id'] ?? null,
                'item_name' => $item['item_name'],
                'description' => $item['description'] ?? null,
                'quantity' => $item['quantity'] ?? 1,
                'unit_price' => $item['unit_price'] ?? 0,
                'discount' => $item['discount'] ?? 0,
                'tax_code_id' => $lineCalc['tax_code_id'] ?? $item['tax_code_id'] ?? null,
                'tax_rate' => $lineCalc['tax_rate'] ?? $item['tax_rate'] ?? 0,
                'line_subtotal' => $lineCalc['line_subtotal'],
                'tax_amount' => $lineCalc['tax_amount'],
                'line_total' => $lineCalc['line_total'],
                'sort_order' => $index + 1,
            ]);
        }
    }

    /**
     * @param  array<int, array{tax_rate: float, taxable_amount: float, tax_amount: float}>  $taxSummary
     */
    protected function syncTaxLines(CustomerInvoice $invoice, array $taxSummary): void
    {
        foreach ($taxSummary as $bucket) {
            $invoice->taxLines()->create([
                'tax_code_id' => $bucket['tax_code_id'],
                'tax_category_id' => $bucket['tax_category_id'],
                'tax_code' => $bucket['tax_code'],
                'tax_name' => $bucket['tax_name'],
                'tax_rate' => $bucket['tax_rate'],
                'taxable_amount' => $bucket['taxable_amount'],
                'tax_amount' => $bucket['tax_amount'],
            ]);
        }
    }
}
