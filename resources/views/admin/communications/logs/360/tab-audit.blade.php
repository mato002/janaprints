<section class="comm-log-360__card">
    <h2 class="comm-log-360__card-title">{{ __('Activity ledger') }}</h2>
    <ul class="comm-log-360__ledger" role="list">
        @forelse ($auditEntries as $entry)
            <li class="comm-log-360__ledger-row">
                <div class="comm-log-360__ledger-action">{{ $entry['action'] }}</div>
                <div class="comm-log-360__ledger-user">{{ $entry['user'] }}</div>
                <time class="comm-log-360__ledger-time">{{ $entry['at']?->format('d M Y • H:i') }}</time>
            </li>
        @empty
            <li class="comm-log-360__empty">{{ __('No audit activity recorded.') }}</li>
        @endforelse
    </ul>
</section>
