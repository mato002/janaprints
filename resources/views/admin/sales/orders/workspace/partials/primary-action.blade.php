@if ($primary)
    @if (($primary['type'] ?? '') === 'link')
        <a
            href="{{ $primary['url'] }}"
            class="erp-btn-primary so-360__primary"
            @if ($primary['modal'] ?? false)
                data-erp-modal-open
            @else
                data-turbo-frame="erp-main"
            @endif
        >
            {{ $primary['label'] }}
        </a>
    @else
        <form method="POST" action="{{ $primary['action'] }}" class="inline">
            @csrf
            <button type="submit" class="erp-btn-primary so-360__primary">{{ $primary['label'] }}</button>
        </form>
    @endif
@endif
