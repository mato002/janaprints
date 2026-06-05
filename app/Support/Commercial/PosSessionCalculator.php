<?php

namespace App\Support\Commercial;

use App\Enums\PosPaymentMethod;
use App\Enums\PosRefundMethod;
use App\Enums\PosReturnStatus;
use App\Enums\PosSaleStatus;
use App\Enums\PosSessionStatus;
use App\Models\Pos\PosPayment;
use App\Models\Pos\PosReturn;
use App\Models\Pos\PosSale;
use App\Models\Pos\PosSession;
use Illuminate\Support\Facades\DB;

class PosSessionCalculator
{
    public function expectedClosingCash(PosSession $session): float
    {
        $opening = (float) $session->opening_float + (float) $session->opening_cash;

        $cashIn = (float) PosPayment::query()
            ->whereHas('sale', fn ($q) => $q
                ->where('pos_session_id', $session->id)
                ->where('status', PosSaleStatus::Paid))
            ->where('payment_method', PosPaymentMethod::Cash)
            ->sum('amount');

        $cashOut = (float) PosReturn::query()
            ->where('pos_session_id', $session->id)
            ->where('status', PosReturnStatus::Completed)
            ->where('refund_method', PosRefundMethod::Cash)
            ->sum('refund_amount');

        $legacyCashOut = (float) PosPayment::query()
            ->whereHas('sale', fn ($q) => $q
                ->where('pos_session_id', $session->id)
                ->where('status', PosSaleStatus::Refunded))
            ->where('payment_method', PosPaymentMethod::Cash)
            ->sum('amount');

        return round($opening + $cashIn - $cashOut - $legacyCashOut, 2);
    }

    /**
     * @return array{
     *     sales_count: int,
     *     transactions_count: int,
     *     total_sales_value: float,
     *     cash_sales: float,
     *     mpesa_sales: float,
     *     card_sales: float,
     *     bank_sales: float,
     *     refunds: int,
     *     refund_total: float,
     *     held_sales: int,
     *     cancelled_sales: int,
     *     expected_closing_cash: float,
     *     expected_mpesa: float,
     *     expected_card: float,
     *     expected_bank: float,
     *     expected_total: float
     * }
     */
    public function sessionMetrics(PosSession $session): array
    {
        $saleIds = PosSale::query()
            ->where('pos_session_id', $session->id)
            ->pluck('id');

        $payments = $saleIds->isEmpty()
            ? collect()
            : PosPayment::query()
                ->whereIn('pos_sale_id', $saleIds)
                ->whereHas('sale', fn ($q) => $q->where('status', PosSaleStatus::Paid))
                ->select('payment_method', DB::raw('SUM(amount) as total'))
                ->groupBy('payment_method')
                ->pluck('total', 'payment_method');

        $cashSales = (float) ($payments[PosPaymentMethod::Cash->value] ?? 0);
        $mpesaSales = (float) ($payments[PosPaymentMethod::Mpesa->value] ?? 0);
        $cardSales = (float) ($payments[PosPaymentMethod::Card->value] ?? 0);
        $bankSales = (float) ($payments[PosPaymentMethod::Bank->value] ?? 0);

        $completedReturns = PosReturn::query()
            ->where('pos_session_id', $session->id)
            ->where('status', PosReturnStatus::Completed);

        $legacyRefunds = PosSale::query()
            ->where('pos_session_id', $session->id)
            ->where('status', PosSaleStatus::Refunded);

        $paidSales = PosSale::query()
            ->where('pos_session_id', $session->id)
            ->whereIn('status', [PosSaleStatus::Paid, PosSaleStatus::PartiallyRefunded]);

        return [
            'sales_count' => (int) $paidSales->count(),
            'transactions_count' => (int) PosSale::query()
                ->where('pos_session_id', $session->id)
                ->whereNotIn('status', [PosSaleStatus::Draft])
                ->count(),
            'total_sales_value' => round((float) $paidSales->sum('total_amount'), 2),
            'cash_sales' => $cashSales,
            'mpesa_sales' => $mpesaSales,
            'card_sales' => $cardSales,
            'bank_sales' => $bankSales,
            'refunds' => (int) $completedReturns->count() + (int) $legacyRefunds->count(),
            'refund_total' => round(
                (float) $completedReturns->sum('refund_amount') + (float) $legacyRefunds->sum('total_amount'),
                2,
            ),
            'held_sales' => (int) PosSale::query()
                ->where('pos_session_id', $session->id)
                ->where('status', PosSaleStatus::Held)
                ->count(),
            'cancelled_sales' => (int) PosSale::query()
                ->where('pos_session_id', $session->id)
                ->where('status', PosSaleStatus::Cancelled)
                ->count(),
            'expected_closing_cash' => $this->expectedClosingCash($session),
            'expected_mpesa' => $mpesaSales,
            'expected_card' => $cardSales,
            'expected_bank' => $bankSales,
            'expected_total' => round($cashSales + $mpesaSales + $cardSales + $bankSales, 2),
        ];
    }

    /**
     * @return array{
     *     open_sessions: int,
     *     closed_today: int,
     *     sales_today: int,
     *     expected_cash: float,
     *     actual_cash: float,
     *     variance: float
     * }
     */
    public function dashboardStats(int $companyId, ?int $branchId): array
    {
        $base = PosSession::query()->where('company_id', $companyId);
        if ($branchId !== null) {
            $base->where('branch_id', $branchId);
        }

        $open = (clone $base)->where('status', PosSessionStatus::Open)->get();
        $closedToday = (clone $base)
            ->where('status', PosSessionStatus::Closed)
            ->whereDate('closed_at', today());

        $sessionIds = (clone $base)->pluck('id');

        $salesToday = $sessionIds->isEmpty()
            ? 0
            : (int) PosSale::query()
                ->whereIn('pos_session_id', $sessionIds)
                ->whereDate('sale_date', today())
                ->where('status', PosSaleStatus::Paid)
                ->count();

        return [
            'open_sessions' => $open->count(),
            'closed_today' => (int) $closedToday->count(),
            'sales_today' => $salesToday,
            'expected_cash' => round($open->sum(fn (PosSession $s) => $this->expectedClosingCash($s)), 2),
            'actual_cash' => round((float) $closedToday->sum('actual_cash'), 2),
            'variance' => round((float) $closedToday->sum('variance'), 2),
        ];
    }
}
