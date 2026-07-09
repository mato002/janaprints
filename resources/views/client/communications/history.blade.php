<x-layouts.client :title="__('Communication history')" :heading="__('Communication history')">
    <div class="client-grid client-grid--single">
        @forelse ($logs as $log)
            <article class="client-card">
                <div class="client-card__head">
                    <div>
                        <p class="client-card__eyebrow">{{ app(\App\Services\Client\ClientPortalCommunicationService::class)->categoryLabel($log) }}</p>
                        <h2 class="client-card__title">{{ $log->subject ?: __('Notification') }}</h2>
                    </div>
                    <span class="client-list-item__meta">{{ $log->created_at?->format('M j, Y g:i A') }}</span>
                </div>
                <p class="client-card__body">{{ \Illuminate\Support\Str::limit(strip_tags((string) $log->body), 240) }}</p>
            </article>
        @empty
            @include('client.partials.empty-state', [
                'icon' => 'inbox',
                'message' => __('No communication history yet.'),
            ])
        @endforelse
    </div>
    {{ $logs->links() }}
</x-layouts.client>
