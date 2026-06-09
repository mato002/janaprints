@props([
    'workspaces' => [],
    'active' => null,
    'ariaLabel' => __('Secondary workspaces'),
])

@if (count($workspaces) > 0)
    <nav
        {{ $attributes->merge(['class' => 'module-workspace-switcher module-workspace-switcher--secondary']) }}
        aria-label="{{ $ariaLabel }}"
    >
        <div class="module-workspace-switcher__track" role="tablist">
            @foreach ($workspaces as $workspace)
                @php
                    $isActive = ($active['key'] ?? null) === ($workspace['key'] ?? null);
                @endphp
                @if (! empty($workspace['coming_soon']) || empty($workspace['href']))
                    <span
                        role="tab"
                        class="module-workspace-tab module-workspace-tab--disabled"
                        title="{{ __('Coming soon') }}"
                    >
                        {{ $workspace['label'] }}
                    </span>
                @else
                    <a
                        href="{{ $workspace['href'] }}"
                        data-turbo-frame="erp-main"
                        role="tab"
                        @class([
                            'module-workspace-tab',
                            'module-workspace-tab--active' => $isActive,
                        ])
                        @if ($isActive) aria-selected="true" @endif
                    >
                        {{ $workspace['label'] }}
                        @if (! empty($workspace['badge']))
                            <span class="module-workspace-tab__badge">{{ $workspace['badge'] }}</span>
                        @endif
                    </a>
                @endif
            @endforeach
        </div>
    </nav>
@endif
