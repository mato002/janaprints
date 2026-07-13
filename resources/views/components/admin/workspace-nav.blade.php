@props([
    'links' => [],
    'ariaLabel' => __('Navigation'),
    'hideInWorkspace' => true,
    'variant' => 'pill',
])

@php
    use App\Support\Navigation\WorkspaceEmbed;

    if ($hideInWorkspace && WorkspaceEmbed::rendersEmbeddedFragment()) {
        return;
    }

    $turboFrame = WorkspaceEmbed::turboFrame();
@endphp

<nav
    {{ $attributes->merge(['class' => $variant === 'pill' ? 'erp-card mb-4 flex flex-wrap gap-2 p-2' : 'mb-4 flex flex-wrap gap-2']) }}
    aria-label="{{ $ariaLabel }}"
>
    @foreach ($links as $link)
        @if (! empty($link['permission']) && ! auth()->user()?->can($link['permission']))
            @continue
        @endif

        @php
            $href = isset($link['route'])
                ? route($link['route'], $link['params'] ?? $link['query'] ?? [])
                : ($link['href'] ?? '#');
            $href = WorkspaceEmbed::url($href) ?? $href;
            $routePattern = $link['route_pattern'] ?? ($link['route'] ?? null);
            $isActive = $link['active'] ?? (
                $routePattern
                    ? request()->routeIs(is_string($routePattern) ? $routePattern.'*' : $routePattern)
                    : false
            );
            $pillClass = $variant === 'pill'
                ? 'rounded-lg px-3 py-1.5 text-sm font-medium transition-colors'
                : 'rounded-md px-3 py-1.5 text-xs font-medium';
        @endphp

        <a
            href="{{ $href }}"
            data-turbo-frame="{{ $turboFrame }}"
            data-turbo-action="advance"
            @class([
                $pillClass,
                'bg-erp-accent text-white' => $isActive && $variant === 'pill',
                'bg-slate-900 text-white' => $isActive && $variant === 'compact',
                'text-slate-600 hover:bg-slate-50' => ! $isActive && $variant === 'pill',
                'bg-slate-100 text-slate-700 hover:bg-slate-200' => ! $isActive && $variant === 'compact',
            ])
        >
            {{ $link['label'] }}
        </a>
    @endforeach

    {{ $slot }}
</nav>
