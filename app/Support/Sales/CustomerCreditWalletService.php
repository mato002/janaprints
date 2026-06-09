<?php

namespace App\Support\Sales;

use App\Enums\CustomerPaymentStatus;
use App\Models\Sales\CustomerPayment;

class CustomerCreditWalletService
{
    public function __construct(
        protected ReceivablesBranchScope $branchScope,
    ) {}

    /**
     * @return array{
     *     available_credit: float,
     *     used_credit: float,
     *     remaining_credit: float,
     *     deposits: list<array{
     *         payment_id: int,
     *         payment_number: string,
     *         payment_date: string,
     *         credit_issued: float,
     *         credit_applied: float,
     *         credit_refunded: float,
     *         credit_remaining: float
     *     }>
     * }
     */
    public function summary(int $customerId, ?int $branchId = null): array
    {
        $branchId = $this->branchScope->resolve($branchId);

        $query = CustomerPayment::query()
            ->where('customer_id', $customerId)
            ->where('is_deposit', true)
            ->where('status', CustomerPaymentStatus::Posted)
            ->where('credit_issued', '>', 0);

        if ($branchId !== null) {
            $query->where('branch_id', $branchId);
        }

        $deposits = $query->orderByDesc('payment_date')->get();

        $rows = $deposits->map(fn (CustomerPayment $payment) => [
            'payment_id' => $payment->id,
            'payment_number' => $payment->payment_number,
            'payment_date' => $payment->payment_date->toDateString(),
            'credit_issued' => round((float) $payment->credit_issued, 2),
            'credit_applied' => round((float) $payment->credit_applied, 2),
            'credit_refunded' => round((float) $payment->credit_refunded, 2),
            'credit_remaining' => round((float) $payment->credit_remaining, 2),
        ])->values()->all();

        return [
            'available_credit' => round($deposits->sum(fn ($p) => (float) $p->credit_issued), 2),
            'used_credit' => round($deposits->sum(fn ($p) => (float) $p->credit_applied), 2),
            'remaining_credit' => round($deposits->sum(fn ($p) => (float) $p->credit_remaining), 2),
            'deposits' => $rows,
        ];
    }

    /**
     * @return list<CustomerPayment>
     */
    public function depositsWithCredit(int $customerId, ?int $branchId = null, bool $ignoreBranchFilter = false): array
    {
        if (! $ignoreBranchFilter) {
            $branchId = $this->branchScope->resolve($branchId);
        }

        $query = CustomerPayment::query()
            ->with('branch')
            ->where('customer_id', $customerId)
            ->where('is_deposit', true)
            ->where('status', CustomerPaymentStatus::Posted)
            ->where('credit_remaining', '>', 0);

        if ($branchId !== null) {
            $query->where('branch_id', $branchId);
        }

        return $query->orderBy('payment_date')->get()->all();
    }
}
