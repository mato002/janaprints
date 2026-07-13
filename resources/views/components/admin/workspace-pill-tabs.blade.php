@props([
    'workspaces' => [],
    'active' => null,
    'variant' => 'primary',
    'ariaLabel' => __('Workspaces'),
    'turboFrame' => null,
])

@php
    $turboFrame = $turboFrame ?? ($variant === 'secondary' ? 'module-workspace-content' : 'erp-main');
@endphp

@if (count($workspaces) > 0)
    <nav
        {{ $attributes->merge([
            'class' => 'workspace-pill-tabs module-workspace-switcher module-workspace-switcher--' . $variant,
        ]) }}
        aria-label="{{ $ariaLabel }}"
    >
        <div class="workspace-pill-tabs__track module-workspace-switcher__track" role="tablist">
            @foreach ($workspaces as $workspace)
                @php
                    $isActive = ($active['key'] ?? null) === ($workspace['key'] ?? null);
                    $searchLabel = strtolower(implode(' ', array_filter([
                        $workspace['label'] ?? '',
                        $workspace['description'] ?? '',
                        $workspace['key'] ?? '',
                    ])));
                    $isDisabled = ! empty($workspace['coming_soon']) || empty($workspace['href']);
                @endphp

                @if ($isDisabled)
                    <span
                        role="tab"
                        data-workspace-tab
                        data-search-label="{{ $searchLabel }}"
                        @class([
                            'workspace-pill',
                            'workspace-pill--' . $variant,
                            'workspace-pill--disabled',
                        ])
                        title="{{ __('Coming soon') }}"
                    >
                        <span class="workspace-pill__label">{{ $workspace['label'] }}</span>
                    </span>
                @else
                    <a
                        href="{{ $workspace['href'] }}"
                        @if (! empty($workspace['content_href']))
                            data-workspace-content-href="{{ $workspace['content_href'] }}"
                        @endif
                        data-turbo-frame="{{ $workspace['turbo_frame'] ?? $turboFrame }}"
                        data-turbo-action="advance"
                        data-workspace-tab
                        data-workspace-tab-key="{{ $workspace['key'] ?? '' }}"
                        data-search-label="{{ $searchLabel }}"
                        role="tab"
                        @class([
                            'workspace-pill',
                            'workspace-pill--' . $variant,
                            'workspace-pill--active' => $isActive,
                        ])
                        @if ($isActive) aria-selected="true" @endif
                    >
                        <span class="workspace-pill__label">{{ $workspace['label'] }}</span>
                        @if (! empty($workspace['badge']))
                            <span class="workspace-pill__badge">{{ $workspace['badge'] }}</span>
                        @endif
                    </a>
                @endif
            @endforeach
        </div>
    </nav>
@endif
