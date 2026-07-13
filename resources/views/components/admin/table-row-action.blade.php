@props([
    'href' => null,
    'method' => null,
    'action' => null,
    'variant' => 'default',
    'confirm' => null,
])

@php
    use App\Support\Navigation\WorkspaceEmbed;
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
        $resolvedHref = $href;
        $linkAttributes = $attributes->merge([
            'class' => "flex w-full items-center gap-2 px-3 py-2 text-sm {$classes}",
        ]);

        $isDownloadLink = is_string($href) && (str_contains($href, '/download') || str_ends_with($href, '.pdf'));

        if ($isModalOpen) {
            $resolvedHref = WorkspaceEmbed::mainUrl($href) ?? $href;
            $linkAttributes = $linkAttributes->merge(['data-erp-modal-open' => true]);
        } elseif ($attributes->get('data-turbo') !== 'false') {
            if ($isDownloadLink) {
                $linkAttributes = $linkAttributes->merge(['data-turbo' => 'false']);
            } else {
                // Default: entity hops (View / Edit) load into erp-main and leave the
                // nested workspace frame. Callers may override data-turbo-frame.
                $frameAttributes = [
                    'data-turbo-frame' => 'erp-main',
                    'data-turbo-action' => 'advance',
                ];
                $resolvedHref = WorkspaceEmbed::mainUrl($href) ?? $href;

                if ($attributes->has('data-turbo-frame')) {
                    unset($frameAttributes['data-turbo-frame']);
                    $resolvedHref = WorkspaceEmbed::url($href) ?? $href;
                }

                $linkAttributes = $linkAttributes->merge($frameAttributes);
            }
        }
    @endphp
    <a
        href="{{ $resolvedHref }}"
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
