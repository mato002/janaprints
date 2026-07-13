@props([
    'url' => null,
    'frameId' => 'module-workspace-content',
])

<div {{ $attributes->merge(['class' => 'module-workspace-content flex min-h-0 w-full min-w-0 flex-1 flex-col overflow-hidden']) }}>
    @if ($url)
        <turbo-frame
            id="{{ $frameId }}"
            src="{{ $url }}"
            class="module-workspace-content__frame flex min-h-0 flex-1 flex-col overflow-hidden"
            data-turbo-action="replace"
            data-turbo-cache="false"
        >
            <div class="module-workspace-content__loading" aria-live="polite">
                <div class="erp-skeleton module-workspace-content__skeleton-bar"></div>
                <div class="erp-skeleton module-workspace-content__skeleton-panel"></div>
            </div>
        </turbo-frame>
    @else
        {{ $slot }}
    @endif
</div>
