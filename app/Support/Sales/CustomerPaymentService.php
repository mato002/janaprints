<?php

namespace App\Support\Sales;

use App\Enums\CustomerInvoiceStatus;
use App\Enums\CustomerInvoiceType;
use App\Enums\CustomerPaymentMethod;
use App\Enums\CustomerPaymentStatus;
use App\Enums\DocumentType;
use App\Enums\PostingEventCode;
use App\Models\Accounting\GlAccount;
use App\Models\Accounting\PostingAccountMapping;
use App\Models\Crm\Customer;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\CustomerPayment;
use App\Models\Sales\CustomerPaymentAllocation;
use App\Support\Accounting\AccountingPostingService;
use App\Support\Platform\NumberingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CustomerPaymentService
{
    public function __construct(
        protected NumberingService $numbering,
        protected AccountingPostingService $posting,
    ) {}

    /**
     * @param  array{
     *     payment_date: string,
     *     payment_method: CustomerPaymentMethod|string,
     *     amount: float,
     *     is_deposit?: bool,
     *     reference?: ?string,
     *     bank_reference?: ?string,
     *     mpesa_reference?: ?string,
     *     notes?: ?string,
     *     allocations?: array<int, array{customer_invoice_id: int, amount: float}>
     * }  $data
     */
    public function create(Customer $customer, int $userId, array $data): CustomerPayment
    {
        $method = $data['payment_method'] instanceof CustomerPaymentMethod
            ? $data['payment_method']
            : CustomerPaymentMethod::from($data['payment_method']);

        $amount = round((float) $data['amount'], 2);
        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => __('Payment amount must be greater than zero.'),
            ]);
        }

        $allocations = $data['allocations'] ?? [];
        $allocated = round(collect($allocations)->sum(fn ($row) => (float) ($row['amount'] ?? 0)), 2);

        if ($allocated > $amount + 0.01) {
            throw ValidationException::withMessages([
                'allocations' => __('Allocated amount cannot exceed payment amount.'),
            ]);
        }

        return DB::transaction(function () use ($customer, $userId, $data, $method, $amount, $allocations, $allocated) {
            $payment = CustomerPayment::query()->create([
                'company_id' => $customer->company_id,
                'branch_id' => $customer->branch_id,
                'customer_id' => $customer->id,
                'payment_number' => $this->numbering->next(
                    DocumentType::Payment,
                    $customer->company_id,
                    $customer->branch_id,
                ),
                'payment_date' => $data['payment_date'],
                'payment_method' => $method,
                'is_deposit' => (bool) ($data['is_deposit'] ?? false),
                'amount' => $amount,
                'allocated_amount' => $allocated,
                'unallocated_amount' => round($amount - $allocated, 2),
                'currency' => 'KES',
                'status' => CustomerPaymentStatus::Draft,
                'reference' => $data['reference'] ?? null,
                'bank_reference' => $data['bank_reference'] ?? null,
                'mpesa_reference' => $data['mpesa_reference'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => $userId,
            ]);

            $this->syncAllocations($payment, $allocations);

            return $payment->load(['allocations.invoice', 'customer']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateDraft(CustomerPayment $payment, array $data): CustomerPayment
    {
        if (! $payment->status->isEditable()) {
            throw ValidationException::withMessages([
                'payment' => __('Only draft payments can be edited.'),
            ]);
        }

        $amount = round((float) ($data['amount'] ?? $payment->amount), 2);
        $allocations = $data['allocations'] ?? $payment->allocations->map(fn ($a) => [
            'customer_invoice_id' => $a->customer_invoice_id,
            'amount' => $a->amount,
        ])->all();
        $allocated = round(collect($allocations)->sum(fn ($row) => (float) ($row['amount'] ?? 0)), 2);

        if ($allocated > $amount + 0.01) {
            throw ValidationException::withMessages([
                'allocations' => __('Allocated amount cannot exceed payment amount.'),
            ]);
        }

        return DB::transaction(function () use ($payment, $data, $amount, $allocations, $allocated) {
            $method = isset($data['payment_method'])
                ? ($data['payment_method'] instanceof CustomerPaymentMethod
                    ? $data['payment_method']
                    : CustomerPaymentMethod::from($data['payment_method']))
                : $payment->payment_method;

            $payment->update([
                'payment_date' => $data['payment_date'] ?? $payment->payment_date,
                'payment_method' => $method,
                'is_deposit' => $data['is_deposit'] ?? $payment->is_deposit,
                'amount' => $amount,
                'allocated_amount' => $allocated,
                'unallocated_amount' => round($amount - $allocated, 2),
                'reference' => $data['reference'] ?? $payment->reference,
                'bank_reference' => $data['bank_reference'] ?? $payment->bank_reference,
                'mpesa_reference' => $data['mpesa_reference'] ?? $payment->mpesa_reference,
                'notes' => $data['notes'] ?? $payment->notes,
            ]);

            $payment->allocations()->delete();
            $this->syncAllocations($payment, $allocations);

            return $payment->fresh(['allocations.invoice', 'customer']);
        });
    }

    public function post(CustomerPayment $payment, int $userId): CustomerPayment
    {
        if ($payment->status !== CustomerPaymentStatus::Draft) {
            throw ValidationException::withMessages([
                'status' => __('Only draft payments can be posted.'),
            ]);
        }

        $payment->load(['allocations.invoice', 'customer']);

        foreach ($payment->allocations as $allocation) {
            $this->assertAllocationValid($allocation->invoice, $allocation->amount, $payment);
        }

        if (! $payment->is_deposit && $payment->allocated_amount < $payment->amount - 0.01) {
            throw ValidationException::withMessages([
                'allocations' => __('Non-deposit payments must be fully allocated before posting.'),
            ]);
        }

        return DB::transaction(function () use ($payment, $userId) {
            $receiptAccountId = $this->resolveReceiptAccountId($payment);

            $journal = $this->posting->postEvent(
                PostingEventCode::PaymentReceived,
                $payment->company_id,
                $userId,
                'customer_payment',
                $payment->id,
                $payment->payment_date->toDateString(),
                [
                    'total_amount' => (float) $payment->amount,
                    'allocated_amount' => (float) $payment->allocated_amount,
                    'unallocated_amount' => (float) $payment->unallocated_amount,
                ],
                $payment->branch_id,
                reference: $payment->payment_number,
                description: $payment->notes ?? __('Customer payment :number', ['number' => $payment->payment_number]),
                accounts: ['receipt_account' => $receiptAccountId],
            );

            $payment->update([
                'status' => CustomerPaymentStatus::Posted,
                'posted_by' => $userId,
                'posted_at' => now(),
                'posted_journal_id' => $journal->id,
            ]);

            foreach ($payment->allocations as $allocation) {
                $allocation->invoice->refreshPaymentBalance();
            }

            return $payment->fresh(['postedJournal', 'poster', 'allocations.invoice']);
        });
    }

    public function cancel(CustomerPayment $payment, int $userId, ?string $reason = null): CustomerPayment
    {
        if ($payment->status !== CustomerPaymentStatus::Draft) {
            throw ValidationException::withMessages([
                'status' => __('Only draft payments can be cancelled.'),
            ]);
        }

        $payment->update([
            'status' => CustomerPaymentStatus::Cancelled,
            'cancelled_by' => $userId,
            'cancelled_at' => now(),
            'cancel_reason' => $reason,
        ]);

        return $payment;
    }

    public function deleteDraft(CustomerPayment $payment): void
    {
        if (! $payment->status->isEditable()) {
            throw ValidationException::withMessages([
                'payment' => __('Only draft payments can be deleted.'),
            ]);
        }

        $payment->delete();
    }

    /**
     * @return list<CustomerInvoice>
     */
    public function openInvoicesForCustomer(int $customerId): array
    {
        return CustomerInvoice::query()
            ->where('customer_id', $customerId)
            ->where('status', CustomerInvoiceStatus::Posted)
            ->where('balance_due', '>', 0)
            ->whereNotIn('invoice_type', [CustomerInvoiceType::CreditNote])
            ->orderBy('invoice_date')
            ->get()
            ->all();
    }

    protected function assertAllocationValid(?CustomerInvoice $invoice, float $amount, CustomerPayment $payment): void
    {
        if (! $invoice || $invoice->customer_id !== $payment->customer_id) {
            throw ValidationException::withMessages([
                'allocations' => __('Invalid invoice for this customer.'),
            ]);
        }

        if ($invoice->status !== CustomerInvoiceStatus::Posted) {
            throw ValidationException::withMessages([
                'allocations' => __('Only posted invoices can receive payments.'),
            ]);
        }

        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'allocations' => __('Allocation amount must be greater than zero.'),
            ]);
        }

        $remaining = (float) $invoice->balance_due;
        $existingDraft = (float) CustomerPaymentAllocation::query()
            ->where('customer_invoice_id', $invoice->id)
            ->whereHas('payment', fn ($q) => $q
                ->where('status', CustomerPaymentStatus::Draft)
                ->where('id', '!=', $payment->id))
            ->sum('amount');

        if ($amount > $remaining - $existingDraft + 0.01) {
            throw ValidationException::withMessages([
                'allocations' => __('Allocation exceeds balance due on invoice :number.', [
                    'number' => $invoice->invoice_number,
                ]),
            ]);
        }
    }

    /**
     * @param  array<int, array{customer_invoice_id: int, amount: float}>  $allocations
     */
    protected function syncAllocations(CustomerPayment $payment, array $allocations): void
    {
        foreach ($allocations as $row) {
            $invoiceId = (int) ($row['customer_invoice_id'] ?? 0);
            $amount = round((float) ($row['amount'] ?? 0), 2);

            if ($invoiceId <= 0 || $amount <= 0) {
                continue;
            }

            $invoice = CustomerInvoice::query()->find($invoiceId);
            $this->assertAllocationValid($invoice, $amount, $payment);

            $payment->allocations()->create([
                'customer_invoice_id' => $invoiceId,
                'amount' => $amount,
            ]);
        }
    }

    protected function resolveReceiptAccountId(CustomerPayment $payment): int
    {
        $key = $payment->payment_method === CustomerPaymentMethod::Mpesa
            ? 'mpesa_clearing'
            : $payment->payment_method->receiptAccountKey();

        $mapping = PostingAccountMapping::query()
            ->where('company_id', $payment->company_id)
            ->where('account_key', $key)
            ->first();

        if ($mapping) {
            return (int) $mapping->gl_account_id;
        }

        $code = config("posting_account_keys.{$key}.default_code");
        $account = GlAccount::query()
            ->where('company_id', $payment->company_id)
            ->where('code', $code)
            ->first();

        if (! $account) {
            throw ValidationException::withMessages([
                'account' => __('Receipt account :key is not configured.', ['key' => $key]),
            ]);
        }

        return (int) $account->id;
    }
}
