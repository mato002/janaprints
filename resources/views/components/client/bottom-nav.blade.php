@php
    $items = [
        ['label' => __('Home'), 'route' => 'client.dashboard', 'icon' => 'home', 'match' => ['client.dashboard']],
        ['label' => __('Quotes'), 'route' => 'client.quotations.index', 'icon' => 'document', 'match' => ['client.quotations.*']],
        ['label' => __('Orders'), 'route' => 'client.orders.index', 'icon' => 'clipboard', 'match' => ['client.orders.*']],
        ['label' => __('Messages'), 'route' => 'client.communications.index', 'icon' => 'chat', 'match' => ['client.communications.*'], 'badge' => true],
    ];
@endphp

<nav class="client-bottom-nav lg:hidden" aria-label="{{ __('Quick navigation') }}">
    <div class="client-bottom-nav__inner">
        @foreach ($items as $item)
            @php
                $active = collect($item['match'])->contains(fn (string $pattern) => request()->routeIs($pattern));
            @endphp
            <a
                href="{{ route($item['route']) }}"
                @class(['client-bottom-nav__link', 'is-active' => $active])
                data-client-bottom-nav-link
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

        <button
            type="button"
            class="client-bottom-nav__link"
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
