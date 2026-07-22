@php
    $navByRoute = collect(config('client_portal.nav', []))->keyBy('route');
    $bottomRoutes = array_slice(config('client_portal.bottom_nav_routes', []), 0, 3);
    $bottomLabels = config('client_portal.bottom_nav_labels', []);

    $items = collect($bottomRoutes)
        ->map(function (string $route) use ($navByRoute, $bottomLabels) {
            $nav = $navByRoute->get($route);

            if (! $nav || ! Route::has($route)) {
                return null;
            }

            $activeRoutes = $nav['active_routes'] ?? [$route, $route.'.*'];

            return [
                'label' => __($bottomLabels[$route] ?? $nav['label']),
                'route' => $route,
                'icon' => $nav['icon'] ?? 'home',
                'match' => $activeRoutes,
                'badge' => $route === 'client.communications.index',
            ];
        })
        ->filter()
        ->values()
        ->all();

    $primaryMatchPatterns = collect($items)
        ->flatMap(fn (array $item) => $item['match'])
        ->unique()
        ->values()
        ->all();
@endphp

<nav
    class="client-bottom-nav lg:hidden"
    aria-label="{{ __('Quick navigation') }}"
    data-client-bottom-nav
    data-client-bottom-nav-primary="{{ implode(',', $bottomRoutes) }}"
    data-client-bottom-nav-primary-patterns="{{ implode('|', $primaryMatchPatterns) }}"
    style="--client-bottom-nav-cols: {{ count($items) + 1 }}"
>
    <div class="client-bottom-nav__inner">
        @foreach ($items as $item)
            @php
                $active = collect($item['match'])->contains(fn (string $pattern) => request()->routeIs($pattern));
            @endphp
            <a
                href="{{ route($item['route']) }}"
                @class(['client-bottom-nav__link', 'is-active' => $active])
                data-client-bottom-nav-link
                data-client-nav-route="{{ $item['route'] }}"
                data-client-nav-active="{{ implode(',', $item['match']) }}"
                data-turbo-frame="client-main"
                data-turbo-action="advance"
            >
                <span class="client-bottom-nav__icon-wrap">
                    <x-client.icon :name="$item['icon']" class="h-5 w-5" />
                    @if (($item['badge'] ?? false) && ($clientCommunicationsUnread ?? 0) > 0)
                        <span
                            class="client-bottom-nav__badge"
                            data-client-comms-unread-badge
                        >{{ ($clientCommunicationsUnread ?? 0) > 9 ? '9+' : $clientCommunicationsUnread }}</span>
                    @endif
                </span>
                <span class="client-bottom-nav__label">{{ $item['label'] }}</span>
            </a>
        @endforeach

        @php
            $onPrimaryBottomNav = collect($items)->contains(
                fn (array $item) => collect($item['match'])->contains(fn (string $pattern) => request()->routeIs($pattern))
            );
        @endphp
        <button
            type="button"
            @class(['client-bottom-nav__link', 'is-active' => ! $onPrimaryBottomNav])
            data-client-bottom-nav-more
            data-client-sidebar-toggle
            aria-expanded="false"
            aria-controls="client-sidebar"
        >
            <span class="client-bottom-nav__icon-wrap">
                <x-client.icon name="menu" class="h-5 w-5" />
            </span>
            <span class="client-bottom-nav__label">{{ __('More') }}</span>
        </button>
    </div>
</nav>
