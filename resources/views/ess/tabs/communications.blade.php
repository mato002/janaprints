<section class="space-y-3">
    @forelse ($communications as $message)
        <article class="ess-card">
            <div class="flex flex-col gap-1">
                <p class="font-semibold">{{ $message['subject'] ?? __('Notification') }}</p>
                <p class="text-xs text-erp-muted">
                    {{ $message['channel'] }} · {{ $message['status'] }} · {{ $message['sent_at']?->format('d M Y H:i') }}
                </p>
                @if (! empty($message['preview']))
                    <p class="mt-2 text-sm text-erp-muted line-clamp-3">{{ strip_tags($message['preview']) }}</p>
                @endif
            </div>
        </article>
    @empty
        <div class="ess-card text-sm text-erp-muted">{{ __('No communications yet.') }}</div>
    @endforelse
</section>
