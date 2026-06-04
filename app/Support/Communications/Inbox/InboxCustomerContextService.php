<?php

namespace App\Support\Communications\Inbox;

use App\Enums\CustomerInvoiceStatus;
use App\Enums\CustomerInvoiceType;
use App\Enums\ProductionJobCardStatus;
use App\Enums\QuotationStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Artwork\ArtworkRequest;
use App\Models\Communications\CommunicationLog;
use App\Models\Communications\Inbox\CommunicationConversation;
use App\Models\Crm\Customer;
use App\Models\Production\ProductionJobCard;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\CustomerPayment;
use App\Models\Sales\Quotation;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use Illuminate\Support\Collection;

class InboxCustomerContextService
{
    /**
     * @return array<string, mixed>|null
     */
    public function forConversation(CommunicationConversation $conversation): ?array
    {
        if (! $conversation->customer_id) {
            return null;
        }

        $customer = Customer::query()
            ->with(['branch', 'segments'])
            ->find($conversation->customer_id);

        if (! $customer) {
            return null;
        }

        $summary = $this->summaryCards($customer, $conversation);

        return [
            'summary' => $summary,
            'summary_compact' => $this->summaryCompact($summary, $conversation),
            'summary_extended' => $summary,
            'linked_records' => $this->linkedRecords($customer),
            'quick_actions' => $this->quickActions($customer),
            'create_menu' => $this->createMenu($customer),
        ];
    }

