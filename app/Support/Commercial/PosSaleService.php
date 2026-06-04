<?php

namespace App\Support\Commercial;

use App\Enums\PosPaymentMethod;
use App\Enums\PosSaleStatus;
use App\Models\Pos\PosPayment;
use App\Models\Pos\PosSale;
use App\Models\Pos\PosSaleHold;
use App\Models\Pos\PosSaleItem;
use Illuminate\Support\Facades\DB;

class PosSaleService
{
    public function __construct(
        protected PosSaleCalculator $calculator,
    ) {}

    public function nextSaleNumber(int $companyId, int $branchId): string
    {
        $prefix = 'POS-'.now()->format('Ymd').'-';
        $last = PosSale::query()
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->where('sale_number', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->value('sale_number');

        $sequence = 1;
        if ($last && preg_match('/-(\d+)$/', $last, $matches)) {
            $sequence = (int) $matches[1] + 1;
        }

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function createSale(array $payload, int $companyId, int $branchId, int $cashierId): PosSale
    {
        return DB::transaction(function () use ($payload, $companyId, $branchId, $cashierId) {
            $lines = $payload['lines'] ?? [];
            $totals = $this->calculator->totals(
                $lines,
                $payload['discount_amount'] ?? 0,
                $payload['tax_amount'] ?? 0,
            );

            $status = isset($payload['status']) && $payload['status'] instanceof PosSaleStatus
                ? $payload['status']
                : PosSaleStatus::from($payload['status'] ?? PosSaleStatus::Draft->value);

            $amountPaid = (float) ($payload['amount_paid'] ?? 0);
            $total = (float) $totals['total_amount'];
            $balanceDue = max(0, $total - $amountPaid);

            if ($status === PosSaleStatus::Paid && $amountPaid < $total) {
                $status = PosSaleStatus::Draft;
            }

            $sale = PosSale::query()->create([
                'company_id' => $companyId,
                'branch_id' => $branchId,
                'cashier_id' => $cashierId,
                'customer_id' => $payload['customer_id'] ?? null,
                'sale_number' => $this->nextSaleNumber($companyId, $branchId),
                'sale_date' => $payload['sale_date'] ?? now()->toDateString(),
                'subtotal' => $totals['subtotal'],
                'discount_amount' => $totals['discount_amount'],
                'tax_amount' => $totals['tax_amount'],
                'total_amount' => $totals['total_amount'],
                'amount_paid' => number_format($amountPaid, 2, '.', ''),
                'balance_due' => number_format($balanceDue, 2, '.', ''),
                'status' => $status,
                'is_walk_in' => (bool) ($payload['is_walk_in'] ?? false),
                'notes' => $payload['notes'] ?? null,
            ]);

            $this->syncItems($sale, $lines);

            if (! empty($payload['payments'])) {
                $this->syncPayments($sale, $payload['payments']);
            }

            if ($status === PosSaleStatus::Held) {
                PosSaleHold::query()->create([
                    'company_id' => $companyId,
                    'branch_id' => $branchId,
                    'cashier_id' => $cashierId,
                    'pos_sale_id' => $sale->id,
                    'customer_id' => $sale->customer_id,
                    'hold_label' => $payload['hold_label'] ?? null,
                    'held_at' => now(),
                ]);
            }

            return $sale->fresh(['items', 'payments', 'customer', 'cashier']);
        });
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function updateSale(PosSale $sale, array $payload): PosSale
    {
        return DB::transaction(function () use ($sale, $payload) {
            $lines = $payload['lines'] ?? [];
            $totals = $this->calculator->totals(
                $lines,
                $payload['discount_amount'] ?? 0,
                $payload['tax_amount'] ?? 0,
            );

            $amountPaid = (float) ($payload['amount_paid'] ?? $sale->amount_paid);
            $total = (float) $totals['total_amount'];
            $status = isset($payload['status'])
                ? ($payload['status'] instanceof PosSaleStatus ? $payload['status'] : PosSaleStatus::from($payload['status']))
                : $sale->status;

            $sale->update([
                'customer_id' => $payload['customer_id'] ?? $sale->customer_id,
                'subtotal' => $totals['subtotal'],
                'discount_amount' => $totals['discount_amount'],
                'tax_amount' => $totals['tax_amount'],
                'total_amount' => $totals['total_amount'],
                'amount_paid' => number_format($amountPaid, 2, '.', ''),
                'balance_due' => number_format(max(0, $total - $amountPaid), 2, '.', ''),
                'status' => $status,
                'is_walk_in' => (bool) ($payload['is_walk_in'] ?? $sale->is_walk_in),
                'notes' => $payload['notes'] ?? $sale->notes,
            ]);

            $sale->items()->delete();
            $this->syncItems($sale, $lines);

            if (array_key_exists('payments', $payload)) {
                $sale->payments()->delete();
                $this->syncPayments($sale, $payload['payments']);
            }

            return $sale->fresh(['items', 'payments', 'customer', 'cashier']);
        });
    }

    public function markPaid(PosSale $sale, PosPaymentMethod $method, float $amount, ?string $reference = null): PosSale
    {
        return DB::transaction(function () use ($sale, $method, $amount, $reference) {
            PosPayment::query()->create([
                'pos_sale_id' => $sale->id,
                'payment_method' => $method,
                'amount' => $amount,
                'reference' => $reference,
            ]);

            $paid = (float) $sale->payments()->sum('amount');
            $total = (float) $sale->total_amount;

            $sale->update([
                'amount_paid' => number_format($paid, 2, '.', ''),
                'balance_due' => number_format(max(0, $total - $paid), 2, '.', ''),
                'status' => $paid >= $total ? PosSaleStatus::Paid : $sale->status,
            ]);

            $sale->hold?->delete();

            return $sale->fresh(['items', 'payments']);
        });
    }

    /**
     * @param  list<array<string, mixed>>  $lines
     */
    protected function syncItems(PosSale $sale, array $lines): void
    {
        foreach ($lines as $line) {
            $qty = (float) ($line['quantity'] ?? 1);
            $unit = (float) ($line['unit_price'] ?? 0);
            $discount = (float) ($line['discount_amount'] ?? 0);
            $tax = (float) ($line['tax_amount'] ?? 0);

            PosSaleItem::query()->create([
                'pos_sale_id' => $sale->id,
                'inventory_item_id' => $line['inventory_item_id'] ?? null,
                'description' => $line['description'] ?? __('Item'),
                'quantity' => $qty,
                'unit_price' => $unit,
                'discount_amount' => $discount,
                'tax_amount' => $tax,
                'line_total' => $this->calculator->lineTotal($qty, $unit, $discount, $tax),
            ]);
        }
    }

    /**
     * @param  list<array<string, mixed>>  $payments
     */
    protected function syncPayments(PosSale $sale, array $payments): void
    {
        foreach ($payments as $payment) {
            PosPayment::query()->create([
                'pos_sale_id' => $sale->id,
                'payment_method' => $payment['payment_method'],
                'amount' => $payment['amount'],
                'reference' => $payment['reference'] ?? null,
            ]);
        }
    }
}
