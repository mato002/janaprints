<?php

namespace App\Support\Accounting;

use App\Enums\JournalStatus;
use App\Enums\PosPaymentMethod;
use App\Enums\PosRefundMethod;
use App\Enums\PosSaleStatus;
use App\Enums\PosVarianceType;
use App\Enums\PostingEventCode;
use App\Models\Accounting\Journal;
use App\Models\Accounting\PostingRule;
use App\Models\Pos\PosCashReconciliation;
use App\Models\Pos\PosPayment;
use App\Models\Pos\PosReturn;
use App\Models\Pos\PosSale;

class PosAccountingPostingService
{
    public function __construct(
        protected AccountingPostingService $posting,
        protected PostingAccountResolverService $accounts,
    ) {}

    public function postPaidSale(PosSale $sale, int $userId): void
    {
        if ($sale->status !== PosSaleStatus::Paid) {
            return;
        }

        $sale->loadMissing('payments');

        foreach ($sale->payments as $payment) {
            $this->postPayment($payment, $sale, $userId);
        }
    }

    public function postReturn(PosReturn $return, int $userId): void
    {
        if ($return->refund_method === PosRefundMethod::NoRefund) {
            return;
        }

        $refundAmount = (float) $return->refund_amount;

        if ($refundAmount <= 0) {
            return;
        }

        if ($this->findPostedReturnJournal($return)) {
            return;
        }

        if (! $this->hasActiveRule(PostingEventCode::PosReturn, $return->company_id)) {
            return;
        }

        $refundAccountId = $this->resolveRefundAccountId($return);

        $journal = $this->posting->postEvent(
            PostingEventCode::PosReturn,
            $return->company_id,
            $userId,
            'pos_return',
            $return->id,
            $return->completed_at?->toDateString() ?? now()->toDateString(),
            ['total_amount' => $refundAmount],
            $return->branch_id,
            reference: $return->return_number,
            description: __('POS return :number', ['number' => $return->return_number]),
            accounts: ['refund_account' => $refundAccountId],
        );

        $return->update(['posted_journal_id' => $journal->id]);
    }

    public function postVariance(PosCashReconciliation $reconciliation, int $userId): void
    {
        if ($reconciliation->variance_type === PosVarianceType::Balanced) {
            return;
        }

        if ($reconciliation->posted_journal_id !== null) {
            return;
        }

        $variance = round(abs((float) $reconciliation->variance), 2);

        if ($variance <= 0) {
            return;
        }

        $amounts = match ($reconciliation->variance_type) {
            PosVarianceType::Short => ['short_amount' => $variance, 'over_amount' => 0.0],
            PosVarianceType::Over => ['short_amount' => 0.0, 'over_amount' => $variance],
            default => ['short_amount' => 0.0, 'over_amount' => 0.0],
        };

        if ($amounts['short_amount'] <= 0 && $amounts['over_amount'] <= 0) {
            return;
        }

        if (! $this->hasActiveRule(PostingEventCode::PosVariance, $reconciliation->company_id)) {
            return;
        }

        $journal = $this->posting->postEvent(
            PostingEventCode::PosVariance,
            $reconciliation->company_id,
            $userId,
            'pos_cash_reconciliation',
            $reconciliation->id,
            $reconciliation->approved_at?->toDateString() ?? now()->toDateString(),
            $amounts,
            $reconciliation->branch_id,
            reference: $reconciliation->reconciliation_number,
            description: __('POS cash variance :number', ['number' => $reconciliation->reconciliation_number]),
        );

        $reconciliation->update(['posted_journal_id' => $journal->id]);
    }

    protected function postPayment(PosPayment $payment, PosSale $sale, int $userId): void
    {
        if ($payment->posted_journal_id !== null) {
            return;
        }

        $event = $this->saleEventForMethod($payment->payment_method);

        if ($event === null || ! $this->hasActiveRule($event, $sale->company_id)) {
            return;
        }

        $amount = (float) $payment->amount;

        if ($amount <= 0) {
            return;
        }

        $journal = $this->posting->postEvent(
            $event,
            $sale->company_id,
            $userId,
            'pos_payment',
            $payment->id,
            $sale->sale_date?->toDateString() ?? now()->toDateString(),
            ['total_amount' => $amount],
            $sale->branch_id,
            reference: $sale->sale_number,
            description: __('POS sale :number (:method)', [
                'number' => $sale->sale_number,
                'method' => $payment->payment_method->value,
            ]),
        );

        $payment->update(['posted_journal_id' => $journal->id]);
    }

    protected function saleEventForMethod(PosPaymentMethod $method): ?PostingEventCode
    {
        return match ($method) {
            PosPaymentMethod::Cash => PostingEventCode::PosSaleCash,
            PosPaymentMethod::Mpesa => PostingEventCode::PosSaleMpesa,
            PosPaymentMethod::Card => PostingEventCode::PosSaleCard,
            default => null,
        };
    }

    protected function resolveRefundAccountId(PosReturn $return): int
    {
        $key = match ($return->refund_method) {
            PosRefundMethod::Cash => 'cash_till',
            PosRefundMethod::Mpesa => 'mpesa_clearing',
            PosRefundMethod::Card => 'card_clearing',
            PosRefundMethod::StoreCredit => 'customer_deposits',
            default => 'cash_till',
        };

        return $this->accounts->resolveGlAccountId($return->company_id, $key);
    }

    protected function findPostedReturnJournal(PosReturn $return): ?Journal
    {
        return Journal::query()
            ->where('company_id', $return->company_id)
            ->where('posting_event', PostingEventCode::PosReturn->value)
            ->where('source_type', 'pos_return')
            ->where('source_id', $return->id)
            ->where('status', JournalStatus::Posted)
            ->first();
    }

    protected function hasActiveRule(PostingEventCode $event, int $companyId): bool
    {
        return PostingRule::query()
            ->where('company_id', $companyId)
            ->where('event_code', $event->value)
            ->where('is_active', true)
            ->exists();
    }
}
