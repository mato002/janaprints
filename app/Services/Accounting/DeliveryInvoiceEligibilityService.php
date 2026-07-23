<?php

namespace App\Services\Accounting;

use App\Enums\CustomerInvoiceStatus;
use App\Enums\Dispatch\DeliveryNoteStatus;
use App\Models\Dispatch\DeliveryNote;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\SalesOrder;
use Illuminate\Support\Facades\Schema;

class DeliveryInvoiceEligibilityService
{
    /**
     * @return array{eligible: bool, blockers: list<string>, warnings: list<string>}
     */
    public function check(DeliveryNote $note): array
    {
        $blockers = [];
        $warnings = [];

        if ($note->status !== DeliveryNoteStatus::Delivered) {
            $blockers[] = __('Delivery note must be in delivered status.');
        }

        if (! $note->invoice_ready) {
            $blockers[] = __('Delivery note is not marked invoice-ready.');
        }

        if ($this->hasActiveInvoice($note)) {
            $blockers[] = __('An invoice already exists for this delivery note.');
        }

        if ($note->items()->count() === 0) {
            $blockers[] = __('Delivery note has no billable line items.');
        }

        if ($note->sales_order_id) {
            $partial = $this->partialDeliverySummary($note);
            if ($partial['is_partial']) {
                $warnings[] = __('Partial delivery — remaining quantity can be invoiced on future delivery notes.');
            }

            $order = $note->relationLoaded('salesOrder')
                ? $note->salesOrder
                : SalesOrder::query()->find($note->sales_order_id);

            if ($order && $this->remainingBillableOnOrder($order) <= 0.01) {
                $blockers[] = __('The linked sales order has no remaining billable balance. It may already have been invoiced from the order.');
            }

        }

        return [
            'eligible' => $blockers === [],
            'blockers' => $blockers,
            'warnings' => $warnings,
        ];
    }

    /**
     * Informational Commercial billing context — not dispatch blockers.
     *
     * @return list<string>
     */
    public function commercialBillingNotes(DeliveryNote $note): array
    {
        if (! $note->sales_order_id || $this->hasActiveInvoice($note)) {
            return [];
        }

        $order = $note->relationLoaded('salesOrder')
            ? $note->salesOrder
            : SalesOrder::query()->find($note->sales_order_id);

        if ($order === null) {
            return [];
        }

        $unlinkedInvoices = CustomerInvoice::query()
            ->where('sales_order_id', $order->id)
            ->where('company_id', $note->company_id)
            ->whereNull('delivery_note_id')
            ->whereNot('status', CustomerInvoiceStatus::Cancelled)
            ->orderByDesc('invoice_date')
            ->orderByDesc('id')
            ->get(['invoice_number']);

        if ($unlinkedInvoices->isEmpty()) {
            return [];
        }

        $numbers = $unlinkedInvoices->pluck('invoice_number')->filter()->join(', ');

        return [
            __('Invoice(s) :numbers exist on the sales order (Commercial). Advance billing is allowed and does not block dispatch.', [
                'numbers' => $numbers,
            ]),
        ];
    }

    public function hasActiveInvoice(DeliveryNote $note): bool
    {
        if (! Schema::hasTable('customer_invoices') || ! Schema::hasColumn('customer_invoices', 'delivery_note_id')) {
            return false;
        }

        return CustomerInvoice::query()
            ->where('delivery_note_id', $note->id)
            ->where('company_id', $note->company_id)
            ->whereNot('status', CustomerInvoiceStatus::Cancelled)
            ->exists();
    }

    /**
     * Partial delivery foundation: compare SO line quantities to this DN.
     *
     * @return array{
     *     is_partial: bool,
     *     lines: list<array{sales_order_item_id: int|null, ordered: float, delivered_on_note: float, remaining: float}>
     * }
     */
    public function partialDeliverySummary(DeliveryNote $note): array
    {
        if (! $note->sales_order_id) {
            return ['is_partial' => false, 'lines' => []];
        }

        $order = SalesOrder::query()
            ->with('items')
            ->find($note->sales_order_id);

        if ($order === null) {
            return ['is_partial' => false, 'lines' => []];
        }

        $note->loadMissing('items');
        $deliveredBySoItem = [];

        foreach ($note->items as $line) {
            if ($line->sales_order_item_id) {
                $deliveredBySoItem[$line->sales_order_item_id] = ($deliveredBySoItem[$line->sales_order_item_id] ?? 0)
                    + (float) $line->quantity;
            }
        }

        $lines = [];
        $isPartial = false;

        foreach ($order->items as $soItem) {
            $ordered = (float) $soItem->quantity;
            $onNote = round($deliveredBySoItem[$soItem->id] ?? 0, 3);
            $remaining = round(max(0, $ordered - $onNote), 3);

            if ($remaining > 0) {
                $isPartial = true;
            }

            $lines[] = [
                'sales_order_item_id' => $soItem->id,
                'ordered' => $ordered,
                'delivered_on_note' => $onNote,
                'remaining' => $remaining,
            ];
        }

        return ['is_partial' => $isPartial, 'lines' => $lines];
    }

    /**
     * @return array{delivered_not_invoiced: int, delivered_invoiced: int}
     */
    public function customerBillingCounts(int $customerId, int $companyId): array
    {
        if (! Schema::hasTable('delivery_notes')) {
            return ['delivered_not_invoiced' => 0, 'delivered_invoiced' => 0];
        }

        $delivered = DeliveryNote::query()
            ->where('customer_id', $customerId)
            ->where('company_id', $companyId)
            ->where('status', DeliveryNoteStatus::Delivered)
            ->where('invoice_ready', true);

        $notInvoiced = (clone $delivered)
            ->whereDoesntHave('activeInvoice')
            ->count();

        $invoiced = (clone $delivered)
            ->whereHas('activeInvoice')
            ->count();

        return [
            'delivered_not_invoiced' => $notInvoiced,
            'delivered_invoiced' => $invoiced,
        ];
    }

    protected function remainingBillableOnOrder(SalesOrder $order): float
    {
        $pending = (float) CustomerInvoice::query()
            ->where('sales_order_id', $order->id)
            ->whereIn('status', [
                CustomerInvoiceStatus::Draft->value,
                CustomerInvoiceStatus::Approved->value,
            ])
            ->sum('total_amount');

        return round(max(0, (float) $order->total_amount - (float) $order->invoiced_total - $pending), 2);
    }
}
