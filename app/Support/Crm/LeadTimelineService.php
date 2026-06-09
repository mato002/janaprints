<?php

namespace App\Support\Crm;

use App\Enums\FollowUpStatus;
use App\Enums\LeadStatus;
use App\Models\Artwork\ArtworkRequest;
use App\Models\Crm\Lead;
use App\Models\Sales\Quotation;
use Illuminate\Support\Collection;

class LeadTimelineService
{
    /**
     * @return Collection<int, array{at: \Illuminate\Support\Carbon, title: string, body: string, badge: string, kind: string, url: ?string}>
     */
    public function eventsFor(Lead $lead, int $limit = 50): Collection
    {
        $events = collect();

        if ($lead->publicQuoteRequest) {
            $events->push([
                'at' => $lead->publicQuoteRequest->created_at,
                'title' => __('Public quote submitted'),
                'body' => $lead->publicQuoteRequest->service_needed,
                'badge' => $lead->publicQuoteRequest->sourceLabel(),
                'kind' => 'public_quote',
                'url' => auth()->user()?->can('view', $lead->publicQuoteRequest)
                    ? route('admin.public-quote-requests.show', $lead->publicQuoteRequest)
                    : null,
            ]);
        }

        $events->push([
            'at' => $lead->created_at,
            'title' => __('Lead created'),
            'body' => $lead->leadSource?->name ?? __('New opportunity'),
            'badge' => __('Created'),
            'kind' => 'created',
            'url' => null,
        ]);

        if ($lead->stage?->name) {
            $events->push([
                'at' => $lead->updated_at,
                'title' => __('Pipeline stage'),
                'body' => $lead->stage->name,
                'badge' => __('Stage'),
                'kind' => 'stage',
                'url' => null,
            ]);
        }

        foreach ($lead->activities->sortBy('activity_at') as $activity) {
            $label = ucfirst(str_replace('_', ' ', $activity->activity_type->value));
            $milestone = match ($activity->activity_type->value) {
                'call', 'email', 'whatsapp', 'sms' => __('Contacted'),
                'meeting', 'visit' => __('Qualified'),
                default => $label,
            };

            $events->push([
                'at' => $activity->activity_at,
                'title' => $milestone,
                'body' => $activity->subject,
                'badge' => $label,
                'kind' => 'activity',
                'url' => auth()->user()?->can('view', $activity)
                    ? route('admin.commercial.activities.show', $activity)
                    : null,
            ]);
        }

        foreach ($lead->followUps->where('status', FollowUpStatus::Completed) as $followUp) {
            $events->push([
                'at' => $followUp->completed_at ?? $followUp->scheduled_at,
                'title' => __('Follow-up completed'),
                'body' => $followUp->notes ?: __('Scheduled follow-up'),
                'badge' => __('Follow-up'),
                'kind' => 'follow_up',
                'url' => null,
            ]);
        }

        if (auth()->user()?->can('quotations.view')) {
            foreach (Quotation::query()->where('lead_id', $lead->id)->orderBy('created_at')->get() as $quotation) {
                $events->push([
                    'at' => $quotation->created_at,
                    'title' => __('Quoted'),
                    'body' => $quotation->quotation_number,
                    'badge' => __('Quote'),
                    'kind' => 'quote',
                    'url' => route('admin.quotations.show', $quotation),
                ]);
            }
        }

        if (auth()->user()?->can('artwork.view')) {
            $quotationIds = Quotation::query()->where('lead_id', $lead->id)->pluck('id');

            foreach (ArtworkRequest::query()
                ->whereIn('quotation_id', $quotationIds)
                ->orderBy('created_at')
                ->get() as $artwork) {
                $events->push([
                    'at' => $artwork->created_at,
                    'title' => __('Artwork requested'),
                    'body' => $artwork->request_number,
                    'badge' => __('Artwork'),
                    'kind' => 'artwork',
                    'url' => route('admin.artwork.show', $artwork),
                ]);
            }
        }

        if ($lead->customer_id && $lead->status === LeadStatus::Won) {
            $events->push([
                'at' => $lead->updated_at,
                'title' => __('Converted'),
                'body' => $lead->customer?->company_name ?? __('Customer created'),
                'badge' => __('Won'),
                'kind' => 'converted',
                'url' => $lead->customer
                    ? route('admin.crm.customers.show', $lead->customer)
                    : null,
            ]);
        }

        if ($lead->status === LeadStatus::Lost) {
            $events->push([
                'at' => $lead->updated_at,
                'title' => __('Marked lost'),
                'body' => __('Opportunity closed'),
                'badge' => __('Lost'),
                'kind' => 'lost',
                'url' => null,
            ]);
        }

        return $events
            ->sortByDesc(fn (array $event) => $event['at']?->timestamp ?? 0)
            ->take($limit)
            ->values();
    }
}
