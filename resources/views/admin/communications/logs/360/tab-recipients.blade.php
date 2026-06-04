<div class="comm-log-360__recipient-grid">
    @forelse ($log->recipients as $recipient)
        <article class="comm-log-360__recipient-card">
            <div class="comm-log-360__recipient-head">
                <h3 class="comm-log-360__recipient-name">{{ $recipient->display_name ?: __('Recipient') }}</h3>
                @if ($recipient->delivery_status)
                    <span class="comm-log-360__badge {{ $recipient->delivery_status->badgeClass() }}">
                        {{ $recipient->delivery_status->label() }}
                    </span>
                @endif
            </div>
            <dl class="comm-log-360__recipient-meta">
                @if ($recipient->phone)
                    <div>
                        <dt>{{ __('Phone') }}</dt>
                        <dd>{{ $recipient->phone }}</dd>
                    </div>
                @endif
                @if ($recipient->email)
                    <div>
                        <dt>{{ __('Email') }}</dt>
                        <dd><a href="mailto:{{ $recipient->email }}" class="comm-log-360__link">{{ $recipient->email }}</a></dd>
                    </div>
                @endif
                @if ($recipient->read_at)
                    <div>
                        <dt>{{ __('Read at') }}</dt>
                        <dd>{{ $recipient->read_at->format('d M Y H:i') }}</dd>
                    </div>
                @endif
            </dl>
        </article>
    @empty
        <p class="comm-log-360__empty">{{ __('No recipients recorded for this communication.') }}</p>
    @endforelse
</div>
