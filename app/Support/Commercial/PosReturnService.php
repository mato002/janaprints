<?php

namespace App\Support\Commercial;

use App\Enums\PosRefundMethod;
use App\Enums\PosReturnStatus;
use App\Enums\PosReturnType;
use App\Enums\PosSaleStatus;
use App\Models\Pos\PosReturn;
use App\Models\Pos\PosReturnEvent;
use App\Models\Pos\PosReturnItem;
use App\Models\Pos\PosSale;
use App\Models\Pos\PosSaleItem;
use App\Support\Accounting\PosAccountingPostingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PosReturnService
{
    public function __construct(
        protected PosInventoryService $inventory,
        protected PosAccountingPostingService $accounting,
    ) {}
    /**
     * @param  array<int, array{pos_sale_item_id: int, quantity_returned: float, reason?: string|null}>  $lines
     */
    public function createReturn(
        PosSale $sale,
        PosReturnType $returnType,
        PosRefundMethod $refundMethod,
        string $reason,
        array $lines,
        int $createdBy,
        ?string $notes = null,
    ): PosReturn {
        $this->assertSaleReturnable($sale);

        return DB::transaction(function () use ($sale, $returnType, $refundMethod, $reason, $lines, $createdBy, $notes) {
            $resolvedLines = $this->resolveReturnLines($sale, $returnType, $lines);
            $totals = $this->calculateTotals($resolvedLines);
            $isFullReturn = $this->isFullReturn($sale, $resolvedLines);

            $return = PosReturn::query()->create([
                'company_id' => $sale->company_id,
                'branch_id' => $sale->branch_id,
                'pos_sale_id' => $sale->id,
                'pos_session_id' => $sale->pos_session_id,
                'created_by' => $createdBy,
                'return_number' => $this->nextReturnNumber($sale->company_id, $sale->branch_id),
                'return_type' => $returnType,
                'status' => PosReturnStatus::Pending,
                'refund_method' => $refundMethod,
                'subtotal' => $totals['subtotal'],
                'tax_amount' => $totals['tax_amount'],
                'refund_amount' => $refundMethod === PosRefundMethod::NoRefund ? 0 : $totals['refund_amount'],
                'is_full_return' => $isFullReturn,
                'reason' => $reason,
                'refund_reference' => $this->nextRefundReference($sale->company_id, $sale->branch_id),
                'notes' => $notes,
            ]);

            foreach ($resolvedLines as $line) {
                PosReturnItem::query()->create([
                    'pos_return_id' => $return->id,
                    'pos_sale_item_id' => $line['sale_item']->id,
                    'description' => $line['sale_item']->description,
                    'quantity_returned' => $line['quantity_returned'],
                    'unit_price' => $line['sale_item']->unit_price,
                    'line_refund_amount' => $line['line_refund_amount'],
                    'reason' => $line['reason'] ?? null,
                ]);
            }

            $this->recordEvent($return, 'created', $createdBy, __('Return submitted for approval.'));

            return $return->fresh(['items.saleItem', 'sale.items', 'creator']);
        });
    }

    public function approveReturn(PosReturn $return, int $approvedBy): PosReturn
    {
        if ($return->status !== PosReturnStatus::Pending) {
            throw ValidationException::withMessages([
                'status' => __('Only pending returns can be approved.'),
            ]);
        }

        return DB::transaction(function () use ($return, $approvedBy) {
            $return->update([
                'status' => PosReturnStatus::Approved,
                'approved_by' => $approvedBy,
                'approved_at' => now(),
            ]);

            $this->recordEvent($return, 'approved', $approvedBy, __('Return approved.'));

            return $this->completeReturn($return->fresh(), $approvedBy);
        });
    }

    public function rejectReturn(PosReturn $return, int $rejectedBy, string $rejectionReason): PosReturn
    {
        if ($return->status !== PosReturnStatus::Pending) {
            throw ValidationException::withMessages([
                'status' => __('Only pending returns can be rejected.'),
            ]);
        }

        return DB::transaction(function () use ($return, $rejectedBy, $rejectionReason) {
            $return->update([
                'status' => PosReturnStatus::Rejected,
                'rejection_reason' => $rejectionReason,
                'rejected_at' => now(),
            ]);

            $this->recordEvent($return, 'rejected', $rejectedBy, $rejectionReason);

            return $return->fresh(['items', 'sale', 'creator', 'approver', 'events.actor']);
        });
    }

    public function returnableQuantity(PosSaleItem $item): float
    {
        $returned = (float) PosReturnItem::query()
            ->where('pos_sale_item_id', $item->id)
            ->whereHas('posReturn', fn ($q) => $q->whereIn('status', [
                PosReturnStatus::Pending->value,
                PosReturnStatus::Approved->value,
                PosReturnStatus::Completed->value,
            ]))
            ->sum('quantity_returned');

        return max(0, round((float) $item->quantity - $returned, 3));
    }

    public function nextReturnNumber(int $companyId, int $branchId): string
    {
        $prefix = 'POS-RET-'.now()->format('Ymd').'-';
        $last = PosReturn::query()
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->where('return_number', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->value('return_number');

        $sequence = 1;
        if ($last && preg_match('/-(\d+)$/', $last, $matches)) {
            $sequence = (int) $matches[1] + 1;
        }

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * @return array<int, array{
     *     sale_item: PosSaleItem,
     *     quantity_returned: float,
     *     line_refund_amount: float,
     *     reason?: string|null
     * }>
     */
    protected function resolveReturnLines(PosSale $sale, PosReturnType $returnType, array $lines): array
    {
        $sale->loadMissing('items');

        if ($returnType === PosReturnType::FullReturn) {
            $lines = $sale->items->map(fn (PosSaleItem $item) => [
                'pos_sale_item_id' => $item->id,
                'quantity_returned' => $this->returnableQuantity($item),
                'reason' => null,
            ])->filter(fn (array $line) => $line['quantity_returned'] > 0)->values()->all();
        }

        if ($lines === []) {
            throw ValidationException::withMessages([
                'lines' => __('Select at least one item to return.'),
            ]);
        }

        $resolved = [];

        foreach ($lines as $line) {
            $saleItem = $sale->items->firstWhere('id', (int) ($line['pos_sale_item_id'] ?? 0));

            if ($saleItem === null) {
                throw ValidationException::withMessages([
                    'lines' => __('One or more items do not belong to this sale.'),
                ]);
            }

            $qty = round((float) ($line['quantity_returned'] ?? 0), 3);
            $returnable = $this->returnableQuantity($saleItem);

            if ($qty <= 0) {
                continue;
            }

            if ($qty > $returnable + 0.0005) {
                throw ValidationException::withMessages([
                    'lines' => __('Return quantity exceeds remaining quantity for :item.', ['item' => $saleItem->description]),
                ]);
            }

            $resolved[] = [
                'sale_item' => $saleItem,
                'quantity_returned' => $qty,
                'line_refund_amount' => $this->lineRefundAmount($saleItem, $qty),
                'reason' => $line['reason'] ?? null,
            ];
        }

        if ($resolved === []) {
            throw ValidationException::withMessages([
                'lines' => __('Select at least one item with a return quantity.'),
            ]);
        }

        return $resolved;
    }

    /**
     * @param  array<int, array{line_refund_amount: float}>  $lines
     * @return array{subtotal: string, tax_amount: string, refund_amount: string}
     */
    protected function calculateTotals(array $lines): array
    {
        $refundAmount = round(array_sum(array_column($lines, 'line_refund_amount')), 2);

        return [
            'subtotal' => number_format($refundAmount, 2, '.', ''),
            'tax_amount' => '0.00',
            'refund_amount' => number_format($refundAmount, 2, '.', ''),
        ];
    }

    protected function lineRefundAmount(PosSaleItem $item, float $qty): float
    {
        $soldQty = (float) $item->quantity;

        if ($soldQty <= 0) {
            return 0;
        }

        $ratio = min(1, $qty / $soldQty);

        return round((float) $item->line_total * $ratio, 2);
    }

    /**
     * @param  array<int, array{sale_item: PosSaleItem, quantity_returned: float}>  $lines
     */
    protected function isFullReturn(PosSale $sale, array $lines): bool
    {
        foreach ($sale->items as $item) {
            $returnedQty = 0.0;

            foreach ($lines as $line) {
                if ($line['sale_item']->id === $item->id) {
                    $returnedQty = (float) $line['quantity_returned'];
                    break;
                }
            }

            if (abs($returnedQty - $this->returnableQuantity($item)) > 0.0005) {
                return false;
            }
        }

        return true;
    }

    protected function completeReturn(PosReturn $return, int $actorId): PosReturn
    {
        if ($return->status !== PosReturnStatus::Approved) {
            throw ValidationException::withMessages([
                'status' => __('Return must be approved before completion.'),
            ]);
        }

        $return->update([
            'status' => PosReturnStatus::Completed,
            'completed_at' => now(),
        ]);

        $this->syncSaleReturnStatus($return->sale);
        $return = $return->fresh(['items.saleItem']);
        $this->inventory->restoreReturn($return, $actorId);
        $this->accounting->postReturn($return, $actorId);
        $this->recordEvent($return, 'completed', $actorId, __('Return completed and refund recorded.'));

        return $return->fresh(['items.saleItem', 'sale.items', 'creator', 'approver', 'events.actor']);
    }

    protected function syncSaleReturnStatus(PosSale $sale): void
    {
        $sale->loadMissing('items');

        $fullyReturned = true;

        foreach ($sale->items as $item) {
            if ($this->returnableQuantity($item) > 0.0005) {
                $fullyReturned = false;
                break;
            }
        }

        $sale->update([
            'status' => $fullyReturned ? PosSaleStatus::Refunded : PosSaleStatus::PartiallyRefunded,
        ]);
    }

    protected function assertSaleReturnable(PosSale $sale): void
    {
        if (! in_array($sale->status, [PosSaleStatus::Paid, PosSaleStatus::PartiallyRefunded], true)) {
            throw ValidationException::withMessages([
                'sale' => __('Only paid or partially refunded sales can be returned.'),
            ]);
        }
    }

    protected function nextRefundReference(int $companyId, int $branchId): string
    {
        return 'REF-'.now()->format('Ymd').'-'.str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
    }

    protected function recordEvent(PosReturn $return, string $action, ?int $actorId, ?string $notes = null, ?array $metadata = null): void
    {
        PosReturnEvent::query()->create([
            'pos_return_id' => $return->id,
            'actor_id' => $actorId,
            'action' => $action,
            'notes' => $notes,
            'metadata' => $metadata,
            'created_at' => now(),
        ]);
    }
}
