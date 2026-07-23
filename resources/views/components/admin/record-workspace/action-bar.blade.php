@props([
    'groups' => [],
])

<section class="rw-actions" aria-label="{{ __('Record actions') }}">
    <div class="rw-actions__inner">
        @foreach ($groups as $group)
            @continue(empty($group['items']))
            <div class="rw-actions__group" data-group="{{ $group['key'] ?? $loop->index }}">
                @foreach ($group['items'] as $action)
                    @if (! empty($action['html']))
                        {!! $action['html'] !!}
                    @elseif (! empty($action['onclick']))
                        <button
                            type="button"
                            class="rw-actions__btn rw-actions__btn--{{ $action['variant'] ?? 'ghost' }}"
                            onclick="{{ $action['onclick'] }}"
                        >{{ $action['label'] }}</button>
                    @else
                        <a
                            href="{{ $action['url'] }}"
                            class="rw-actions__btn rw-actions__btn--{{ $action['variant'] ?? 'outline' }}"
                            @if (! empty($action['external'])) target="_blank" rel="noopener" @elseif (! str_starts_with((string) ($action['url'] ?? ''), '#')) data-turbo-frame="erp-main" @endif
                        >{{ $action['label'] }}</a>
                    @endif
                @endforeach
            </div>
        @endforeach

        {{ $slot }}
    </div>
</section>
