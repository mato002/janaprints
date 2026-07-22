<?php

namespace App\Support\Sales;

use App\Enums\ArtworkRequestStatus;
use App\Enums\PublicQuoteRequestStatus;
use App\Enums\QuotationStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Artwork\ArtworkRequest;
use App\Models\Crm\Customer;
use App\Models\Production\ProductionJobCard;
use App\Models\PublicQuoteRequest;
use App\Models\Sales\Quotation;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use App\Services\Commercial\PublicQuoteRequestCountService;
use Illuminate\Http\Request;

class SalesDeskWorkQueueService
{
    public function __construct(
        protected PublicQuoteRequestCountService $quoteRequests,
    ) {}

    /**
     * @return array{summary: list<array<string, mixed>>, items: list<array<string, mixed>>}
     */
    public function present(Request $request): array
    {
        $user = $request->user();

        $newQuoteRequests = $this->quoteRequests->canView($user)
            ? $this->quoteRequests->pendingCount()
            : 0;

        $quotesAwaitingFollowUp = Quotation::query()
            ->forTenant()
            ->where('status', QuotationStatus::Sent)
            ->count();

        $ordersReadyForRelease = SalesOrder::query()
            ->forTenant()
            ->where('status', SalesOrderStatus::ReadyForProduction)
            ->whereDoesntHave('jobCard')
            ->count();

        $pendingArtwork = ArtworkRequest::query()
            ->forTenant()
            ->whereIn('status', [
                ArtworkRequestStatus::Requested,
                ArtworkRequestStatus::InDesign,
                ArtworkRequestStatus::Submitted,
                ArtworkRequestStatus::RevisionRequested,
            ])
            ->count();

        $myDraftQuotes = $user
            ? Quotation::query()
                ->forTenant()
                ->where('status', QuotationStatus::Draft)
                ->where('prepared_by', $user->id)
                ->count()
            : 0;

        $summary = array_values(array_filter([
            $this->summaryCard(__('Quote requests'), $newQuoteRequests, 'amber', $newQuoteRequests > 0 ? route('admin.public-quote-requests.index') : null),
            $this->summaryCard(__('Quotes to follow up'), $quotesAwaitingFollowUp, 'indigo', route('admin.quotations.index', ['status' => QuotationStatus::Sent->value])),
            $this->summaryCard(__('Ready for release'), $ordersReadyForRelease, 'emerald', route('admin.sales-orders.index', ['status' => SalesOrderStatus::ReadyForProduction->value])),
            $this->summaryCard(__('Artwork pending'), $pendingArtwork, 'violet', route('admin.artwork.index')),
            $this->summaryCard(__('My draft quotes'), $myDraftQuotes, 'slate', route('admin.quotations.index', ['status' => QuotationStatus::Draft->value])),
        ], fn (array $card) => $card['visible'] ?? true));

        return [
            'summary' => $summary,
            'items' => $this->queueItems($user, $newQuoteRequests, $quotesAwaitingFollowUp, $ordersReadyForRelease, $myDraftQuotes),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function queueItems(
        ?User $user,
        int $newQuoteRequests,
        int $quotesAwaitingFollowUp,
        int $ordersReadyForRelease,
        int $myDraftQuotes,
    ): array {
        $items = collect();

        if ($newQuoteRequests > 0 && $this->quoteRequests->canView($user)) {
            PublicQuoteRequest::query()
                ->whereIn('status', [
                    PublicQuoteRequestStatus::Pending->value,
                    PublicQuoteRequestStatus::Reviewing->value,
                ])
                ->latest('created_at')
                ->limit(3)
                ->get(['id', 'uuid', 'name', 'company', 'created_at'])
                ->each(function (PublicQuoteRequest $lead) use ($items) {
                    $items->push([
                        'kind' => 'quote_request',
                        'label' => $lead->company ?: $lead->name ?: $lead->reference(),
                        'meta' => $lead->reference(),
                        'tone' => 'amber',
                        'url' => route('admin.public-quote-requests.show', $lead),
                    ]);
                });
        }

        Quotation::query()
            ->forTenant()
            ->where('status', QuotationStatus::Sent)
            ->with('customer:id,company_name,public_id')
            ->latest('quotation_date')
            ->limit(3)
            ->get()
            ->each(function (Quotation $quote) use ($items) {
                $items->push([
                    'kind' => 'quotation',
                    'label' => $quote->quotation_number,
                    'meta' => $quote->customer?->name,
                    'tone' => 'indigo',
                    'url' => route('admin.quotations.show', [$quote, 'from' => 'sales-desk']),
                    'modal' => true,
                ]);
            });

        SalesOrder::query()
            ->forTenant()
            ->where('status', SalesOrderStatus::ReadyForProduction)
            ->whereDoesntHave('jobCard')
            ->with('customer:id,company_name,public_id')
            ->latest('order_date')
            ->limit(3)
            ->get()
            ->each(function (SalesOrder $order) use ($items) {
                $customerKey = $order->customer?->getRouteKey();

                $items->push([
                    'kind' => 'release',
                    'label' => $order->order_number,
                    'meta' => $order->customer?->name,
                    'tone' => 'emerald',
                    'url' => $customerKey
                        ? route('admin.sales.desk', [
                            'customer' => $customerKey,
                            'order' => $order->getRouteKey(),
                            'step' => 4,
                        ])
                        : route('admin.sales-orders.show', [$order, 'from' => 'sales-desk']),
                    'modal' => ! $customerKey,
                ]);
            });

        if ($user && $myDraftQuotes > 0) {
            Quotation::query()
                ->forTenant()
                ->where('status', QuotationStatus::Draft)
                ->where('prepared_by', $user->id)
                ->with('customer:id,company_name,public_id')
                ->latest('updated_at')
                ->limit(3)
                ->get()
                ->each(function (Quotation $quote) use ($items) {
                    $items->push([
                        'kind' => 'draft_quote',
                        'label' => __('Resume draft'),
                        'meta' => trim(($quote->customer?->name ?? '').' · '.$quote->quotation_number),
                        'tone' => 'slate',
                        'url' => route('admin.quotations.edit', [$quote, 'from' => 'sales-desk']),
                        'modal' => true,
                    ]);
                });
        }

        PublicQuoteRequest::query()
            ->whereIn('status', [
                PublicQuoteRequestStatus::Pending->value,
                PublicQuoteRequestStatus::Reviewing->value,
            ])
            ->whereNotNull('target_follow_up_at')
            ->whereDate('target_follow_up_at', '<=', now()->addDay())
            ->orderBy('target_follow_up_at')
            ->limit(2)
            ->get(['id', 'uuid', 'name', 'company', 'target_follow_up_at'])
            ->each(function (PublicQuoteRequest $lead) use ($items) {
                $items->push([
                    'kind' => 'follow_up',
                    'label' => __('Follow up').': '.($lead->company ?: $lead->name ?: $lead->reference()),
                    'meta' => $lead->target_follow_up_at?->format('d M Y'),
                    'tone' => 'rose',
                    'url' => route('admin.public-quote-requests.show', $lead),
                ]);
            });

        return $items->take(10)->values()->all();
    }

    /**
     * @return array<string, mixed>
     */
    protected function summaryCard(string $label, int $value, string $tone, ?string $url = null): array
    {
        return [
            'label' => $label,
            'value' => $value,
            'tone' => $tone,
            'url' => $url,
            'visible' => true,
        ];
    }
}
