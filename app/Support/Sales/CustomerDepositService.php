<?php

namespace App\Support\Sales;

use App\Enums\CustomerDepositApplicationStatus;
use App\Enums\CustomerDepositRefundStatus;
use App\Enums\CustomerInvoiceStatus;
use App\Enums\CustomerInvoiceType;
use App\Enums\CustomerPaymentMethod;
use App\Enums\CustomerPaymentStatus;
use App\Enums\PostingEventCode;
use App\Models\Accounting\GlAccount;
use App\Models\Accounting\PostingAccountMapping;
use App\Models\Sales\CustomerDepositApplication;
use App\Models\Sales\CustomerDepositRefund;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\CustomerPayment;
use App\Models\User;
use App\Support\Accounting\AccountingPostingService;
use App\Support\ActivityLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CustomerDepositService
{
    public function __construct(
        protected AccountingPostingService $posting,
    ) {}

    /**
     * @param  array{application_date?: string, notes?: ?string, override_reason?: ?string}  $data
     */
    public function applyToInvoice(
        CustomerPayment $deposit,
        CustomerInvoice $invoice,
        float $amount,
        int $userId,
        array $data = [],
    ): CustomerDepositApplication {
        $user = User::query()->findOrFail($userId);

        $this->assertDepositHasCredit($deposit);
        $this->assertInvoiceReceivable($invoice, $deposit, $user, $data['override_reason'] ?? null);

        $amount = round($amount, 2);
        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => __('Application amount must be greater than zero.'),
            ]);
        }

        if ($amount > (float) $deposit->credit_remaining + 0.01) {
            throw ValidationException::withMessages([
                'amount' => __('Amount exceeds remaining deposit credit.'),
            ]);
        }

        if ($amount > (float) $invoice->balance_due + 0.01) {
            throw ValidationException::withMessages([
                'amount' => __('Amount exceeds invoice balance due.'),
            ]);
        }

        $isCrossBranch = $deposit->branch_id !== $invoice->branch_id;

        return DB::transaction(function () use ($deposit, $invoice, $amount, $userId, $user, $data, $isCrossBranch) {
            $applicationDate = $data['application_date'] ?? now()->toDateString();
            $sequence = CustomerDepositApplication::query()
                ->where('customer_payment_id', $deposit->id)
                ->count() + 1;

            $application = CustomerDepositApplication::query()->create([
                'company_id' => $deposit->company_id,
                'branch_id' => $deposit->branch_id,
                'source_branch_id' => $deposit->branch_id,
                'target_branch_id' => $invoice->branch_id,
                'is_cross_branch' => $isCrossBranch,
                'override_reason' => $isCrossBranch ? ($data['override_reason'] ?? null) : null,
                'customer_id' => $deposit->customer_id,
                'customer_payment_id' => $deposit->id,
                'customer_invoice_id' => $invoice->id,
                'application_number' => $deposit->payment_number.'-A'.str_pad((string) $sequence, 3, '0', STR_PAD_LEFT),
                'application_date' => $applicationDate,
                'amount' => $amount,
                'status' => CustomerDepositApplicationStatus::Posted,
                'notes' => $data['notes'] ?? null,
                'created_by' => $userId,
            ]);

            $journal = $this->posting->postEvent(
                PostingEventCode::DepositApplicationPosted,
                $deposit->company_id,
                $userId,
                'customer_deposit_application',
                $application->id,
                $applicationDate,
                ['total_amount' => $amount],
                $deposit->branch_id,
                reference: $application->application_number,
                description: $data['notes'] ?? __('Apply deposit :payment to invoice :invoice', [
                    'payment' => $deposit->payment_number,
                    'invoice' => $invoice->invoice_number,
                ]),
            );

            $application->update([
                'posted_by' => $userId,
                'posted_at' => now(),
                'posted_journal_id' => $journal->id,
            ]);

            $deposit->update([
                'credit_applied' => round((float) $deposit->credit_applied + $amount, 2),
                'credit_remaining' => round((float) $deposit->credit_remaining - $amount, 2),
            ]);

            $invoice->refreshPaymentBalance();

            if ($isCrossBranch) {
                ActivityLogger::log('cross_branch_deposit_application', $application, $userId, [
                    'source_branch_id' => $deposit->branch_id,
                    'target_branch_id' => $invoice->branch_id,
                    'override_reason' => $data['override_reason'] ?? null,
                    'amount' => $amount,
                    'deposit_number' => $deposit->payment_number,
                    'invoice_number' => $invoice->invoice_number,
                ]);
            }

            return $application->fresh(['invoice', 'depositPayment', 'postedJournal', 'sourceBranch', 'targetBranch', 'creator']);
        });
    }

    /**
     * @param  array{
     *     refund_date?: string,
     *     payment_method?: CustomerPaymentMethod|string,
     *     reference?: ?string,
     *     notes?: ?string
     * }  $data
     */
    public function refund(CustomerPayment $deposit, float $amount, int $userId, array $data = []): CustomerDepositRefund
    {
        $this->assertDepositHasCredit($deposit);

        $amount = round($amount, 2);
        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => __('Refund amount must be greater than zero.'),
            ]);
        }

        if ($amount > (float) $deposit->credit_remaining + 0.01) {
            throw ValidationException::withMessages([
                'amount' => __('Amount exceeds remaining deposit credit.'),
            ]);
        }

        $method = isset($data['payment_method'])
            ? ($data['payment_method'] instanceof CustomerPaymentMethod
                ? $data['payment_method']
                : CustomerPaymentMethod::from($data['payment_method']))
            : $deposit->payment_method;

        return DB::transaction(function () use ($deposit, $amount, $userId, $data, $method) {
            $refundDate = $data['refund_date'] ?? now()->toDateString();
            $sequence = CustomerDepositRefund::query()
                ->where('customer_payment_id', $deposit->id)
                ->count() + 1;

            $refund = CustomerDepositRefund::query()->create([
                'company_id' => $deposit->company_id,
                'branch_id' => $deposit->branch_id,
                'customer_id' => $deposit->customer_id,
                'customer_payment_id' => $deposit->id,
                'refund_number' => $deposit->payment_number.'-R'.str_pad((string) $sequence, 3, '0', STR_PAD_LEFT),
                'refund_date' => $refundDate,
                'payment_method' => $method,
                'amount' => $amount,
                'status' => CustomerDepositRefundStatus::Posted,
                'reference' => $data['reference'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => $userId,
            ]);

            $receiptAccountId = $this->resolveReceiptAccountId($deposit->company_id, $method);

            $journal = $this->posting->postEvent(
                PostingEventCode::DepositRefundPosted,
                $deposit->company_id,
                $userId,
                'customer_deposit_refund',
                $refund->id,
                $refundDate,
                ['total_amount' => $amount],
                $deposit->branch_id,
                reference: $refund->refund_number,
                description: $data['notes'] ?? __('Refund deposit :payment', ['payment' => $deposit->payment_number]),
                accounts: ['receipt_account' => $receiptAccountId],
            );

            $refund->update([
                'posted_by' => $userId,
                'posted_at' => now(),
                'posted_journal_id' => $journal->id,
            ]);

            $deposit->update([
                'credit_refunded' => round((float) $deposit->credit_refunded + $amount, 2),
                'credit_remaining' => round((float) $deposit->credit_remaining - $amount, 2),
            ]);

            return $refund->fresh(['depositPayment', 'postedJournal']);
        });
    }

    protected function assertDepositHasCredit(CustomerPayment $deposit): void
    {
        if ($deposit->status !== CustomerPaymentStatus::Posted || ! $deposit->is_deposit) {
            throw ValidationException::withMessages([
                'deposit' => __('Only posted customer deposits can be applied or refunded.'),
            ]);
        }

        if ((float) $deposit->credit_remaining <= 0) {
            throw ValidationException::withMessages([
                'deposit' => __('This deposit has no remaining credit.'),
            ]);
        }
    }

    protected function assertInvoiceReceivable(
        CustomerInvoice $invoice,
        CustomerPayment $deposit,
        User $user,
        ?string $overrideReason = null,
    ): void {
        if ($invoice->customer_id !== $deposit->customer_id) {
            throw ValidationException::withMessages([
                'invoice' => __('Invoice does not belong to this customer.'),
            ]);
        }

        if ($invoice->status !== CustomerInvoiceStatus::Posted) {
            throw ValidationException::withMessages([
                'invoice' => __('Only posted invoices can receive deposit applications.'),
            ]);
        }

        if ($invoice->invoice_type === CustomerInvoiceType::CreditNote) {
            throw ValidationException::withMessages([
                'invoice' => __('Credit notes cannot receive deposit applications.'),
            ]);
        }

        if ((float) $invoice->balance_due <= 0) {
            throw ValidationException::withMessages([
                'invoice' => __('Invoice has no balance due.'),
            ]);
        }

        $this->assertBranchAllocationAllowed($invoice, $deposit, $user, $overrideReason);
    }

    protected function assertBranchAllocationAllowed(
        CustomerInvoice $invoice,
        CustomerPayment $deposit,
        User $user,
        ?string $overrideReason,
    ): void {
        if ($invoice->branch_id === $deposit->branch_id) {
            return;
        }

        if (! $user->can('finance.cross_branch.allocate')) {
            throw ValidationException::withMessages([
                'invoice' => __('Deposit and invoice must belong to the same branch.'),
            ]);
        }

        if (trim((string) $overrideReason) === '') {
            throw ValidationException::withMessages([
                'override_reason' => __('A reason is required for cross-branch deposit allocation.'),
            ]);
        }
    }

    protected function resolveReceiptAccountId(int $companyId, CustomerPaymentMethod $method): int
    {
        $key = $method === CustomerPaymentMethod::Mpesa
            ? 'mpesa_clearing'
            : $method->receiptAccountKey();

        $mapping = PostingAccountMapping::query()
            ->where('company_id', $companyId)
            ->where('account_key', $key)
            ->first();

        if ($mapping) {
            return (int) $mapping->gl_account_id;
        }

        $code = config("posting_account_keys.{$key}.default_code");
        $account = GlAccount::query()
            ->where('company_id', $companyId)
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
