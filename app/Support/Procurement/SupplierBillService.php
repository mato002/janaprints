<?php

namespace App\Support\Procurement;

use App\Enums\GoodsReceiptStatus;
use App\Enums\PostingEventCode;
use App\Enums\PurchaseOrderStatus;
use App\Enums\SupplierBillLineType;
use App\Enums\SupplierBillStatus;
use App\Enums\SupplierBillType;
use App\Models\Procurement\GoodsReceipt;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\PurchaseOrderItem;
use App\Models\Procurement\SupplierBill;
use App\Models\Procurement\SupplierBillLine;
use App\Models\Procurement\SupplierBillTaxLine;
use App\Enums\TaxDocumentType;
use App\Support\Accounting\AccountingPostingService;
use App\Support\Platform\NumberingService;
use App\Support\Tax\TaxCalculationService;
use App\Support\Tax\TaxTransactionRecorder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SupplierBillService
{
    public function __construct(
        protected NumberingService $numbering,
        protected AccountingPostingService $posting,
        protected TaxCalculationService $taxCalculator,
        protected TaxTransactionRecorder $taxRecorder,
    ) {}

    /**
     * @param  array<string, mixed>  $options
     */
    public function createFromPurchaseOrder(PurchaseOrder $order, int $userId, array $options = []): SupplierBill
    {
        if (! in_array($order->status, [
            PurchaseOrderStatus::Approved,
            PurchaseOrderStatus::Sent,
            PurchaseOrderStatus::PartiallyReceived,
            PurchaseOrderStatus::Received,
        ], true)) {
            throw ValidationException::withMessages([
                'purchase_order' => __('Purchase order must be approved before billing.'),
            ]);
        }

        $order->load('items.inventoryItem');

        $lines = collect($options['lines'] ?? [])->isNotEmpty()
            ? $options['lines']
            : $order->items->map(fn (PurchaseOrderItem $item) => [
                'purchase_order_item_id' => $item->id,
                'line_type' => SupplierBillLineType::Inventory->value,
                'item_name' => $item->inventoryItem?->name ?? $item->description,
                'description' => $item->description,
                'quantity' => (float) $item->quantity,
                'unit_cost' => (float) $item->unit_cost,
                'discount' => 0,
                'tax_rate' => (float) ($options['default_tax_rate'] ?? 0),
            ])->all();

        return $this->createBill([
            'company_id' => $order->company_id,
            'branch_id' => $order->branch_id,
            'vendor_id' => $order->vendor_id,
            'purchase_order_id' => $order->id,
            'bill_type' => SupplierBillType::FromPurchaseOrder,
            'bill_date' => $options['bill_date'] ?? now()->toDateString(),
            'due_date' => $options['due_date'] ?? null,
            'notes' => $options['notes'] ?? null,
            'currency' => 'KES',
        ], $lines, $userId, (float) ($options['header_discount'] ?? 0));
    }

    /**
     * @param  array<string, mixed>  $options
     */
    public function createFromGoodsReceipt(GoodsReceipt $receipt, int $userId, array $options = []): SupplierBill
    {
        if ($receipt->status !== GoodsReceiptStatus::Posted) {
            throw ValidationException::withMessages([
                'goods_receipt' => __('Goods receipt must be posted before billing.'),
            ]);
        }

        $receipt->load(['items.inventoryItem', 'items.purchaseOrderItem', 'purchaseOrder']);

        $lines = $receipt->items->map(fn ($item) => [
            'goods_receipt_item_id' => $item->id,
            'purchase_order_item_id' => $item->purchase_order_item_id,
            'line_type' => SupplierBillLineType::Inventory->value,
            'item_name' => $item->inventoryItem?->name ?? __('Received item'),
            'description' => $item->purchaseOrderItem?->description,
            'quantity' => (float) $item->quantity_received,
            'unit_cost' => (float) $item->unit_cost,
            'discount' => 0,
            'tax_rate' => (float) ($options['default_tax_rate'] ?? 0),
        ])->all();

        if ($lines === []) {
            throw ValidationException::withMessages([
                'lines' => __('Goods receipt has no lines to bill.'),
            ]);
        }

        return $this->createBill([
            'company_id' => $receipt->company_id,
            'branch_id' => $receipt->branch_id,
            'vendor_id' => $receipt->purchaseOrder->vendor_id,
            'purchase_order_id' => $receipt->purchase_order_id,
            'goods_receipt_id' => $receipt->id,
            'bill_type' => SupplierBillType::FromGoodsReceipt,
            'bill_date' => $options['bill_date'] ?? $receipt->receipt_date->toDateString(),
            'due_date' => $options['due_date'] ?? null,
            'notes' => $options['notes'] ?? __('Bill from GRN :number', ['number' => $receipt->receipt_number]),
            'currency' => 'KES',
        ], $lines, $userId, (float) ($options['header_discount'] ?? 0));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createCreditNote(SupplierBill $postedBill, int $userId, array $data): SupplierBill
    {
        if ($postedBill->status !== SupplierBillStatus::Posted && $postedBill->status !== SupplierBillStatus::Paid) {
            throw ValidationException::withMessages([
                'bill' => __('Only posted supplier bills can be credited.'),
            ]);
        }

        if ($postedBill->bill_type->isCredit()) {
            throw ValidationException::withMessages([
                'bill' => __('Cannot credit a credit note.'),
            ]);
        }

        $lines = $data['lines'] ?? $postedBill->lines->map(fn (SupplierBillLine $line) => [
            'purchase_order_item_id' => $line->purchase_order_item_id,
            'goods_receipt_item_id' => $line->goods_receipt_item_id,
            'line_type' => $line->line_type->value,
            'item_name' => $line->item_name,
            'description' => $line->description,
            'quantity' => $line->quantity,
            'unit_cost' => $line->unit_cost,
            'discount' => $line->discount,
            'tax_rate' => $line->tax_rate,
        ])->all();

        return $this->createBill([
            'company_id' => $postedBill->company_id,
            'branch_id' => $postedBill->branch_id,
            'vendor_id' => $postedBill->vendor_id,
            'purchase_order_id' => $postedBill->purchase_order_id,
            'goods_receipt_id' => $postedBill->goods_receipt_id,
            'credited_bill_id' => $postedBill->id,
            'bill_type' => SupplierBillType::CreditNote,
            'bill_date' => $data['bill_date'] ?? now()->toDateString(),
            'due_date' => $data['due_date'] ?? null,
            'notes' => $data['notes'] ?? __('Credit note for :number', ['number' => $postedBill->bill_number]),
            'currency' => $postedBill->currency,
        ], $lines, $userId, (float) ($data['header_discount'] ?? 0));
    }

    /**
     * @param  array<string, mixed>  $header
     * @param  array<int, array<string, mixed>>  $lineItems
     */
    public function createBill(array $header, array $lineItems, int $userId, float $headerDiscount = 0): SupplierBill
    {
        if ($lineItems === []) {
            throw ValidationException::withMessages([
                'lines' => __('At least one bill line is required.'),
            ]);
        }

        $type = $header['bill_type'] ?? SupplierBillType::Standard;
        $documentDate = $header['bill_date'] ?? now()->toDateString();
        $taxDocType = $type->isCredit() ? TaxDocumentType::SupplierCreditNote : TaxDocumentType::SupplierBill;
        $calculated = $this->taxCalculator->calculate(
            (int) $header['company_id'],
            $taxDocType,
            $this->mapCalculatorLines($lineItems),
            $documentDate,
            $headerDiscount,
        );

        return DB::transaction(function () use ($header, $lineItems, $calculated, $userId, $type) {
            $bill = SupplierBill::query()->create([
                ...$header,
                'bill_number' => $this->numbering->next(
                    $type->documentType(),
                    (int) $header['company_id'],
                    isset($header['branch_id']) ? (int) $header['branch_id'] : null,
                ),
                'bill_type' => $type,
                'status' => SupplierBillStatus::Draft,
                'subtotal' => $calculated['subtotal'],
                'tax_amount' => $calculated['tax_amount'],
                'discount_amount' => $calculated['discount_amount'],
                'total_amount' => $calculated['total_amount'],
                'created_by' => $userId,
            ]);

            $this->syncLines($bill, $lineItems, $calculated);
            $this->syncTaxLines($bill, $calculated['tax_summary']);

            if ($bill->purchase_order_id && ! $type->isCredit()) {
                $this->validatePurchaseOrderCap($bill->purchaseOrder);
            }

            return $bill->load(['lines', 'taxLines', 'vendor', 'purchaseOrder']);
        });
    }

    /**
     * @param  array<int, array<string, mixed>>  $lineItems
     */
    public function updateDraft(SupplierBill $bill, array $header, array $lineItems, float $headerDiscount = 0): SupplierBill
    {
        if (! $bill->status->isEditable()) {
            throw ValidationException::withMessages([
                'bill' => __('Only draft bills can be edited.'),
            ]);
        }

        $documentDate = $header['bill_date'] ?? $bill->bill_date->toDateString();
        $taxDocType = $bill->bill_type->isCredit() ? TaxDocumentType::SupplierCreditNote : TaxDocumentType::SupplierBill;
        $calculated = $this->taxCalculator->calculate(
            $bill->company_id,
            $taxDocType,
            $this->mapCalculatorLines($lineItems),
            $documentDate,
            $headerDiscount,
        );

        return DB::transaction(function () use ($bill, $header, $lineItems, $calculated) {
            $bill->update([
                ...$header,
                'subtotal' => $calculated['subtotal'],
                'tax_amount' => $calculated['tax_amount'],
                'discount_amount' => $calculated['discount_amount'],
                'total_amount' => $calculated['total_amount'],
            ]);

            $bill->lines()->delete();
            $bill->taxLines()->delete();
            $this->syncLines($bill, $lineItems, $calculated);
            $this->syncTaxLines($bill, $calculated['tax_summary']);

            if ($bill->purchase_order_id) {
                $this->validatePurchaseOrderCap($bill->purchaseOrder()->first(), $bill);
            }

            return $bill->fresh(['lines', 'taxLines', 'vendor']);
        });
    }

    public function approve(SupplierBill $bill, int $userId): SupplierBill
    {
        if (! $bill->status->canTransitionTo(SupplierBillStatus::Approved)) {
            throw ValidationException::withMessages([
                'status' => __('Bill cannot be approved from its current status.'),
            ]);
        }

        if ($bill->total_amount <= 0 && ! $bill->bill_type->isCredit()) {
            throw ValidationException::withMessages([
                'total_amount' => __('Bill total must be greater than zero.'),
            ]);
        }

        $bill->update([
            'status' => SupplierBillStatus::Approved,
            'approved_by' => $userId,
            'approved_at' => now(),
        ]);

        return $bill->fresh(['approver']);
    }

    public function post(SupplierBill $bill, int $userId): SupplierBill
    {
        if (! $bill->status->canTransitionTo(SupplierBillStatus::Posted)) {
            throw ValidationException::withMessages([
                'status' => __('Only approved bills can be posted.'),
            ]);
        }

        if ($bill->purchase_order_id) {
            $this->validatePurchaseOrderCap($bill->purchaseOrder, $bill);
        }

        $bill->load('lines');

        return DB::transaction(function () use ($bill, $userId) {
            $amounts = $this->postingAmounts($bill);

            $event = $bill->bill_type->isCredit()
                ? PostingEventCode::SupplierBillCreditNotePosted
                : PostingEventCode::SupplierBillPosted;

            $journal = $this->posting->postEvent(
                $event,
                $bill->company_id,
                $userId,
                'supplier_bill',
                $bill->id,
                $bill->bill_date->toDateString(),
                $amounts,
                $bill->branch_id,
                reference: $bill->bill_number,
                description: $bill->notes ?? $bill->bill_type->label().' '.$bill->bill_number,
            );

            $bill->update([
                'status' => SupplierBillStatus::Posted,
                'posted_by' => $userId,
                'posted_at' => now(),
                'posted_journal_id' => $journal->id,
                'balance_due' => $bill->total_amount,
                'amount_paid' => 0,
            ]);

            if ($bill->purchase_order_id && ! $bill->bill_type->isCredit()) {
                $this->applyBilledAmounts($bill);
            } elseif ($bill->purchase_order_id && $bill->bill_type->isCredit()) {
                $this->reverseBilledAmounts($bill);
            }

            $bill = $bill->fresh(['postedJournal', 'poster', 'taxLines']);
            $this->taxRecorder->recordSupplierBill($bill);

            return $bill;
        });
    }

    public function cancel(SupplierBill $bill, int $userId, ?string $reason = null): SupplierBill
    {
        if (! $bill->status->canTransitionTo(SupplierBillStatus::Cancelled)) {
            throw ValidationException::withMessages([
                'status' => __('Posted bills cannot be cancelled. Issue a credit note instead.'),
            ]);
        }

        $bill->update([
            'status' => SupplierBillStatus::Cancelled,
            'cancelled_by' => $userId,
            'cancelled_at' => now(),
            'cancel_reason' => $reason,
        ]);

        return $bill;
    }

    public function deleteDraft(SupplierBill $bill): void
    {
        if (! $bill->status->isEditable()) {
            throw ValidationException::withMessages([
                'bill' => __('Only draft bills can be deleted.'),
            ]);
        }

        $bill->delete();
    }

    /**
     * @return array<string, float>
     */
    protected function postingAmounts(SupplierBill $bill): array
    {
        $inventory = 0.0;
        $expense = 0.0;

        foreach ($bill->lines as $line) {
            $subtotal = (float) $line->line_subtotal;
            if ($line->line_type === SupplierBillLineType::Expense) {
                $expense += $subtotal;
            } else {
                $inventory += $subtotal;
            }
        }

        return [
            'inventory_amount' => round($inventory, 2),
            'expense_amount' => round($expense, 2),
            'subtotal' => (float) $bill->subtotal,
            'tax_amount' => (float) $bill->tax_amount,
            'total_amount' => (float) $bill->total_amount,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $lineItems
     * @return array<int, array<string, mixed>>
     */
    protected function mapCalculatorLines(array $lineItems): array
    {
        return array_map(fn (array $line) => [
            'quantity' => $line['quantity'] ?? 1,
            'unit_price' => $line['unit_cost'] ?? $line['unit_price'] ?? 0,
            'discount' => $line['discount'] ?? 0,
            'tax_code_id' => $line['tax_code_id'] ?? null,
            'tax_rate' => $line['tax_rate'] ?? 0,
        ], $lineItems);
    }

    /**
     * @param  array<int, array<string, mixed>>  $lineItems
     * @param  array<string, mixed>  $calculated
     */
    protected function syncLines(SupplierBill $bill, array $lineItems, array $calculated): void
    {
        foreach ($lineItems as $index => $item) {
            $lineCalc = $calculated['lines'][$index] ?? ['line_subtotal' => 0, 'tax_amount' => 0, 'line_total' => 0];
            $lineType = $item['line_type'] ?? SupplierBillLineType::Inventory->value;

            $bill->lines()->create([
                'purchase_order_item_id' => $item['purchase_order_item_id'] ?? null,
                'goods_receipt_item_id' => $item['goods_receipt_item_id'] ?? null,
                'line_type' => $lineType,
                'item_name' => $item['item_name'] ?? __('Line :n', ['n' => $index + 1]),
                'description' => $item['description'] ?? null,
                'quantity' => $item['quantity'] ?? 1,
                'unit_cost' => $item['unit_cost'] ?? $item['unit_price'] ?? 0,
                'discount' => $item['discount'] ?? 0,
                'tax_code_id' => $lineCalc['tax_code_id'] ?? $item['tax_code_id'] ?? null,
                'tax_rate' => $lineCalc['tax_rate'] ?? $item['tax_rate'] ?? 0,
                'line_subtotal' => $lineCalc['line_subtotal'],
                'tax_amount' => $lineCalc['tax_amount'],
                'line_total' => $lineCalc['line_total'],
                'sort_order' => $index,
            ]);
        }
    }

    /**
     * @param  array<int, array{tax_rate: float, taxable_amount: float, tax_amount: float}>  $taxSummary
     */
    protected function syncTaxLines(SupplierBill $bill, array $taxSummary): void
    {
        foreach ($taxSummary as $row) {
            $bill->taxLines()->create([
                'tax_code_id' => $row['tax_code_id'],
                'tax_category_id' => $row['tax_category_id'],
                'tax_code' => $row['tax_code'],
                'tax_name' => $row['tax_name'],
                'tax_rate' => $row['tax_rate'],
                'taxable_amount' => $row['taxable_amount'],
                'tax_amount' => $row['tax_amount'],
            ]);
        }
    }

    protected function applyBilledAmounts(SupplierBill $bill): void
    {
        $order = $bill->purchaseOrder;
        $order->update([
            'billed_subtotal' => round((float) $order->billed_subtotal + (float) $bill->subtotal, 2),
            'billed_tax_amount' => round((float) $order->billed_tax_amount + (float) $bill->tax_amount, 2),
            'billed_total' => round((float) $order->billed_total + (float) $bill->total_amount, 2),
        ]);
    }

    protected function reverseBilledAmounts(SupplierBill $bill): void
    {
        $order = $bill->purchaseOrder;
        $order->update([
            'billed_subtotal' => round(max(0, (float) $order->billed_subtotal - (float) $bill->subtotal), 2),
            'billed_tax_amount' => round(max(0, (float) $order->billed_tax_amount - (float) $bill->tax_amount), 2),
            'billed_total' => round(max(0, (float) $order->billed_total - (float) $bill->total_amount), 2),
        ]);
    }

    protected function validatePurchaseOrderCap(?PurchaseOrder $order, ?SupplierBill $including = null): void
    {
        if (! $order || ($including && $including->bill_type->isCredit())) {
            return;
        }

        $pending = SupplierBill::query()
            ->where('purchase_order_id', $order->id)
            ->whereIn('status', [
                SupplierBillStatus::Draft->value,
                SupplierBillStatus::Approved->value,
            ])
            ->when($including, fn ($q) => $q->where('id', '!=', $including->id))
            ->sum('total_amount');

        $projected = (float) $order->billed_total + (float) $pending + (float) ($including?->total_amount ?? 0);

        if ($projected > (float) $order->total_amount + 0.02) {
            throw ValidationException::withMessages([
                'purchase_order' => __('Bill total would exceed purchase order amount.'),
            ]);
        }
    }
}
