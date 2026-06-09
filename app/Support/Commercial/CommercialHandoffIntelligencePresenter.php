<?php

namespace App\Support\Commercial;

use App\Enums\ArtworkRequestStatus;
use App\Enums\CommercialHandoffAttentionLevel;
use App\Enums\CustomerInvoiceStatus;
use App\Enums\ProductionJobCardStatus;
use App\Enums\QuotationStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Artwork\ArtworkRequest;
use App\Models\Production\ProductionJobCard;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\Quotation;
use App\Models\Sales\SalesOrder;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class CommercialHandoffIntelligencePresenter
{
    private const ITEM_LIMIT = 10;

    /**
     * @return array{
     *     summary: array<string, int>,
     *     sections: list<array<string, mixed>>,
     * }
     */
    public function build(): array
    {
        $sections = array_values(array_filter([
            $this->blockedQuotesSection(),
            $this->blockedArtworkSection(),
            $this->blockedSalesOrdersSection(),
            $this->blockedProductionSection(),
            $this->blockedBillingSection(),
            $this->blockedCashflowSection(),
        ]));

        $summary = [
            'critical' => 0,
            'high' => 0,
            'medium' => 0,
            'low' => 0,
        ];

        foreach ($sections as $section) {
            foreach ($section['items'] as $item) {
                $level = $item['attention_level'] ?? CommercialHandoffAttentionLevel::Low->value;
                if (isset($summary[$level])) {
                    $summary[$level]++;
                }
            }
        }

        return [
            'summary' => $summary,
            'sections' => $sections,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function blockedQuotesSection(): ?array
    {
        if (! auth()->user()?->can('quotations.view')) {
            return null;
        }

        $quotations = Quotation::query()->forTenant()
            ->where('status', QuotationStatus::Accepted)
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('artwork_requests')
                    ->whereColumn('artwork_requests.quotation_id', 'quotations.id')
                    ->whereColumn('artwork_requests.company_id', 'quotations.company_id');
            })
            ->with('customer:id,company_name')
            ->orderBy('updated_at')
            ->limit(self::ITEM_LIMIT)
            ->get();

        return $this->section(
            key: 'blocked_quotes',
            title: __('Blocked Quotes'),
            description: __('Accepted quotes without an artwork request.'),
            route: 'admin.quotations.index',
            items: $quotations->map(fn (Quotation $quotation) => $this->handoffItem(
                reference: $quotation->quotation_number,
                customer: $quotation->customer?->company_name ?? __('Walk-in'),
                value: (float) $quotation->total_amount,
                blockedSince: $quotation->updated_at,
                url: Route::has('admin.quotations.show') ? route('admin.quotations.show', $quotation) : null,
                blockReason: __('No artwork request'),
            ))->all(),
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function blockedArtworkSection(): ?array
    {
        if (! auth()->user()?->can('artwork.view')) {
            return null;
        }

        $requests = ArtworkRequest::query()->forTenant()
            ->where('status', ArtworkRequestStatus::Submitted)
            ->with('customer:id,company_name', 'quotation:id,total_amount')
            ->orderBy('updated_at')
            ->limit(self::ITEM_LIMIT)
            ->get();

        return $this->section(
            key: 'blocked_artwork',
            title: __('Blocked Artwork'),
            description: __('Artwork submitted but not yet approved.'),
            route: 'admin.artwork.index',
            items: $requests->map(fn (ArtworkRequest $request) => $this->handoffItem(
                reference: $request->request_number,
                customer: $request->customer?->company_name ?? '—',
                value: (float) ($request->quotation?->total_amount ?? 0),
                blockedSince: $request->updated_at,
                url: Route::has('admin.artwork.show') ? route('admin.artwork.show', $request) : null,
                blockReason: __('Awaiting approval'),
            ))->all(),
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function blockedSalesOrdersSection(): ?array
    {
        if (! auth()->user()?->can('sales_orders.view')) {
            return null;
        }

        $requests = ArtworkRequest::query()->forTenant()
            ->where('status', ArtworkRequestStatus::Approved)
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('sales_orders')
                    ->whereColumn('sales_orders.artwork_request_id', 'artwork_requests.id')
                    ->whereColumn('sales_orders.company_id', 'artwork_requests.company_id');
            })
            ->with('customer:id,company_name', 'quotation:id,quotation_number,total_amount')
            ->orderBy('updated_at')
            ->limit(self::ITEM_LIMIT)
            ->get();

        return $this->section(
            key: 'blocked_sales_orders',
            title: __('Blocked Sales Orders'),
            description: __('Approved artwork without a sales order.'),
            route: 'admin.sales-orders.create',
            items: $requests->map(fn (ArtworkRequest $request) => $this->handoffItem(
                reference: $request->request_number,
                customer: $request->customer?->company_name ?? '—',
                value: (float) ($request->quotation?->total_amount ?? 0),
                blockedSince: $request->updated_at,
                url: Route::has('admin.artwork.show') ? route('admin.artwork.show', $request) : null,
                blockReason: __('No sales order'),
                context: $request->quotation?->quotation_number,
            ))->all(),
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function blockedProductionSection(): ?array
    {
        if (! auth()->user()?->can('sales_orders.view') && ! auth()->user()?->can('production.view')) {
            return null;
        }

        $orders = SalesOrder::query()->forTenant()
            ->where('status', SalesOrderStatus::ReadyForProduction)
            ->whereDoesntHave('jobCard')
            ->with('customer:id,company_name')
            ->orderBy('updated_at')
            ->limit(self::ITEM_LIMIT)
            ->get();

        return $this->section(
            key: 'blocked_production',
            title: __('Blocked Production'),
            description: __('Sales orders ready for production without a job card.'),
            route: 'admin.sales-orders.index',
            items: $orders->map(fn (SalesOrder $order) => $this->handoffItem(
                reference: $order->order_number,
                customer: $order->customer?->company_name ?? '—',
                value: (float) $order->total_amount,
                blockedSince: $order->updated_at,
                url: Route::has('admin.sales-orders.show') ? route('admin.sales-orders.show', $order) : null,
                blockReason: __('No job card'),
            ))->all(),
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function blockedBillingSection(): ?array
    {
        if (! auth()->user()?->can('invoices.view') && ! auth()->user()?->can('production.view')) {
            return null;
        }

        $jobCards = ProductionJobCard::query()->forTenant()
            ->whereIn('status', [
                ProductionJobCardStatus::Completed,
                ProductionJobCardStatus::ReadyForDispatch,
            ])
            ->whereHas('salesOrder', function ($query) {
                $query->whereDoesntHave('invoices', fn ($invoice) => $invoice
                    ->whereIn('status', [CustomerInvoiceStatus::Approved, CustomerInvoiceStatus::Posted]));
            })
            ->with(['customer:id,company_name', 'salesOrder:id,order_number,total_amount'])
            ->orderBy('updated_at')
            ->limit(self::ITEM_LIMIT)
            ->get();

        return $this->section(
            key: 'blocked_billing',
            title: __('Blocked Billing'),
            description: __('Completed production without an invoice.'),
            route: 'admin.invoices.index',
            items: $jobCards->map(fn (ProductionJobCard $jobCard) => $this->handoffItem(
                reference: $jobCard->job_card_number,
                customer: $jobCard->customer?->company_name ?? '—',
                value: (float) ($jobCard->salesOrder?->total_amount ?? 0),
                blockedSince: $jobCard->updated_at,
                url: Route::has('admin.production.job-cards.show')
                    ? route('admin.production.job-cards.show', $jobCard)
                    : null,
                blockReason: __('No invoice'),
                context: $jobCard->salesOrder?->order_number,
            ))->all(),
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function blockedCashflowSection(): ?array
    {
        if (! auth()->user()?->can('invoices.view')) {
            return null;
        }

        $invoices = CustomerInvoice::query()->forTenant()
            ->whereIn('status', [CustomerInvoiceStatus::Approved, CustomerInvoiceStatus::Posted])
            ->where('balance_due', '>', 0)
            ->where('amount_paid', '<=', 0)
            ->with('customer:id,company_name')
            ->orderByDesc('balance_due')
            ->limit(self::ITEM_LIMIT)
            ->get();

        return $this->section(
            key: 'blocked_cashflow',
            title: __('Blocked Cashflow'),
            description: __('Invoices issued with no payment recorded.'),
            route: 'admin.payments.create',
            items: $invoices->map(fn (CustomerInvoice $invoice) => $this->handoffItem(
                reference: $invoice->invoice_number,
                customer: $invoice->customer?->company_name ?? '—',
                value: (float) $invoice->balance_due,
                blockedSince: $invoice->invoice_date ?? $invoice->updated_at,
                url: Route::has('admin.invoices.show') ? route('admin.invoices.show', $invoice) : null,
                blockReason: __('No payment'),
            ))->all(),
        );
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @return array<string, mixed>
     */
    protected function section(
        string $key,
        string $title,
        string $description,
        ?string $route,
        array $items,
    ): array {
        usort($items, fn (array $a, array $b) => ($b['attention_rank'] ?? 0) <=> ($a['attention_rank'] ?? 0));

        return [
            'key' => $key,
            'title' => $title,
            'description' => $description,
            'route' => $route && Route::has($route) ? $route : null,
            'count' => count($items),
            'items' => $items,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function handoffItem(
        string $reference,
        string $customer,
        float $value,
        CarbonInterface $blockedSince,
        ?string $url,
        string $blockReason,
        ?string $context = null,
    ): array {
        $ageDays = (int) $blockedSince->diffInDays(now());
        $level = CommercialHandoffAttentionLevel::fromAgeAndValue($ageDays, $value);

        return [
            'reference' => $reference,
            'customer' => $customer,
            'value' => number_format($value, 2),
            'value_raw' => $value,
            'age_days' => $ageDays,
            'age_label' => $ageDays === 1 ? __('1 day') : __(':count days', ['count' => $ageDays]),
            'blocked_since' => $blockedSince,
            'attention_level' => $level->value,
            'attention_label' => $level->label(),
            'attention_variant' => $level->badgeVariant(),
            'attention_rank' => $level->rank(),
            'url' => $url,
            'block_reason' => $blockReason,
            'context' => $context,
        ];
    }
}