    /**
     * @param  array<string, string|int|float|null>  $summary
     * @return array<string, mixed>
     */
    protected function summaryCompact(array $summary, CommunicationConversation $conversation): array
    {
        $openItems = (int) ($summary['open_quotations'] ?? 0)
            + (int) ($summary['open_orders'] ?? 0)
            + (int) ($summary['open_jobs'] ?? 0)
            + (int) ($summary['open_invoices'] ?? 0);

        return [
            'customer_name' => $summary['customer_name'],
            'phone' => $summary['phone'] ?? '—',
            'email' => $summary['email'] ?? '—',
            'status' => $summary['customer_status'] ?? '—',
            'assigned_user' => $conversation->assignee?->name ?? __('Unassigned'),
            'outstanding_balance' => $summary['outstanding_balance'] ?? '0.00',
            'open_items_count' => $openItems,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function createMenu(Customer $customer): array
    {
        $customerId = ['customer_id' => $customer->id];

        return array_values(array_filter([
            ['label' => __('Quotation'), 'route' => 'admin.quotations.create', 'permission' => 'quotations.create', 'params' => $customerId],
            ['label' => __('Sales order'), 'route' => 'admin.sales-orders.create', 'permission' => 'sales_orders.create', 'params' => $customerId],
            ['label' => __('Job card'), 'route' => 'admin.production.job-cards.create', 'permission' => 'production.create', 'params' => $customerId],
            ['label' => __('Invoice'), 'route' => 'admin.invoices.index', 'permission' => 'invoices.create', 'params' => []],
            ['label' => __('Payment'), 'route' => 'admin.payments.create', 'permission' => 'payments.create', 'params' => $customerId],
            ['label' => __('Delivery'), 'route' => null, 'permission' => null, 'params' => []],
            ['label' => __('Follow-up'), 'route' => 'admin.commercial.activities.create', 'permission' => 'crm.activities.create', 'params' => $customerId],
        ], fn (array $item) => ! empty($item['route'])));
    }

    /**
     * @return array<string, string|int|float|null>
     */
    protected function summaryCards(Customer $customer, CommunicationConversation $conversation): array
    {
        $openQuotations = Quotation::query()->where('customer_id', $customer->id)
            ->whereNotIn('status', [QuotationStatus::Converted, QuotationStatus::Rejected, QuotationStatus::Expired])
            ->count();
        $openOrders = SalesOrder::query()->where('customer_id', $customer->id)
            ->whereNotIn('status', [SalesOrderStatus::Closed, SalesOrderStatus::Cancelled, SalesOrderStatus::Delivered])
            ->count();
        $openJobs = ProductionJobCard::query()->where('customer_id', $customer->id)
            ->whereNotIn('status', [ProductionJobCardStatus::Completed, ProductionJobCardStatus::Cancelled])
            ->count();
        $openInvoices = CustomerInvoice::query()->where('customer_id', $customer->id)
            ->where('balance_due', '>', 0)
            ->whereIn('status', [CustomerInvoiceStatus::Approved, CustomerInvoiceStatus::Posted])
            ->count();
        $outstanding = (float) CustomerInvoice::query()->where('customer_id', $customer->id)
            ->whereIn('status', [CustomerInvoiceStatus::Approved, CustomerInvoiceStatus::Posted])
            ->sum('balance_due');
        $lifetime = (float) CustomerInvoice::query()->where('customer_id', $customer->id)
            ->where('status', CustomerInvoiceStatus::Posted)
            ->where('invoice_type', '!=', CustomerInvoiceType::CreditNote->value)
            ->sum('total_amount');
        $lastPayment = CustomerPayment::query()->where('customer_id', $customer->id)
            ->orderByDesc('payment_date')->first();
        $lastContact = CommunicationLog::query()
            ->where('company_id', $customer->company_id)
            ->whereHas('recipients', fn ($q) => $q->where('recipient_type', 'customer')->where('recipient_id', $customer->id))
            ->orderByDesc('created_at')
            ->value('created_at');

        $isVip = $customer->segments->contains(fn ($s) => str_contains(strtolower($s->name ?? ''), 'vip'));

        return [
            'customer_name' => $customer->name,
            'customer_number' => $customer->customer_code,
            'phone' => $customer->phone,
            'email' => $customer->email,
            'branch' => $customer->branch?->name,
            'customer_since' => $customer->created_at?->format('d M Y'),
            'assigned_salesperson' => __('—'),
            'last_contact_date' => $lastContact?->format('d M Y H:i'),
            'preferred_channel' => $conversation->preferred_channel ?? $conversation->last_channel ?? __('—'),
            'customer_status' => ucfirst($customer->status->value),
            'vip_status' => $isVip ? __('VIP') : __('Standard'),
            'credit_status' => $customer->status->value,
            'outstanding_balance' => number_format($outstanding, 2),
            'lifetime_revenue' => number_format($lifetime, 2),
            'last_payment' => $lastPayment
                ? $lastPayment->payment_number.' · '.number_format((float) $lastPayment->amount, 2)
                : __('—'),
            'open_quotations' => $openQuotations,
            'open_orders' => $openOrders,
            'open_jobs' => $openJobs,
            'open_invoices' => $openInvoices,
            'open_deliveries' => SalesOrder::query()->where('customer_id', $customer->id)
                ->where('status', SalesOrderStatus::Completed)->count(),
        ];
    }

    /**
     * @return array<string, Collection<int, array<string, mixed>>>
     */
    protected function linkedRecords(Customer $customer): array
    {
        $map = fn ($items, string $routeName, string $numberKey) => $items->map(fn ($row) => [
            'id' => $row->id,
            'number' => $row->{$numberKey},
            'status' => is_object($row->status) ? $row->status->value : (string) $row->status,
            'date' => $row->created_at?->format('d M Y'),
            'view_url' => route($routeName, $row),
            'create_url' => null,
        ]);

        return [
            'quotations' => $map(
                Quotation::query()->where('customer_id', $customer->id)->orderByDesc('id')->limit(8)->get(),
                'admin.quotations.show',
                'quotation_number',
            ),
            'sales_orders' => $map(
                SalesOrder::query()->where('customer_id', $customer->id)->orderByDesc('id')->limit(8)->get(),
                'admin.sales-orders.show',
                'order_number',
            ),
            'artwork' => $map(
                ArtworkRequest::query()->where('customer_id', $customer->id)->orderByDesc('id')->limit(8)->get(),
                'admin.artwork.show',
                'request_number',
            ),
            'jobs' => $map(
                ProductionJobCard::query()->where('customer_id', $customer->id)->orderByDesc('id')->limit(8)->get(),
                'admin.production.job-cards.show',
                'job_card_number',
            ),
            'invoices' => $map(
                CustomerInvoice::query()->where('customer_id', $customer->id)->orderByDesc('id')->limit(8)->get(),
                'admin.invoices.show',
                'invoice_number',
            ),
            'payments' => CustomerPayment::query()->where('customer_id', $customer->id)->orderByDesc('id')->limit(8)->get()
                ->map(fn ($row) => [
                    'id' => $row->id,
                    'number' => $row->payment_number,
                    'status' => $row->status->value,
                    'date' => $row->payment_date?->format('d M Y'),
                    'view_url' => route('admin.payments.show', $row),
                    'create_url' => null,
                ]),
            'credit_notes' => $map(
                CustomerInvoice::query()->where('customer_id', $customer->id)
                    ->where('invoice_type', CustomerInvoiceType::CreditNote->value)
                    ->orderByDesc('id')->limit(5)->get(),
                'admin.invoices.show',
                'invoice_number',
            ),
            'deliveries' => collect(),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function quickActions(Customer $customer): array
    {
        $customerParam = ['customer' => $customer->id, 'customer_id' => $customer->id];

        return [
            ['label' => __('Create quotation'), 'route' => 'admin.quotations.create', 'permission' => 'quotations.create'],
            ['label' => __('Create sales order'), 'route' => 'admin.sales-orders.create', 'permission' => 'sales_orders.create'],
            ['label' => __('Create job card'), 'route' => 'admin.production.job-cards.create', 'permission' => 'production.create'],
            ['label' => __('Create invoice'), 'route' => 'admin.invoices.index', 'permission' => 'invoices.create'],
            ['label' => __('Record payment'), 'route' => 'admin.payments.create', 'permission' => 'payments.create'],
            ['label' => __('Schedule follow-up'), 'route' => null, 'permission' => null],
            ['label' => __('Add internal note'), 'route' => null, 'permission' => 'communications.inbox.notes', 'anchor' => 'inbox-note'],
            ['label' => __('Assign user'), 'route' => null, 'permission' => 'communications.inbox.assign', 'anchor' => 'inbox-assign'],
            ['label' => __('Escalate'), 'route' => null, 'permission' => 'communications.inbox.escalate', 'anchor' => 'inbox-escalate'],
        ];
    }
}
