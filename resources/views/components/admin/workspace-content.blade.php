@props([
    'url' => null,
    'frameId' => 'module-workspace-content',
])

<div {{ $attributes->merge(['class' => 'module-workspace-content w-full min-w-0']) }}>
    @if ($url)
        <turbo-frame
            id="{{ $frameId }}"
            src="{{ $url }}"
            class="module-workspace-content__frame"
            data-turbo-action="advance"
            data-turbo-cache="false"
            loading="lazy"
        >
            <div class="module-workspace-content__loading" aria-live="polite">
                <div class="erp-skeleton h-8 w-48 mb-4"></div>
                <div class="erp-skeleton h-32 w-full"></div>
            </div>
        </turbo-frame>
    @else
        {{ $slot }}
    @endif
</div>
