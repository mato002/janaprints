<?php

namespace App\Support\Procurement;

use App\Enums\DocumentType;
use App\Enums\PostingEventCode;
use App\Enums\SupplierBillStatus;
use App\Enums\SupplierBillType;
use App\Enums\SupplierPaymentMethod;
use App\Enums\SupplierPaymentStatus;
use App\Models\Accounting\GlAccount;
use App\Models\Accounting\PostingAccountMapping;
use App\Models\Procurement\SupplierBill;
use App\Models\Procurement\SupplierPayment;
use App\Models\Procurement\SupplierPaymentAllocation;
use App\Models\Procurement\Vendor;
use App\Models\Tax\TaxCode;
use App\Support\Accounting\AccountingPostingService;
use App\Support\Platform\NumberingService;
use App\Support\Tax\TaxRateResolver;
use App\Support\Tax\TaxTransactionRecorder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SupplierPaymentService
{
    public function __construct(
        protected NumberingService $numbering,
        protected AccountingPostingService $posting,
        protected TaxRateResolver $taxRates,
        protected TaxTransactionRecorder $taxRecorder,
    ) {}

    /**
     * @param  array{
     *     payment_date: string,
     *     payment_method: SupplierPaymentMethod|string,
     *     amount: float,
     *     reference?: ?string,
     *     bank_reference?: ?string,
     *     notes?: ?string,
     *     allocations?: array<int, array{supplier_bill_id: int, amount: float}>
     * }  $data
     */
    public function create(Vendor $vendor, int $userId, array $data): SupplierPayment
    {
        $method = $data['payment_method'] instanceof SupplierPaymentMethod
            ? $data['payment_method']
            : SupplierPaymentMethod::from($data['payment_method']);

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

        [$whtCodeId, $whtAmount] = $this->resolveWithholding($vendor->company_id, $amount, $data);

        return DB::transaction(function () use ($vendor, $userId, $data, $method, $amount, $allocations, $allocated, $whtCodeId, $whtAmount) {
            $payment = SupplierPayment::query()->create([
                'company_id' => $vendor->company_id,
                'branch_id' => $data['branch_id'] ?? tenant()->branchId() ?? auth()->user()?->default_branch_id,
                'vendor_id' => $vendor->id,
                'withholding_tax_code_id' => $whtCodeId,
                'withholding_tax_amount' => $whtAmount,
                'payment_number' => $this->numbering->next(
                    DocumentType::SupplierPayment,
                    $vendor->company_id,
                    $vendor->branch_id ?? null,
                ),
                'payment_date' => $data['payment_date'],
                'payment_method' => $method,
                'amount' => $amount,
                'allocated_amount' => $allocated,
                'unallocated_amount' => round($amount - $allocated, 2),
                'currency' => 'KES',
                'status' => SupplierPaymentStatus::Draft,
                'reference' => $data['reference'] ?? null,
                'bank_reference' => $data['bank_reference'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => $userId,
            ]);

            $this->syncAllocations($payment, $allocations);

            return $payment->load(['allocations.bill', 'vendor']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateDraft(SupplierPayment $payment, array $data): SupplierPayment
    {
        if (! $payment->status->isEditable()) {
            throw ValidationException::withMessages([
                'payment' => __('Only draft payments can be edited.'),
            ]);
        }

        $amount = round((float) ($data['amount'] ?? $payment->amount), 2);
        $allocations = $data['allocations'] ?? $payment->allocations->map(fn ($a) => [
            'supplier_bill_id' => $a->supplier_bill_id,
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
                ? ($data['payment_method'] instanceof SupplierPaymentMethod
                    ? $data['payment_method']
                    : SupplierPaymentMethod::from($data['payment_method']))
                : $payment->payment_method;

            $paymentDate = $data['payment_date'] ?? $payment->payment_date->toDateString();
            [$whtCodeId, $whtAmount] = $this->resolveWithholding($payment->company_id, $amount, [
                ...$data,
                'payment_date' => $paymentDate,
            ]);

            $payment->update([
                'payment_date' => $paymentDate,
                'payment_method' => $method,
                'amount' => $amount,
                'withholding_tax_code_id' => $whtCodeId,
                'withholding_tax_amount' => $whtAmount,
                'allocated_amount' => $allocated,
                'unallocated_amount' => round($amount - $allocated, 2),
                'reference' => $data['reference'] ?? $payment->reference,
                'bank_reference' => $data['bank_reference'] ?? $payment->bank_reference,
                'notes' => $data['notes'] ?? $payment->notes,
            ]);

            $payment->allocations()->delete();
            $this->syncAllocations($payment, $allocations);

            return $payment->fresh(['allocations.bill', 'vendor']);
        });
    }

    public function post(SupplierPayment $payment, int $userId): SupplierPayment
    {
        if ($payment->status !== SupplierPaymentStatus::Draft) {
            throw ValidationException::withMessages([
                'status' => __('Only draft payments can be posted.'),
            ]);
        }

        $payment->load(['allocations.bill', 'vendor']);

        foreach ($payment->allocations as $allocation) {
            $this->assertAllocationValid($allocation->bill, $allocation->amount, $payment);
        }

        if ($payment->allocated_amount < $payment->amount - 0.01) {
            throw ValidationException::withMessages([
                'allocations' => __('Supplier payments must be fully allocated before posting.'),
            ]);
        }

        return DB::transaction(function () use ($payment, $userId) {
            $paymentAccountId = $this->resolvePaymentAccountId($payment);

            $journal = $this->posting->postEvent(
                PostingEventCode::PaymentMade,
                $payment->company_id,
                $userId,
                'supplier_payment',
                $payment->id,
                $payment->payment_date->toDateString(),
                ['total_amount' => (float) $payment->amount],
                $payment->branch_id,
                reference: $payment->payment_number,
                description: $payment->notes ?? __('Supplier payment :number', ['number' => $payment->payment_number]),
                accounts: ['payment_account' => $paymentAccountId],
            );

            $payment->update([
                'status' => SupplierPaymentStatus::Posted,
                'posted_by' => $userId,
                'posted_at' => now(),
                'posted_journal_id' => $journal->id,
            ]);

            foreach ($payment->allocations as $allocation) {
                $allocation->bill->refreshPaymentBalance();
            }

            $payment = $payment->fresh(['postedJournal', 'poster', 'allocations.bill', 'withholdingTaxCode']);
            $this->taxRecorder->recordSupplierPayment($payment);

            return $payment;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{0: ?int, 1: float}
     */
    protected function resolveWithholding(int $companyId, float $amount, array $data): array
    {
        $codeId = ! empty($data['withholding_tax_code_id']) ? (int) $data['withholding_tax_code_id'] : null;

        if (! $codeId) {
            return [null, 0.0];
        }

        $code = TaxCode::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->findOrFail($codeId);

        $rate = $this->taxRates->resolve($code, $data['payment_date'] ?? now()->toDateString());

        return [$codeId, round($amount * ($rate / 100), 2)];
    }

    public function cancel(SupplierPayment $payment, int $userId, ?string $reason = null): SupplierPayment
    {
        if ($payment->status !== SupplierPaymentStatus::Draft) {
            throw ValidationException::withMessages([
                'status' => __('Only draft payments can be cancelled.'),
            ]);
        }

        $payment->update([
            'status' => SupplierPaymentStatus::Cancelled,
            'cancelled_by' => $userId,
            'cancelled_at' => now(),
            'cancel_reason' => $reason,
        ]);

        return $payment;
    }

    public function deleteDraft(SupplierPayment $payment): void
    {
        if (! $payment->status->isEditable()) {
            throw ValidationException::withMessages([
                'payment' => __('Only draft payments can be deleted.'),
            ]);
        }

        $payment->delete();
    }

    /**
     * @return list<SupplierBill>
     */
    public function openBillsForVendor(int $vendorId): array
    {
        return SupplierBill::query()
            ->where('vendor_id', $vendorId)
            ->whereIn('status', [SupplierBillStatus::Posted->value])
            ->where('balance_due', '>', 0)
            ->whereNot('bill_type', SupplierBillType::CreditNote->value)
            ->orderBy('bill_date')
            ->get()
            ->all();
    }

    protected function assertAllocationValid(?SupplierBill $bill, float $amount, SupplierPayment $payment): void
    {
        if (! $bill || $bill->vendor_id !== $payment->vendor_id) {
            throw ValidationException::withMessages([
                'allocations' => __('Invalid bill for this supplier.'),
            ]);
        }

        if ($bill->status !== SupplierBillStatus::Posted) {
            throw ValidationException::withMessages([
                'allocations' => __('Only posted bills can receive payments.'),
            ]);
        }

        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'allocations' => __('Allocation amount must be greater than zero.'),
            ]);
        }

        $remaining = (float) $bill->balance_due;
        $existingDraft = (float) SupplierPaymentAllocation::query()
            ->where('supplier_bill_id', $bill->id)
            ->whereHas('payment', fn ($q) => $q
                ->where('status', SupplierPaymentStatus::Draft)
                ->where('id', '!=', $payment->id))
            ->sum('amount');

        if ($amount > $remaining - $existingDraft + 0.01) {
            throw ValidationException::withMessages([
                'allocations' => __('Allocation exceeds balance due on bill :number.', [
                    'number' => $bill->bill_number,
                ]),
            ]);
        }
    }

    /**
     * @param  array<int, array{supplier_bill_id: int, amount: float}>  $allocations
     */
    protected function syncAllocations(SupplierPayment $payment, array $allocations): void
    {
        foreach ($allocations as $row) {
            $billId = (int) ($row['supplier_bill_id'] ?? 0);
            $amount = round((float) ($row['amount'] ?? 0), 2);

            if ($billId <= 0 || $amount <= 0) {
                continue;
            }

            $bill = SupplierBill::query()->find($billId);
            $this->assertAllocationValid($bill, $amount, $payment);

            $payment->allocations()->create([
                'supplier_bill_id' => $billId,
                'amount' => $amount,
            ]);
        }
    }

    protected function resolvePaymentAccountId(SupplierPayment $payment): int
    {
        $key = $payment->payment_method->paymentAccountKey();

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
                'account' => __('Payment account :key is not configured.', ['key' => $key]),
            ]);
        }

        return (int) $account->id;
    }
}
