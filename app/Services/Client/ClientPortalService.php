<?php

namespace App\Services\Client;

use App\Enums\ArtworkRequestStatus;
use App\Enums\CustomerInvoiceStatus;
use App\Enums\CustomerPaymentStatus;
use App\Enums\QuotationStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Artwork\ArtworkRequest;
use App\Models\Crm\Customer;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\CustomerPayment;
use App\Models\Sales\Quotation;
use App\Models\Sales\SalesOrder;
use App\Support\Sales\CustomerLedgerService;
use App\Support\Sales\CustomerPaymentReceiptService;

class ClientPortalService
{
    public function __construct(
        protected CustomerLedgerService $ledger,
        protected CustomerPaymentReceiptService $receipts,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function dashboard(Customer $customer): array
    {
        $customerId = (int) $customer->id;

        $openQuotes = Quotation::query()
            ->where('customer_id', $customerId)
            ->whereIn('status', [QuotationStatus::Sent, QuotationStatus::Viewed])
            ->count();

        $activeOrders = SalesOrder::query()
            ->where('customer_id', $customerId)
            ->whereNotIn('status', [
                SalesOrderStatus::Draft,
                SalesOrderStatus::Delivered,
                SalesOrderStatus::Closed,
                SalesOrderStatus::Cancelled,
            ])
            ->count();

        $pendingArtwork = ArtworkRequest::query()
            ->where('customer_id', $customerId)
            ->where('status', ArtworkRequestStatus::Submitted)
            ->count();

        $outstanding = $this->ledger->closingBalance($customerId);

        return [
            'metrics' => [
                ['key' => 'balance', 'label' => __('Outstanding balance'), 'value' => $this->money($outstanding), 'tone' => $outstanding > 0 ? 'warning' : 'neutral'],
                ['key' => 'quotes', 'label' => __('Quotes awaiting response'), 'value' => (string) $openQuotes, 'tone' => $openQuotes > 0 ? 'action' : 'neutral'],
                ['key' => 'orders', 'label' => __('Active orders'), 'value' => (string) $activeOrders, 'tone' => 'neutral'],
                ['key' => 'artwork', 'label' => __('Artwork to review'), 'value' => (string) $pendingArtwork, 'tone' => $pendingArtwork > 0 ? 'action' : 'neutral'],
            ],
            'recent_quotations' => $this->recentQuotations($customerId),
            'recent_orders' => $this->recentOrders($customerId),
            'recent_invoices' => $this->recentInvoices($customerId),
            'recent_payments' => $this->recentPayments($customerId),
            'pending_artwork' => $this->pendingArtwork($customerId),
        ];
    }

    /**
     * @return list<Quotation>
     */
    public function recentQuotations(int $customerId, int $limit = 5): array
    {
        return Quotation::query()
            ->where('customer_id', $customerId)
            ->whereNotIn('status', [QuotationStatus::Draft, QuotationStatus::PendingApproval])
            ->latest('quotation_date')
            ->limit($limit)
            ->get()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function recentOrders(int $customerId, int $limit = 5): array
    {
        return SalesOrder::query()
            ->where('customer_id', $customerId)
            ->where('status', '!=', SalesOrderStatus::Draft)
            ->latest('order_date')
            ->limit($limit)
            ->get()
            ->all();
    }

    /**
     * @return list<CustomerInvoice>
     */
    public function recentInvoices(int $customerId, int $limit = 5): array
    {
        return CustomerInvoice::query()
            ->where('customer_id', $customerId)
            ->where('status', CustomerInvoiceStatus::Posted)
            ->latest('invoice_date')
            ->limit($limit)
            ->get()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function recentPayments(int $customerId, int $limit = 5): array
    {
        return CustomerPayment::query()
            ->where('customer_id', $customerId)
            ->where('status', CustomerPaymentStatus::Posted)
            ->latest('payment_date')
            ->limit($limit)
            ->get()
            ->map(fn (CustomerPayment $payment) => [
                'payment' => $payment,
                'receipt_url' => $this->receipts->signedPublicUrl($payment),
            ])
            ->all();
    }

    /**
     * @return list<ArtworkRequest>
     */
    public function pendingArtwork(int $customerId, int $limit = 5): array
    {
        return ArtworkRequest::query()
            ->where('customer_id', $customerId)
            ->where('status', ArtworkRequestStatus::Submitted)
            ->latest('updated_at')
            ->limit($limit)
            ->get()
            ->all();
    }

    public function money(float $amount): string
    {
        return 'KES '.number_format($amount, 0);
    }
}
