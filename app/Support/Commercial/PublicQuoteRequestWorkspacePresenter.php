<?php

namespace App\Support\Commercial;

use App\Enums\PublicQuoteRequestPriority;
use App\Enums\PublicQuoteRequestStatus;
use App\Models\PublicQuoteRequest;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

class PublicQuoteRequestWorkspacePresenter
{
    /**
     * @return array<string, mixed>
     */
    public function build(PublicQuoteRequest $quoteRequest): array
    {
        $quoteRequest->load(['assignee', 'notes.user']);

        $artworkFiles = $this->artworkFiles($quoteRequest);
        $primaryArtwork = $artworkFiles[0] ?? null;
        $whatsapp = config('conversion.whatsapp');
        $whatsappNumber = preg_replace('/\D+/', '', (string) ($whatsapp['number'] ?? ''));
        $whatsappMessage = rawurlencode(__('Hi :name, regarding your quote request with Jana Prints.', ['name' => $quoteRequest->name]));

        return [
            'reference' => $quoteRequest->reference(),
            'header' => $this->header($quoteRequest),
            'snapshot' => $this->snapshot($quoteRequest),
            'artwork' => $primaryArtwork,
            'artwork_files' => $artworkFiles,
            'artwork_count' => count($artworkFiles),
            'action_bar' => $this->actionBar($quoteRequest, $whatsappNumber, $whatsappMessage),
            'timeline' => $this->timeline($quoteRequest),
            'notes_feed' => $this->notesFeed($quoteRequest),
            'sidebar' => $this->sidebar($quoteRequest, $artworkFiles),
            'lead_score' => $this->leadScore($quoteRequest),
            'next_action' => $this->nextAction($quoteRequest, $primaryArtwork),
            'conversion' => $this->conversionTracker($quoteRequest),
            'assignable_users' => $this->assignableUsers(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function header(PublicQuoteRequest $quoteRequest): array
    {
        return [
            'reference' => $quoteRequest->reference(),
            'status_label' => $quoteRequest->status->workspaceLabel(),
            'status_variant' => $quoteRequest->status->badgeVariant(),
            'customer_name' => $quoteRequest->name,
            'service' => $quoteRequest->service_needed,
            'quantity' => $quoteRequest->quantity ?: '—',
            'submitted_at' => $quoteRequest->created_at->format('d M Y'),
            'priority_label' => $quoteRequest->priority?->label() ?? __('Not set'),
            'assigned_to' => $quoteRequest->assignee?->name ?? __('Unassigned'),
            'expected_value' => $quoteRequest->expected_value
                ? number_format((float) $quoteRequest->expected_value, 2)
                : '—',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function snapshot(PublicQuoteRequest $quoteRequest): array
    {
        return [
            'name' => $quoteRequest->name,
            'company' => $quoteRequest->company,
            'phone' => $quoteRequest->phone,
            'email' => $quoteRequest->email,
            'service' => $quoteRequest->service_needed,
            'quantity' => $quoteRequest->quantity ?: '—',
            'deadline' => $quoteRequest->deadline ?: '—',
            'source' => ucfirst($quoteRequest->source),
            'message' => $quoteRequest->message,
            'submitted_at' => $quoteRequest->created_at->format('d M Y, H:i'),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function artworkFiles(PublicQuoteRequest $quoteRequest): array
    {
        if (! $quoteRequest->artwork_path) {
            return [];
        }

        $disk = config('leads.artwork.disk', 'public');

        if (! Storage::disk($disk)->exists($quoteRequest->artwork_path)) {
            return [];
        }

        $extension = strtolower(pathinfo($quoteRequest->artwork_path, PATHINFO_EXTENSION));

        return [[
            'id' => 'primary',
            'name' => $quoteRequest->artwork_original_name ?? basename($quoteRequest->artwork_path),
            'extension' => $extension,
            'preview_url' => route('admin.public-quote-requests.artwork-preview', $quoteRequest),
            'download_url' => route('admin.public-quote-requests.artwork', $quoteRequest),
            'is_image' => in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'svg'], true),
            'is_pdf' => $extension === 'pdf',
            'size' => Storage::disk($disk)->size($quoteRequest->artwork_path),
            'uploaded_at' => $quoteRequest->created_at,
        ]];
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function timeline(PublicQuoteRequest $quoteRequest): array
    {
        $events = [
            [
                'type' => 'submitted',
                'badge' => __('Submitted'),
                'title' => __('Request submitted'),
                'body' => __('Storefront quote request received'),
                'at' => $quoteRequest->created_at,
            ],
        ];

        if ($quoteRequest->artwork_path) {
            $events[] = [
                'type' => 'artwork',
                'badge' => __('Artwork'),
                'title' => __('Artwork uploaded'),
                'body' => $quoteRequest->artwork_original_name ?? __('Customer artwork file'),
                'at' => $quoteRequest->created_at,
            ];
        }

        if ($quoteRequest->status !== PublicQuoteRequestStatus::Pending) {
            $events[] = [
                'type' => 'status',
                'badge' => __('Status'),
                'title' => __('Status updated'),
                'body' => $quoteRequest->status->workspaceLabel(),
                'at' => $quoteRequest->updated_at,
            ];
        }

        foreach ($quoteRequest->notes as $note) {
            $events[] = [
                'type' => 'note',
                'badge' => __('Note'),
                'title' => __('Internal note added'),
                'body' => \Illuminate\Support\Str::limit($note->body, 120),
                'at' => $note->created_at,
            ];
        }

        if ($quoteRequest->responded_at) {
            $events[] = [
                'type' => 'response',
                'badge' => __('Follow-up'),
                'title' => __('Commercial response recorded'),
                'body' => $quoteRequest->status->workspaceLabel(),
                'at' => $quoteRequest->responded_at,
            ];
        }

        if ($quoteRequest->target_follow_up_at) {
            $events[] = [
                'type' => 'followup',
                'badge' => __('Follow-up'),
                'title' => __('Follow-up scheduled'),
                'body' => $quoteRequest->target_follow_up_at->format('d M Y'),
                'at' => $quoteRequest->target_follow_up_at,
            ];
        }

        return collect($events)
            ->sortByDesc(fn (array $event) => $event['at']?->timestamp ?? 0)
            ->values()
            ->all();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    protected function notesFeed(PublicQuoteRequest $quoteRequest): Collection
    {
        $feed = $quoteRequest->notes->map(fn ($note) => [
            'author' => $note->user?->name ?? __('System'),
            'body' => $note->body,
            'at' => $note->created_at,
            'legacy' => false,
        ]);

        if ($quoteRequest->admin_notes && $feed->isEmpty()) {
            $feed->push([
                'author' => __('Legacy note'),
                'body' => $quoteRequest->admin_notes,
                'at' => $quoteRequest->updated_at,
                'legacy' => true,
            ]);
        }

        return $feed->sortByDesc('at')->values();
    }

    /**
     * @return array<string, mixed>
     */
    protected function sidebar(PublicQuoteRequest $quoteRequest, array $artworkFiles): array
    {
        $lastActivity = collect([
            $quoteRequest->updated_at,
            $quoteRequest->notes->max('created_at'),
            $quoteRequest->responded_at,
        ])->filter()->max();

        return [
            'status' => $quoteRequest->status,
            'priority' => $quoteRequest->priority?->label() ?? __('Not set'),
            'assigned_to' => $quoteRequest->assignee?->name ?? __('Unassigned'),
            'expected_value' => $quoteRequest->expected_value
                ? number_format((float) $quoteRequest->expected_value, 2)
                : '—',
            'probability' => $quoteRequest->probability !== null ? $quoteRequest->probability.'%' : '—',
            'follow_up_at' => $quoteRequest->target_follow_up_at?->format('d M Y') ?? '—',
            'artwork_count' => count($artworkFiles),
            'submitted_at' => $quoteRequest->created_at,
            'updated_at' => $quoteRequest->updated_at,
            'last_activity' => $lastActivity,
            'phone' => $quoteRequest->phone,
            'email' => $quoteRequest->email,
        ];
    }

    /**
     * @return array{key: string, label: string, variant: string}
     */
    protected function leadScore(PublicQuoteRequest $quoteRequest): array
    {
        $points = 0;

        $points += match ($quoteRequest->priority) {
            PublicQuoteRequestPriority::Urgent => 40,
            PublicQuoteRequestPriority::High => 28,
            PublicQuoteRequestPriority::Normal => 10,
            PublicQuoteRequestPriority::Low => 0,
            default => 0,
        };

        $value = (float) ($quoteRequest->expected_value ?? 0);
        if ($value >= 50000) {
            $points += 30;
        } elseif ($value >= 15000) {
            $points += 18;
        } elseif ($value >= 5000) {
            $points += 8;
        }

        if ($quoteRequest->status === PublicQuoteRequestStatus::Pending
            && $quoteRequest->created_at->lt(now()->subHours(48))) {
            $points += 15;
        }

        if ($quoteRequest->probability !== null && $quoteRequest->probability >= 70) {
            $points += 12;
        }

        $key = match (true) {
            $points >= 45 => 'hot',
            $points >= 20 => 'warm',
            default => 'cold',
        };

        return [
            'key' => $key,
            'label' => match ($key) {
                'hot' => __('Hot'),
                'warm' => __('Warm'),
                default => __('Cold'),
            },
            'variant' => $key,
        ];
    }

    /**
     * @return array{label: string, hint: string, tone: string}
     */
    protected function nextAction(PublicQuoteRequest $quoteRequest, ?array $artwork): array
    {
        if (in_array($quoteRequest->status, [PublicQuoteRequestStatus::Spam, PublicQuoteRequestStatus::Closed], true)) {
            return [
                'label' => __('Await Customer Response'),
                'hint' => __('This request has been closed or rejected.'),
                'tone' => 'muted',
            ];
        }

        if ($quoteRequest->target_follow_up_at?->isPast()) {
            return [
                'label' => __('Follow Up'),
                'hint' => __('Target follow-up date has passed.'),
                'tone' => 'warning',
            ];
        }

        if ($artwork && $quoteRequest->status === PublicQuoteRequestStatus::Pending) {
            return [
                'label' => __('Review Artwork'),
                'hint' => __('Customer artwork is ready for commercial review.'),
                'tone' => 'accent',
            ];
        }

        if (! $quoteRequest->assigned_to) {
            return [
                'label' => __('Assign Salesperson'),
                'hint' => __('Assign ownership before progressing this opportunity.'),
                'tone' => 'accent',
            ];
        }

        if ($quoteRequest->status === PublicQuoteRequestStatus::Reviewing) {
            return [
                'label' => __('Prepare Quotation'),
                'hint' => __('Request has been reviewed — convert to quotation.'),
                'tone' => 'success',
            ];
        }

        if ($quoteRequest->status === PublicQuoteRequestStatus::Pending) {
            return [
                'label' => __('Call Customer'),
                'hint' => __('Qualify requirements and confirm scope with the customer.'),
                'tone' => 'accent',
            ];
        }

        if ($quoteRequest->status === PublicQuoteRequestStatus::Quoted) {
            return [
                'label' => __('Follow Up'),
                'hint' => __('Quotation sent — await customer decision.'),
                'tone' => 'success',
            ];
        }

        return [
            'label' => __('Await Customer Response'),
            'hint' => __('Monitor this opportunity for customer feedback.'),
            'tone' => 'muted',
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function conversionTracker(PublicQuoteRequest $quoteRequest): array
    {
        return [
            [
                'key' => 'customer',
                'label' => __('Customer'),
                'linked' => false,
                'reference' => null,
                'url' => Route::has('admin.crm.customers.create') ? route('admin.crm.customers.create') : null,
            ],
            [
                'key' => 'lead',
                'label' => __('Lead'),
                'linked' => false,
                'reference' => null,
                'url' => Route::has('admin.crm.leads.create') ? route('admin.crm.leads.create') : null,
            ],
            [
                'key' => 'quotation',
                'label' => __('Quotation'),
                'linked' => $quoteRequest->status === PublicQuoteRequestStatus::Quoted,
                'reference' => $quoteRequest->status === PublicQuoteRequestStatus::Quoted ? __('Quoted') : null,
                'url' => Route::has('admin.quotations.create') ? route('admin.quotations.create') : null,
            ],
            [
                'key' => 'order',
                'label' => __('Sales Order'),
                'linked' => false,
                'reference' => null,
                'url' => Route::has('admin.sales-orders.index') ? route('admin.sales-orders.index') : null,
            ],
        ];
    }

    /**
     * @return list<array<string, string>>
     */
    protected function actionBar(PublicQuoteRequest $quoteRequest, string $whatsappNumber, string $whatsappMessage): array
    {
        $phone = preg_replace('/\s+/', '', $quoteRequest->phone);

        return array_values(array_filter([
            ['key' => 'call', 'label' => __('Call Customer'), 'url' => 'tel:'.$phone, 'variant' => 'outline', 'external' => true],
            $whatsappNumber !== '' ? [
                'key' => 'whatsapp',
                'label' => __('WhatsApp'),
                'url' => 'https://wa.me/'.$whatsappNumber.'?text='.$whatsappMessage,
                'variant' => 'outline',
                'external' => true,
            ] : null,
            ['key' => 'email', 'label' => __('Email'), 'url' => 'mailto:'.$quoteRequest->email, 'variant' => 'outline', 'external' => true],
            ['key' => 'assign', 'label' => __('Assign'), 'url' => '#qr-360-review', 'variant' => 'outline', 'external' => false],
            Route::has('admin.quotations.create') ? [
                'key' => 'quotation',
                'label' => __('Create Quotation'),
                'url' => route('admin.quotations.create'),
                'variant' => 'primary',
                'external' => false,
            ] : null,
            Route::has('admin.crm.leads.create') ? [
                'key' => 'lead',
                'label' => __('Convert To Lead'),
                'url' => route('admin.crm.leads.create'),
                'variant' => 'outline',
                'external' => false,
            ] : null,
            ['key' => 'print', 'label' => __('Print'), 'url' => '#', 'variant' => 'ghost', 'onclick' => 'window.print()'],
        ]));
    }

    /**
     * @return Collection<int, User>
     */
    protected function assignableUsers(): Collection
    {
        return User::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
    }
}
