<?php

namespace App\Support\Tax;

use App\Enums\CustomerInvoiceStatus;
use App\Enums\CustomerInvoiceType;
use App\Enums\SupplierBillStatus;
use App\Enums\SupplierBillType;
use App\Enums\SupplierPaymentStatus;
use App\Enums\TaxCategoryType;
use App\Enums\TaxDirection;
use App\Enums\TaxDocumentType;
use App\Models\Sales\CustomerInvoice;
use App\Models\Procurement\SupplierBill;
use App\Models\Procurement\SupplierPayment;
use App\Models\Tax\TaxPeriod;
use App\Models\Tax\TaxTransaction;
use Illuminate\Support\Collection;

class TaxTransactionRecorder
{
    public function recordCustomerInvoice(CustomerInvoice $invoice): void
    {
        if ($invoice->status !== CustomerInvoiceStatus::Posted) {
            return;
        }

        $this->purgeSource('customer_invoice', $invoice->id);

        $documentType = $invoice->invoice_type === CustomerInvoiceType::CreditNote
            ? TaxDocumentType::CustomerCreditNote
            : TaxDocumentType::CustomerInvoice;

        $direction = $documentType->ledgerDirection();
        $multiplier = $invoice->invoice_type->isCredit() ? -1 : 1;

        foreach ($invoice->taxLines as $line) {
            $this->createTransaction(
                companyId: $invoice->company_id,
                branchId: $invoice->branch_id,
                taxCodeId: $line->tax_code_id,
                taxCategoryId: $line->tax_category_id,
                direction: $direction,
                sourceType: 'customer_invoice',
                sourceId: $invoice->id,
                documentNumber: $invoice->invoice_number,
                documentDate: $invoice->invoice_date->toDateString(),
                taxableAmount: $multiplier * (float) $line->taxable_amount,
                taxAmount: $multiplier * (float) $line->tax_amount,
                ratePercent: (float) $line->tax_rate,
                postedAt: $invoice->posted_at ?? now(),
            );
        }
    }

    public function recordSupplierBill(SupplierBill $bill): void
    {
        if ($bill->status !== SupplierBillStatus::Posted && $bill->status !== SupplierBillStatus::Paid) {
            return;
        }

        $this->purgeSource('supplier_bill', $bill->id);

        $documentType = $bill->bill_type === SupplierBillType::CreditNote
            ? TaxDocumentType::SupplierCreditNote
            : TaxDocumentType::SupplierBill;

        $multiplier = $bill->bill_type->isCredit() ? -1 : 1;

        foreach ($bill->taxLines as $line) {
            $this->createTransaction(
                companyId: $bill->company_id,
                branchId: $bill->branch_id,
                taxCodeId: $line->tax_code_id,
                taxCategoryId: $line->tax_category_id,
                direction: TaxDirection::Input,
                sourceType: 'supplier_bill',
                sourceId: $bill->id,
                documentNumber: $bill->bill_number,
                documentDate: $bill->bill_date->toDateString(),
                taxableAmount: $multiplier * (float) $line->taxable_amount,
                taxAmount: $multiplier * (float) $line->tax_amount,
                ratePercent: (float) $line->tax_rate,
                postedAt: $bill->posted_at ?? now(),
            );
        }
    }

    public function recordSupplierPayment(SupplierPayment $payment): void
    {
        if ($payment->status !== SupplierPaymentStatus::Posted) {
            return;
        }

        if ((float) $payment->withholding_tax_amount <= 0 || ! $payment->withholding_tax_code_id) {
            return;
        }

        $payment->loadMissing('withholdingTaxCode.category');

        $this->purgeSource('supplier_payment_wht', $payment->id);

        $this->createTransaction(
            companyId: $payment->company_id,
            branchId: $payment->branch_id,
            taxCodeId: (int) $payment->withholding_tax_code_id,
            taxCategoryId: (int) $payment->withholdingTaxCode->tax_category_id,
            direction: TaxDirection::Input,
            sourceType: 'supplier_payment_wht',
            sourceId: $payment->id,
            documentNumber: $payment->payment_number,
            documentDate: $payment->payment_date->toDateString(),
            taxableAmount: (float) $payment->amount,
            taxAmount: (float) $payment->withholding_tax_amount,
            ratePercent: 0,
            postedAt: $payment->posted_at ?? now(),
        );
    }

    protected function createTransaction(
        int $companyId,
        ?int $branchId,
        ?int $taxCodeId,
        ?int $taxCategoryId,
        TaxDirection $direction,
        string $sourceType,
        int $sourceId,
        ?string $documentNumber,
        string $documentDate,
        float $taxableAmount,
        float $taxAmount,
        float $ratePercent,
        $postedAt,
    ): void {
        if (! $taxCodeId || abs($taxAmount) < 0.005) {
            return;
        }

        TaxTransaction::query()->create([
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'tax_code_id' => $taxCodeId,
            'tax_category_id' => $taxCategoryId,
            'tax_period_id' => $this->resolvePeriodId($companyId, $documentDate),
            'direction' => $direction->value,
            'source_type' => $sourceType,
            'source_id' => $sourceId,
            'document_number' => $documentNumber,
            'document_date' => $documentDate,
            'taxable_amount' => round($taxableAmount, 2),
            'tax_amount' => round($taxAmount, 2),
            'rate_percent' => $ratePercent,
            'posted_at' => $postedAt,
        ]);
    }

    protected function resolvePeriodId(int $companyId, string $documentDate): ?int
    {
        return TaxPeriod::query()
            ->where('company_id', $companyId)
            ->whereDate('start_date', '<=', $documentDate)
            ->whereDate('end_date', '>=', $documentDate)
            ->value('id');
    }

    protected function purgeSource(string $sourceType, int $sourceId): void
    {
        TaxTransaction::query()
            ->where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->delete();
    }

    /**
     * @return Collection<int, TaxTransaction>
     */
    public function ledgerQuery(int $companyId, ?string $fromDate, ?string $toDate, ?TaxDirection $direction = null): Collection
    {
        $query = TaxTransaction::query()
            ->where('company_id', $companyId)
            ->with(['taxCode', 'taxCategory']);

        if ($fromDate) {
            $query->whereDate('document_date', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('document_date', '<=', $toDate);
        }

        if ($direction) {
            $query->where('direction', $direction->value);
        }

        return $query->orderBy('document_date')->orderBy('id')->get();
    }
}
