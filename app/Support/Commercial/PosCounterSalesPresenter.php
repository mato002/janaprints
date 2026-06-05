<?php

namespace App\Support\Commercial;

use App\Models\Pos\PosSale;
use App\Models\Pos\PosSaleHold;
use App\Models\Pos\PosSession;
use Illuminate\Support\Collection;

class PosCounterSalesPresenter
{
    public function __construct(
        protected PosSessionService $sessions,
        protected PosSessionVarianceService $variance,
    ) {}

    /**
     * @return array{session: ?array<string, mixed>, metrics: ?array<string, mixed>, has_session: bool}
     */
    public function sessionWidget(?PosSession $session): array
    {
        if ($session === null) {
            return [
                'has_session' => false,
                'session' => null,
                'metrics' => null,
            ];
        }

        $metrics = $this->sessions->sessionMetrics($session);

        return [
            'has_session' => true,
            'session' => [
                'id' => $session->id,
                'session_number' => $session->session_number,
                'cashier_name' => $session->cashier?->name,
                'opened_at' => $session->opened_at?->format('Y-m-d H:i'),
                'opening_float' => (float) $session->opening_float,
                'terminal' => $session->terminal,
            ],
            'metrics' => [
                'sales_count' => $metrics['sales_count'],
                'total_sales_value' => $metrics['total_sales_value'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function closePreview(PosSession $session): array
    {
        $metrics = $this->sessions->sessionMetrics($session);
        $governance = $this->sessions->closureGovernance($session);

        return [
            'expected_cash' => $metrics['expected_closing_cash'],
            'expected_mpesa' => $metrics['expected_mpesa'],
            'expected_card' => $metrics['expected_card'],
            'expected_bank' => $metrics['expected_bank'],
            'expected_total' => $metrics['expected_total'],
            'variance_tolerance' => $this->variance->tolerance(),
            'can_close' => $governance['can_close'],
            'governance' => $governance,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function heldCartPayload(PosSale $sale): array
    {
        $sale->loadMissing(['items', 'customer']);

        return [
            'sale_id' => $sale->id,
            'sale_number' => $sale->sale_number,
            'pay_url' => route('admin.commercial.pos.pay', $sale),
            'cancel_url' => route('admin.commercial.pos.cancel', $sale),
            'lines' => $sale->items->map(fn ($item) => [
                'item_id' => $item->inventory_item_id ?? '',
                'description' => $item->description,
                'quantity' => (float) $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'discount_amount' => (float) $item->discount_amount,
                'tax_amount' => (float) $item->tax_amount,
            ])->values()->all(),
            'saleDiscount' => (float) $sale->discount_amount,
            'saleTax' => (float) $sale->tax_amount,
            'walkIn' => $sale->is_walk_in,
            'customerId' => $sale->customer_id ?? '',
            'held_at' => $sale->hold?->held_at?->format('Y-m-d H:i'),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function heldSalesList(Collection $holds): array
    {
        return $holds->map(fn (PosSaleHold $hold) => [
            'id' => $hold->id,
            'sale_id' => $hold->pos_sale_id,
            'sale_number' => $hold->sale?->sale_number,
            'customer' => $hold->customer?->company_name ?? __('Walk-in'),
            'cashier' => $hold->cashier?->name,
            'held_at' => $hold->held_at?->format('Y-m-d H:i'),
            'value' => (float) ($hold->sale?->total_amount ?? 0),
            'resume_url' => route('admin.commercial.pos.counter-sales.held-sales.resume', $hold->sale),
            'cancel_url' => route('admin.commercial.pos.cancel', $hold->sale),
        ])->values()->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function receiptPayload(PosSale $sale): array
    {
        $sale->loadMissing(['items', 'payments', 'customer', 'cashier', 'branch']);

        return [
            'id' => $sale->id,
            'sale_number' => $sale->sale_number,
            'branch_name' => $sale->branch?->name ?? config('app.name'),
            'sale_date' => $sale->sale_date?->format('Y-m-d H:i'),
            'cashier_name' => $sale->cashier?->name,
            'customer_label' => $sale->is_walk_in
                ? __('Walk-in')
                : ($sale->customer?->company_name ?? '—'),
            'subtotal' => (float) $sale->subtotal,
            'discount_amount' => (float) $sale->discount_amount,
            'tax_amount' => (float) $sale->tax_amount,
            'total_amount' => (float) $sale->total_amount,
            'amount_paid' => (float) $sale->amount_paid,
            'items' => $sale->items->map(fn ($item) => [
                'description' => $item->description,
                'quantity' => (float) $item->quantity,
                'line_total' => (float) $item->line_total,
            ])->values()->all(),
            'payments' => $sale->payments->map(fn ($payment) => [
                'method' => ucfirst(str_replace('_', ' ', $payment->payment_method->value)),
                'amount' => (float) $payment->amount,
            ])->values()->all(),
            'full_receipt_url' => route('admin.commercial.pos.receipt', $sale),
        ];
    }
}
