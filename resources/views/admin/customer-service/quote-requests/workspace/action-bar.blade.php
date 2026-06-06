<section class="qr-360__action-bar" aria-label="{{ __('Commercial actions') }}">
    <div class="qr-360__action-bar-inner">
        @foreach ($workspace['action_bar'] as $action)
            @if (! empty($action['onclick']))
                <button type="button" class="qr-360__action-btn qr-360__action-btn--{{ $action['variant'] ?? 'ghost' }}" onclick="{{ $action['onclick'] }}">
                    {{ $action['label'] }}
                </button>
            @else
                <a
                    href="{{ $action['url'] }}"
                    class="qr-360__action-btn qr-360__action-btn--{{ $action['variant'] ?? 'outline' }}"
                    @if (! empty($action['external'])) target="_blank" rel="noopener" @elseif (! str_starts_with($action['url'], '#')) data-turbo-frame="erp-main" @endif
                >{{ $action['label'] }}</a>
            @endif
        @endforeach

        @can('update', $quoteRequest)
            <form method="POST" action="{{ route('admin.public-quote-requests.update-status', $quoteRequest) }}" class="inline">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="spam">
                <button type="submit" class="qr-360__action-btn qr-360__action-btn--danger" onclick="return confirm(@js(__('Reject this quote request?')))">
                    {{ __('Reject Request') }}
                </button>
            </form>
        @endcan
    </div>
</section>
