<?php

namespace App\Support\Commercial;

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

        $artwork = $this->artwork($quoteRequest);
        $whatsapp = config('conversion.whatsapp');
        $whatsappNumber = preg_replace('/\D+/', '', (string) ($whatsapp['number'] ?? ''));
        $whatsappMessage = rawurlencode(__('Hi :name, regarding your quote request with Jana Prints.', ['name' => $quoteRequest->name]));

        return [
            'reference' => $quoteRequest->reference(),
            'artwork' => $artwork,
            'artwork_count' => $artwork ? 1 : 0,
            'timeline' => $this->timeline($quoteRequest),
            'notes_feed' => $this->notesFeed($quoteRequest),
            'sidebar' => $this->sidebar($quoteRequest, $artwork),
            'quick_actions' => $this->quickActions($quoteRequest, $whatsappNumber, $whatsappMessage),
            'workflow_actions' => $this->workflowActions($quoteRequest),
            'assignable_users' => $this->assignableUsers(),
            'links' => [
                'customer_exists' => false,
                'lead_exists' => false,
                'quotation_exists' => false,
                'order_exists' => false,
            ],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function artwork(PublicQuoteRequest $quoteRequest): ?array
    {
        if (! $quoteRequest->artwork_path) {
            return null;
        }

        $disk = config('leads.artwork.disk', 'public');

        if (! Storage::disk($disk)->exists($quoteRequest->artwork_path)) {
            return null;
        }

        $extension = strtolower(pathinfo($quoteRequest->artwork_path, PATHINFO_EXTENSION));

        return [
            'name' => $quoteRequest->artwork_original_name ?? basename($quoteRequest->artwork_path),
            'extension' => $extension,
            'preview_url' => route('admin.public-quote-requests.artwork-preview', $quoteRequest),
            'download_url' => route('admin.public-quote-requests.artwork', $quoteRequest),
            'is_image' => in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'svg'], true),
            'is_pdf' => $extension === 'pdf',
            'size' => Storage::disk($disk)->size($quoteRequest->artwork_path),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function timeline(PublicQuoteRequest $quoteRequest): array
    {
        $events = [
            [
                'badge' => __('Submitted'),
                'title' => __('Request submitted'),
                'body' => __('Storefront quote request received'),
                'at' => $quoteRequest->created_at,
                'url' => null,
            ],
        ];

        if ($quoteRequest->status !== PublicQuoteRequestStatus::Pending) {
            $events[] = [
                'badge' => __('Status'),
                'title' => __('Status updated'),
                'body' => $quoteRequest->status->workspaceLabel(),
                'at' => $quoteRequest->updated_at,
                'url' => null,
            ];
        }

        if ($quoteRequest->artwork_path) {
            $events[] = [
                'badge' => __('Artwork'),
                'title' => __('Artwork uploaded'),
                'body' => $quoteRequest->artwork_original_name ?? __('Customer artwork file'),
                'at' => $quoteRequest->created_at,
                'url' => null,
            ];
        }

        foreach ($quoteRequest->notes as $note) {
            $events[] = [
                'badge' => __('Note'),
                'title' => __('Internal note added'),
                'body' => \Illuminate\Support\Str::limit($note->body, 120),
                'at' => $note->created_at,
                'url' => null,
            ];
        }

        if ($quoteRequest->responded_at) {
            $events[] = [
                'badge' => __('Response'),
                'title' => __('Commercial response recorded'),
                'body' => $quoteRequest->status->workspaceLabel(),
                'at' => $quoteRequest->responded_at,
                'url' => null,
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
    protected function sidebar(PublicQuoteRequest $quoteRequest, ?array $artwork): array
    {
        return [
            'status' => $quoteRequest->status,
            'priority' => $quoteRequest->priority?->label() ?? __('Not set'),
            'assigned_to' => $quoteRequest->assignee?->name ?? __('Unassigned'),
            'artwork_count' => $artwork ? 1 : 0,
            'submitted_at' => $quoteRequest->created_at,
            'updated_at' => $quoteRequest->updated_at,
        ];
    }

    /**
     * @return list<array<string, string>>
     */
    protected function quickActions(PublicQuoteRequest $quoteRequest, string $whatsappNumber, string $whatsappMessage): array
    {
        $phone = preg_replace('/\s+/', '', $quoteRequest->phone);

        return array_values(array_filter([
            ['label' => __('Call Customer'), 'url' => 'tel:'.$phone, 'variant' => 'outline', 'external' => true],
            ['label' => __('Email Customer'), 'url' => 'mailto:'.$quoteRequest->email, 'variant' => 'outline', 'external' => true],
            $whatsappNumber !== '' ? [
                'label' => __('WhatsApp Customer'),
                'url' => 'https://wa.me/'.$whatsappNumber.'?text='.$whatsappMessage,
                'variant' => 'outline',
                'external' => true,
            ] : null,
            Route::has('admin.quotations.create') ? [
                'label' => __('Convert To Quotation'),
                'url' => route('admin.quotations.create'),
                'variant' => 'primary',
                'external' => false,
            ] : null,
        ]));
    }

    /**
     * @return list<array<string, string>>
     */
    protected function workflowActions(PublicQuoteRequest $quoteRequest): array
    {
        return array_values(array_filter([
            Route::has('admin.quotations.create') ? [
                'label' => __('Create Quotation'),
                'url' => route('admin.quotations.create'),
                'variant' => 'primary',
            ] : null,
            Route::has('admin.crm.customers.create') ? [
                'label' => __('Create Customer'),
                'url' => route('admin.crm.customers.create'),
                'variant' => 'outline',
            ] : null,
            Route::has('admin.crm.leads.create') ? [
                'label' => __('Convert To Lead'),
                'url' => route('admin.crm.leads.create'),
                'variant' => 'outline',
            ] : null,
            $quoteRequest->artwork_path ? [
                'label' => __('Download Artwork'),
                'url' => route('admin.public-quote-requests.artwork', $quoteRequest),
                'variant' => 'outline',
            ] : null,
            [
                'label' => __('Email Customer'),
                'url' => 'mailto:'.$quoteRequest->email,
                'variant' => 'outline',
            ],
            [
                'label' => __('Print Request'),
                'url' => '#',
                'onclick' => 'window.print()',
                'variant' => 'ghost',
            ],
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
