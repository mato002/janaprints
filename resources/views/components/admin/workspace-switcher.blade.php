@props([
    'workspaces' => [],
    'active' => null,
    'ariaLabel' => __('Primary workspaces'),
])

<nav
    {{ $attributes->merge(['class' => 'module-workspace-switcher module-workspace-switcher--primary']) }}
    aria-label="{{ $ariaLabel }}"
>
    <div class="module-workspace-switcher__track" role="tablist">
        @foreach ($workspaces as $workspace)
            @php
                $isActive = ($active['key'] ?? null) === ($workspace['key'] ?? null);
            @endphp
            @if (! empty($workspace['href']))
                <a
                    href="{{ $workspace['href'] }}"
                    data-turbo-frame="erp-main"
                    role="tab"
                    @class([
                        'module-workspace-chip',
                        'module-workspace-chip--active' => $isActive,
                    ])
                    @if ($isActive) aria-selected="true" @endif
                >
                    <x-admin.icon :name="$workspace['icon'] ?? 'home'" class="module-workspace-chip__icon" />
                    <span class="module-workspace-chip__label">{{ $workspace['label'] }}</span>
                    @if (! empty($workspace['badge']))
                        <span class="module-workspace-chip__badge">{{ $workspace['badge'] }}</span>
                    @endif
                </a>
            @else
                <span
                    role="tab"
                    class="module-workspace-chip module-workspace-chip--disabled"
                    title="{{ __('Coming soon') }}"
                >
                    <x-admin.icon :name="$workspace['icon'] ?? 'home'" class="module-workspace-chip__icon" />
                    <span class="module-workspace-chip__label">{{ $workspace['label'] }}</span>
                </span>
            @endif
        @endforeach
    </div>
</nav>
