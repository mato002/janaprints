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
use Illuminate\Validation\ValidationException;

class PosSessionService
{
    public function nextSessionNumber(int $companyId): string
    {
        $prefix = 'SES-'.now()->format('Ymd').'-';
        $last = PosSession::query()
            ->where('company_id', $companyId)
            ->where('session_number', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->value('session_number');

        $sequence = 1;
        if ($last && preg_match('/-(\d+)$/', $last, $matches)) {
            $sequence = (int) $matches[1] + 1;
        }

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    public function openSession(
        int $companyId,
        int $branchId,
        int $cashierId,
        float $openingFloat,
        float $openingCash,
        int $openedBy,
        ?string $notes = null,
    ): PosSession {
        return DB::transaction(function () use ($companyId, $branchId, $cashierId, $openingFloat, $openingCash, $openedBy, $notes) {
            $existing = $this->activeSessionForCashier($companyId, $branchId, $cashierId);

            if ($existing !== null) {
                throw ValidationException::withMessages([
                    'cashier_id' => __('This cashier already has an active session at this branch.'),
                ]);
            }

            return PosSession::query()->create([
                'company_id' => $companyId,
                'branch_id' => $branchId,
                'cashier_id' => $cashierId,
                'session_number' => $this->nextSessionNumber($companyId),
                'opening_float' => $openingFloat,
                'opening_cash' => $openingCash,
                'status' => PosSessionStatus::Open,
                'opened_at' => now(),
                'opened_by' => $openedBy,
                'opening_notes' => $notes,
            ]);
        });
    }

    /**
     * @return array{
     *     held_sales: int,
     *     draft_sales: int,
     *     pending_payments: int,
     *     unapproved_returns: int,
     *     can_close: bool,
     *     checklist: list<array{key: string, label: string, passed: bool, detail: ?string}>
     * }
     */
    public function closureGovernance(PosSession $session): array
    {
        $heldSales = (int) PosSale::query()
            ->where('pos_session_id', $session->id)
            ->where('status', PosSaleStatus::Held)
            ->count();

        $draftSales = (int) PosSale::query()
            ->where('pos_session_id', $session->id)
            ->where('status', PosSaleStatus::Draft)
            ->count();

        $pendingPayments = (int) PosSale::query()
            ->where('pos_session_id', $session->id)
            ->where('balance_due', '>', 0)
            ->whereNotIn('status', [
                PosSaleStatus::Held,
                PosSaleStatus::Draft,
                PosSaleStatus::Cancelled,
                PosSaleStatus::Refunded,
            ])
            ->count();

        $unapprovedReturns = (int) PosReturn::query()
            ->where('pos_session_id', $session->id)
            ->whereIn('status', [PosReturnStatus::Pending, PosReturnStatus::Approved])
            ->count();

        $allSalesPaid = $heldSales === 0 && $draftSales === 0 && $pendingPayments === 0;
        $returnsCleared = $unapprovedReturns === 0;

        $checklist = [
            [
                'key' => 'all_sales_paid',
                'label' => __('All Sales Paid'),
                'passed' => $allSalesPaid,
                'detail' => $allSalesPaid ? null : $this->unresolvedSalesDetail($heldSales, $draftSales, $pendingPayments),
            ],
            [
                'key' => 'no_held_sales',
                'label' => __('No Held Sales'),
                'passed' => $heldSales === 0,
                'detail' => $heldSales === 0 ? null : __(':count held sale(s) must be paid or cancelled.', ['count' => $heldSales]),
            ],
            [
                'key' => 'no_draft_sales',
                'label' => __('No Draft Sales'),
                'passed' => $draftSales === 0,
                'detail' => $draftSales === 0 ? null : __(':count draft sale(s) must be completed or cancelled.', ['count' => $draftSales]),
            ],
            [
                'key' => 'returns_cleared',
                'label' => __('Returns Cleared'),
                'passed' => $returnsCleared,
                'detail' => $returnsCleared ? null : __(':count return(s) awaiting approval or completion.', ['count' => $unapprovedReturns]),
            ],
            [
                'key' => 'cash_count_completed',
                'label' => __('Cash Count Completed'),
                'passed' => true,
                'detail' => __('Enter actual closing cash below to complete this step.'),
            ],
        ];

        $canClose = $allSalesPaid && $returnsCleared;

        return [
            'held_sales' => $heldSales,
            'draft_sales' => $draftSales,
            'pending_payments' => $pendingPayments,
            'unapproved_returns' => $unapprovedReturns,
            'can_close' => $canClose,
            'checklist' => $checklist,
        ];
    }

    public function assertSessionReadyToClose(PosSession $session): void
    {
        $governance = $this->closureGovernance($session);

        if ($governance['can_close']) {
            return;
        }

        $messages = [];

        if ($governance['held_sales'] > 0) {
            $messages['held_sales'] = __('Resolve :count held sale(s) before closing the session.', ['count' => $governance['held_sales']]);
        }

        if ($governance['draft_sales'] > 0) {
            $messages['draft_sales'] = __('Resolve :count draft sale(s) before closing the session.', ['count' => $governance['draft_sales']]);
        }

        if ($governance['pending_payments'] > 0) {
            $messages['pending_payments'] = __('Complete :count pending payment(s) before closing the session.', ['count' => $governance['pending_payments']]);
        }

        if ($governance['unapproved_returns'] > 0) {
            $messages['unapproved_returns'] = __('Clear :count unapproved return(s) before closing the session.', ['count' => $governance['unapproved_returns']]);
        }

        throw ValidationException::withMessages($messages ?: [
            'session' => __('This session cannot be closed until all pre-close checks pass.'),
        ]);
    }

    protected function unresolvedSalesDetail(int $held, int $draft, int $pending): string
    {
        $parts = array_filter([
            $held > 0 ? __(':count held', ['count' => $held]) : null,
            $draft > 0 ? __(':count draft', ['count' => $draft]) : null,
            $pending > 0 ? __(':count pending payment', ['count' => $pending]) : null,
        ]);

        return implode(', ', $parts);
    }

    public function closeSession(PosSession $session, float $actualCash, int $closedBy, ?string $notes = null): PosSession
    {
        if ($session->status !== PosSessionStatus::Open) {
            throw ValidationException::withMessages([
                'status' => __('Only open sessions can be closed.'),
            ]);
        }

        $this->assertSessionReadyToClose($session);

        return DB::transaction(function () use ($session, $actualCash, $closedBy, $notes) {
            $expected = $this->expectedClosingCash($session);
            $variance = round($actualCash - $expected, 2);

            $session->update([
                'expected_cash' => $expected,
                'actual_cash' => $actualCash,
                'variance' => $variance,
                'status' => PosSessionStatus::Closed,
                'closed_at' => now(),
                'closed_by' => $closedBy,
                'closing_notes' => $notes,
            ]);

            return $session->fresh(['cashier', 'branch', 'closer']);
        });
    }

    public function activeSessionForCashier(int $companyId, int $branchId, int $cashierId): ?PosSession
    {
        return PosSession::query()
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->where('cashier_id', $cashierId)
            ->whereIn('status', [PosSessionStatus::Open, PosSessionStatus::Suspended])
            ->first();
    }

    public function requireOpenSession(int $companyId, int $branchId, int $cashierId): PosSession
    {
        $session = PosSession::query()
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->where('cashier_id', $cashierId)
            ->where('status', PosSessionStatus::Open)
            ->first();

        if ($session === null) {
            throw ValidationException::withMessages([
                'session' => __('Open a POS session before recording counter sales.'),
            ]);
        }

        return $session;
    }

    public function assertSessionAcceptsSales(PosSession $session): void
    {
        if (! $session->status->acceptsSales()) {
            throw ValidationException::withMessages([
                'session' => __('This session is not accepting new sales.'),
            ]);
        }
    }

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
     *     cash_sales: float,
     *     mpesa_sales: float,
     *     card_sales: float,
     *     bank_sales: float,
     *     refunds: int,
     *     refund_total: float,
     *     expected_closing_cash: float
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

        $completedReturns = PosReturn::query()
            ->where('pos_session_id', $session->id)
            ->where('status', PosReturnStatus::Completed);

        $legacyRefunds = PosSale::query()
            ->where('pos_session_id', $session->id)
            ->where('status', PosSaleStatus::Refunded);

        return [
            'sales_count' => (int) PosSale::query()
                ->where('pos_session_id', $session->id)
                ->whereIn('status', [PosSaleStatus::Paid, PosSaleStatus::PartiallyRefunded])
                ->count(),
            'cash_sales' => (float) ($payments[PosPaymentMethod::Cash->value] ?? 0),
            'mpesa_sales' => (float) ($payments[PosPaymentMethod::Mpesa->value] ?? 0),
            'card_sales' => (float) ($payments[PosPaymentMethod::Card->value] ?? 0),
            'bank_sales' => (float) ($payments[PosPaymentMethod::Bank->value] ?? 0),
            'refunds' => (int) $completedReturns->count() + (int) $legacyRefunds->count(),
            'refund_total' => (float) $completedReturns->sum('refund_amount') + (float) $legacyRefunds->sum('total_amount'),
            'expected_closing_cash' => $this->expectedClosingCash($session),
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
