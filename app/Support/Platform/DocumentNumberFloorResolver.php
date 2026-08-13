<?php

namespace App\Support\Platform;

use App\Enums\DocumentType;
use App\Models\Accounting\Journal;
use App\Models\Artwork\ArtworkRequest;
use App\Models\Assets\FixedAsset;
use App\Models\Crm\Customer;
use App\Models\Dispatch\DeliveryNote;
use App\Models\Inventory\StockIssue;
use App\Models\Inventory\StockReceipt;
use App\Models\Procurement\GoodsReceipt;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\PurchaseRequest;
use App\Models\Procurement\Rfq;
use App\Models\Procurement\SupplierBill;
use App\Models\Procurement\SupplierPayment;
use App\Models\Procurement\SupplierQuotation;
use App\Models\Procurement\Vendor;
use App\Models\Production\ProductionJobCard;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\CustomerPayment;
use App\Models\Sales\Quotation;
use App\Models\Sales\SalesOrder;
use Illuminate\Database\Eloquent\Model;

class DocumentNumberFloorResolver
{
    /**
     * @return array{model: class-string<Model>, column: string, branch_scoped: bool}|null
     */
    public function sourceFor(DocumentType $documentType): ?array
    {
        return match ($documentType) {
            DocumentType::Customer => ['model' => Customer::class, 'column' => 'customer_code', 'branch_scoped' => true],
            DocumentType::Quotation => ['model' => Quotation::class, 'column' => 'quotation_number', 'branch_scoped' => true],
            DocumentType::ArtworkRequest => ['model' => ArtworkRequest::class, 'column' => 'request_number', 'branch_scoped' => true],
            DocumentType::SalesOrder => ['model' => SalesOrder::class, 'column' => 'order_number', 'branch_scoped' => true],
            DocumentType::JobCard => ['model' => ProductionJobCard::class, 'column' => 'job_card_number', 'branch_scoped' => true],
            DocumentType::DeliveryNote => ['model' => DeliveryNote::class, 'column' => 'delivery_note_number', 'branch_scoped' => true],
            DocumentType::StockReceipt => ['model' => StockReceipt::class, 'column' => 'receipt_number', 'branch_scoped' => true],
            DocumentType::StockIssue => ['model' => StockIssue::class, 'column' => 'issue_number', 'branch_scoped' => true],
            DocumentType::Invoice => ['model' => CustomerInvoice::class, 'column' => 'invoice_number', 'branch_scoped' => true],
            DocumentType::Payment => ['model' => CustomerPayment::class, 'column' => 'payment_number', 'branch_scoped' => true],
            DocumentType::SupplierBill => ['model' => SupplierBill::class, 'column' => 'bill_number', 'branch_scoped' => true],
            DocumentType::SupplierPayment => ['model' => SupplierPayment::class, 'column' => 'payment_number', 'branch_scoped' => true],
            DocumentType::Vendor => ['model' => Vendor::class, 'column' => 'vendor_code', 'branch_scoped' => true],
            DocumentType::PurchaseRequest => ['model' => PurchaseRequest::class, 'column' => 'request_number', 'branch_scoped' => true],
            DocumentType::PurchaseOrder => ['model' => PurchaseOrder::class, 'column' => 'po_number', 'branch_scoped' => true],
            DocumentType::GoodsReceipt => ['model' => GoodsReceipt::class, 'column' => 'receipt_number', 'branch_scoped' => true],
            DocumentType::SupplierQuotation => ['model' => SupplierQuotation::class, 'column' => 'quotation_number', 'branch_scoped' => true],
            DocumentType::Rfq => ['model' => Rfq::class, 'column' => 'rfq_number', 'branch_scoped' => true],
            DocumentType::FixedAsset => ['model' => FixedAsset::class, 'column' => 'asset_number', 'branch_scoped' => false],
            DocumentType::Journal => ['model' => Journal::class, 'column' => 'journal_number', 'branch_scoped' => true],
            default => null,
        };
    }

    public function highestUsedNumber(
        DocumentType $documentType,
        int $companyId,
        ?int $branchId,
        bool $limitToCurrentYear,
    ): int {
        $source = $this->sourceFor($documentType);

        if ($source === null) {
            return 0;
        }

        /** @var class-string<Model> $modelClass */
        $modelClass = $source['model'];
        $column = $source['column'];
        $branchScoped = $source['branch_scoped'];

        $query = $modelClass::query()
            ->where('company_id', $companyId)
            ->whereNotNull($column)
            ->where($column, '!=', '');

        if (in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive($modelClass), true)) {
            $query->withTrashed();
        }

        if ($branchScoped && $branchId) {
            $query->where('branch_id', $branchId);
        }

        if ($limitToCurrentYear) {
            $query->where($column, 'like', '%-'.now()->year.'-%');
        }

        $max = 0;

        $query->select($column)->cursor()->each(function (Model $row) use ($column, &$max) {
            $numeric = $this->extractNumericSuffix((string) $row->getAttribute($column));
            $max = max($max, $numeric);
        });

        return $max;
    }

    protected function extractNumericSuffix(string $value): int
    {
        if (preg_match('/-(\d+)$/', $value, $matches) === 1) {
            return (int) $matches[1];
        }

        if (preg_match('/(\d+)$/', $value, $matches) === 1) {
            return (int) $matches[1];
        }

        return 0;
    }
}
