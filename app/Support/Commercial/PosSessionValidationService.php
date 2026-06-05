<?php

namespace App\Support\Commercial;

use App\Enums\PosReturnStatus;
use App\Enums\PosSaleStatus;
use App\Enums\PosSessionStatus;
use App\Models\Pos\PosReturn;
use App\Models\Pos\PosSale;
use App\Models\Pos\PosSession;
use Illuminate\Validation\ValidationException;

class PosSessionValidationService
{
    public function assertNoDuplicateActiveSession(int $companyId, int $branchId, int $cashierId): void
    {
        $existing = PosSession::query()
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->where('cashier_id', $cashierId)
            ->whereIn('status', [PosSessionStatus::Open, PosSessionStatus::Suspended])
            ->exists();

        if ($existing) {
            throw ValidationException::withMessages([
                'cashier_id' => __('This cashier already has an active session at this branch.'),
            ]);
        }
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
                'session' => __('Open a cashier session before processing sales.'),
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

    public function assertSessionOwnedByCashier(PosSession $session, int $cashierId): void
    {
        if ((int) $session->cashier_id !== $cashierId) {
            throw ValidationException::withMessages([
                'session' => __('This sale must be processed under your own active session.'),
            ]);
        }
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

        return [
            'held_sales' => $heldSales,
            'draft_sales' => $draftSales,
            'pending_payments' => $pendingPayments,
            'unapproved_returns' => $unapprovedReturns,
            'can_close' => $allSalesPaid && $returnsCleared,
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
}
