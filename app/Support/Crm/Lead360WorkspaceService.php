<?php

namespace App\Support\Crm;

use App\Enums\FollowUpStatus;
use App\Enums\LeadStatus;
use App\Models\Artwork\ArtworkRequest;
use App\Models\Crm\Lead;
use App\Models\PublicQuoteRequest;
use App\Models\Sales\Quotation;
use App\Support\EnumLabel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

class Lead360WorkspaceService
{
    public function __construct(
        protected LeadTimelineService $timeline,
        protected LeadQuotationService $leadQuotation,
        protected CrmSettings $crmSettings,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function present(Lead $lead): array
    {
        $user = auth()->user();
        $canQuotes = $user?->can('quotations.view') ?? false;
        $canArtwork = $user?->can('artwork.view') ?? false;

        $quotations = $canQuotes
            ? Quotation::query()
                ->where('lead_id', $lead->id)
                ->with('customer')
                ->orderByDesc('created_at')
                ->get()
            : collect();

        $quotationIds = $quotations->pluck('id');

        $artwork = collect();

        if ($canArtwork) {
            if ($quotationIds->isNotEmpty()) {
                $artwork = ArtworkRequest::query()
                    ->whereIn('quotation_id', $quotationIds)
                    ->orderByDesc('created_at')
                    ->get();
            }

            if ($lead->publicQuoteRequest?->artwork_request_id) {
                $linked = ArtworkRequest::query()->find($lead->publicQuoteRequest->artwork_request_id);

                if ($linked && ! $artwork->contains(fn (ArtworkRequest $item) => $item->id === $linked->id)) {
                    $artwork->push($linked);
                }
            }
        }

        $followUps = $lead->followUps->sortByDesc('scheduled_at');
        $now = now();

        $scheduled = $followUps->where('status', FollowUpStatus::Pending);
        $overdue = $scheduled->filter(fn ($fu) => $fu->scheduled_at && $fu->scheduled_at->lt($now));
        $completed = $followUps->where('status', FollowUpStatus::Completed);

        return [
            'kpis' => $this->kpis($lead, $quotations, $scheduled, $overdue, $completed),
            'quotationActions' => [
                'can_create' => $user ? $this->leadQuotation->canCreateFromLead($user, $lead) : false,
                'auto_convert_enabled' => $this->crmSettings->autoConvertLeadOnQuote($lead->company_id, $lead->branch_id),
                'needs_customer' => ! $lead->customer_id,
            ],
            'quotations' => $this->mapQuotations($quotations),
            'artwork' => $this->mapArtwork($artwork),
            'followUps' => [
                'scheduled' => $this->mapFollowUps($scheduled),
                'overdue' => $this->mapFollowUps($overdue),
                'completed' => $this->mapFollowUps($completed),
            ],
            'acquisition' => $this->acquisitionContext($lead),
            'timeline' => $this->timeline->eventsFor($lead),
            'conversionHistory' => $this->conversionHistory($lead),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function acquisitionContext(Lead $lead): ?array
    {
        $quoteRequest = $lead->publicQuoteRequest;

        if (! $quoteRequest) {
            return null;
        }

        return [
            'origin' => $quoteRequest->sourceLabel(),
            'reference' => $quoteRequest->reference(),
            'url' => auth()->user()?->can('view', $quoteRequest)
                ? route('admin.public-quote-requests.show', $quoteRequest)
                : null,
            'requested_product' => $quoteRequest->service_needed,
            'quantity' => $quoteRequest->quantity,
            'budget' => $quoteRequest->expected_value,
            'deadline' => $quoteRequest->deadline,
            'message' => $quoteRequest->message,
            'attachments' => $this->publicQuoteArtwork($quoteRequest),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function publicQuoteArtwork(PublicQuoteRequest $quoteRequest): array
    {
        if (! $quoteRequest->artwork_path) {
            return [];
        }

        $disk = config('leads.artwork.disk', 'public');

        if (! Storage::disk($disk)->exists($quoteRequest->artwork_path)) {
            return [];
        }

        $extension = strtolower(pathinfo($quoteRequest->artwork_path, PATHINFO_EXTENSION));

        $attachment = [
            'name' => $quoteRequest->artwork_original_name ?? basename($quoteRequest->artwork_path),
            'extension' => $extension,
            'is_image' => in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'svg'], true),
            'is_pdf' => $extension === 'pdf',
            'size' => Storage::disk($disk)->size($quoteRequest->artwork_path),
        ];

        if (auth()->user()?->can('view', $quoteRequest) && Route::has('admin.public-quote-requests.artwork')) {
            $attachment['download_url'] = route('admin.public-quote-requests.artwork', $quoteRequest);
            $attachment['preview_url'] = route('admin.public-quote-requests.artwork-preview', $quoteRequest);
        }

        return [$attachment];
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function kpis(Lead $lead, Collection $quotations, Collection $scheduled, Collection $overdue, Collection $completed): array
    {
        return [
            [
                'key' => 'value',
                'priority' => 'high',
                'icon' => 'revenue',
                'label' => __('Estimated value'),
                'value' => (float) $lead->estimated_value > 0 ? $lead->estimated_value : null,
                'hint' => $lead->probability ? $lead->probability.'% '.__('probability') : null,
                'format' => 'money',
                'trend' => null,
            ],
            [
                'key' => 'follow_ups',
                'priority' => 'high',
                'icon' => 'activity',
                'label' => __('Follow-ups due'),
                'value' => $scheduled->count() > 0 ? $scheduled->count() : null,
                'hint' => $overdue->count() > 0
                    ? $overdue->count().' '.__('overdue')
                    : __('On track'),
                'format' => null,
                'trend' => $overdue->count() > 0 ? 'alert' : null,
            ],
            [
                'key' => 'quotes',
                'priority' => 'medium',
                'icon' => 'quote',
                'label' => __('Quotations'),
                'value' => auth()->user()?->can('quotations.view') ? ($quotations->count() ?: null) : null,
                'hint' => __('Linked quotes'),
                'format' => null,
                'trend' => $quotations->count() > 0 ? 'up' : null,
            ],
            [
                'key' => 'activities',
                'priority' => 'medium',
                'icon' => 'chat',
                'label' => __('Activities'),
                'value' => $lead->activities->count() ?: null,
                'hint' => __('Logged touchpoints'),
                'format' => null,
                'trend' => null,
            ],
            [
                'key' => 'close_date',
                'priority' => 'low',
                'icon' => 'activity',
                'label' => __('Expected close'),
                'value' => $lead->expected_close_date,
                'hint' => $lead->expected_close_date?->format('d M Y'),
                'format' => 'date',
                'trend' => $lead->expected_close_date && $lead->expected_close_date->isPast() ? 'alert' : null,
            ],
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    protected function mapQuotations(Collection $quotations): Collection
    {
        return $quotations->map(fn (Quotation $q) => [
            'id' => $q->id,
            'number' => $q->quotation_number,
            'status' => EnumLabel::of($q->status),
            'total' => number_format((float) $q->total_amount, 2),
            'date' => $q->created_at,
            'url' => route('admin.quotations.show', $q),
        ]);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    protected function mapArtwork(Collection $artwork): Collection
    {
        return $artwork->map(fn (ArtworkRequest $a) => [
            'id' => $a->id,
            'number' => $a->request_number,
            'status' => EnumLabel::of($a->status),
            'date' => $a->created_at,
            'url' => route('admin.artwork.show', $a),
        ]);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    protected function mapFollowUps(Collection $followUps): Collection
    {
        return $followUps->map(fn ($fu) => [
            'id' => $fu->id,
            'scheduled_at' => $fu->scheduled_at,
            'completed_at' => $fu->completed_at,
            'status' => $fu->status->value,
            'notes' => $fu->notes,
            'assignee' => $fu->assignee?->name,
        ])->values();
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function conversionHistory(Lead $lead): array
    {
        $history = [
            [
                'event' => __('Lead created'),
                'at' => $lead->created_at,
                'detail' => $lead->publicQuoteRequest
                    ? __('From public quote :ref', ['ref' => $lead->publicQuoteRequest->reference()])
                    : $lead->leadSource?->name,
                'actor' => null,
                'url' => $lead->publicQuoteRequest && auth()->user()?->can('view', $lead->publicQuoteRequest)
                    ? route('admin.public-quote-requests.show', $lead->publicQuoteRequest)
                    : null,
            ],
        ];

        if ($lead->customer_id && $lead->status === LeadStatus::Won) {
            $history[] = [
                'event' => __('Converted to customer'),
                'at' => $lead->updated_at,
                'detail' => $lead->customer?->company_name,
                'actor' => null,
                'url' => $lead->customer
                    ? route('admin.crm.customers.show', $lead->customer)
                    : null,
            ];
        }

        if ($lead->status === LeadStatus::Lost) {
            $history[] = [
                'event' => __('Marked as lost'),
                'at' => $lead->updated_at,
                'detail' => __('Opportunity closed'),
                'actor' => null,
            ];
        }

        return collect($history)->sortByDesc('at')->values()->all();
    }
}
