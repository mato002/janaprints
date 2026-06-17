@props([
    'href' => null,
    'method' => null,
    'action' => null,
    'variant' => 'default',
    'confirm' => null,
])

@php
    use App\Support\Platform\ModalFormRoutes;

    $classes = match ($variant) {
        'danger' => 'border-t border-red-100 text-red-700 hover:bg-red-50',
        default => 'text-erp-primary hover:bg-erp-page',
    };
@endphp

@if ($action && $method)
    <form method="POST" action="{{ $action }}" class="block" @if($confirm) onsubmit="return confirm(@js($confirm))" @endif>
        @csrf
        @if (in_array(strtoupper($method), ['PUT', 'PATCH', 'DELETE']))
            @method($method)
        @endif
        <button type="submit" @click="$dispatch('erp-row-menu-close')" {{ $attributes->merge(['class' => "flex w-full items-center gap-2 px-3 py-2 text-left text-sm {$classes}"]) }}>
            {{ $slot }}
        </button>
    </form>
@elseif ($href)
    @php
        $isModalOpen = $attributes->has('data-erp-modal-open')
            || (! $attributes->has('data-no-modal') && ModalFormRoutes::supportsUrl($href));
        $linkAttributes = $attributes->merge([
            'class' => "flex w-full items-center gap-2 px-3 py-2 text-sm {$classes}",
        ]);

        $isDownloadLink = is_string($href) && (str_contains($href, '/download') || str_ends_with($href, '.pdf'));

        if ($isModalOpen) {
            $linkAttributes = $linkAttributes->merge(['data-erp-modal-open' => true]);
        } elseif ($attributes->get('data-turbo') !== 'false') {
            if ($isDownloadLink) {
                $linkAttributes = $linkAttributes->merge(['data-turbo' => 'false']);
            } else {
                $frameAttributes = ['data-turbo-action' => 'advance'];

                if (! $attributes->has('data-turbo-frame')) {
                    $frameAttributes['data-turbo-frame'] = 'erp-main';
                }

                $linkAttributes = $linkAttributes->merge($frameAttributes);
            }
        }
    @endphp
    <a
        href="{{ $href }}"
        @click="$dispatch('erp-row-menu-close')"
        {{ $linkAttributes }}
    >
        {{ $slot }}
    </a>
@else
    <button type="button" {{ $attributes->merge(['class' => "flex w-full items-center gap-2 px-3 py-2 text-left text-sm {$classes}"]) }}>
        {{ $slot }}
    </button>
@endif
