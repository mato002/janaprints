@php
    $navItems = collect(config('client_portal.nav', []))
        ->reject(fn (array $item) => ($item['route'] ?? '') === 'client.account.edit');
    $user = auth()->user();
    $initials = collect(explode(' ', $user?->name ?? ''))->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->implode('');
@endphp

<aside id="client-sidebar" class="client-sidebar" data-client-sidebar aria-label="{{ __('Account navigation') }}">
    <div class="client-sidebar__head">
        <a
            href="{{ route('client.dashboard') }}"
            class="client-sidebar__brand"
            data-turbo-frame="client-main"
            data-turbo-action="advance"
        >
            <img
                src="{{ $brandingSidebarLogoUrl }}"
                alt=""
                class="client-sidebar__brand-logo"
                width="36"
                height="36"
                decoding="async"
                aria-hidden="true"
            >
            <span class="client-sidebar__brand-name">{{ config('site.name', config('app.name')) }}</span>
        </a>

        <div class="client-sidebar__profile lg:hidden">
            <span class="client-sidebar__profile-avatar" aria-hidden="true">{{ $initials ?: 'C' }}</span>
            <div class="client-sidebar__profile-text">
                <p class="client-sidebar__profile-name">{{ $user?->name }}</p>
                <p class="client-sidebar__profile-company">{{ $user?->customer?->company_name }}</p>
            </div>
        </div>

        <p class="client-sidebar__label hidden lg:block">{{ __('My account') }}</p>
    </div>

    <nav class="client-sidebar__nav">
        @foreach ($navItems as $item)
            @php
                $activeRoutes = $item['active_routes'] ?? [$item['route'].'*'];
                $active = collect($activeRoutes)->contains(fn (string $pattern) => request()->routeIs($pattern))
                    || (($item['route'] ?? '') === 'client.dashboard' && request()->routeIs('client.dashboard'));
            @endphp
            <a
                href="{{ route($item['route']) }}"
                @class(['client-sidebar__link', 'is-active' => $active])
                data-client-sidebar-link
                data-client-nav-route="{{ $item['route'] }}"
                data-client-nav-active="{{ implode(',', $activeRoutes) }}"
                data-turbo-frame="client-main"
                data-turbo-action="advance"
            >
                <span class="client-sidebar__icon">
                    <x-client.icon :name="$item['icon']" class="h-5 w-5" />
                </span>
                <span>{{ __($item['label']) }}</span>
                @if (($item['route'] ?? '') === 'client.communications.index' && ($clientCommunicationsUnread ?? 0) > 0)
                    <span
                        class="client-sidebar__badge"
                        data-client-comms-unread-badge
                        aria-label="{{ __(':count unread messages', ['count' => $clientCommunicationsUnread]) }}"
                    >{{ $clientCommunicationsUnread > 99 ? '99+' : $clientCommunicationsUnread }}</span>
                @endif
            </a>
        @endforeach
    </nav>

    <div class="client-sidebar__foot lg:hidden">
        <a
            href="{{ route('client.account.edit') }}"
            class="client-sidebar__foot-link"
            data-turbo-frame="client-main"
            data-turbo-action="advance"
        >
            <x-client.icon name="user" class="h-4 w-4" />
            {{ __('Account settings') }}
        </a>
        <form method="POST" action="{{ route('logout') }}" data-turbo-frame="_top">
            @csrf
            <button type="submit" class="client-sidebar__foot-link client-sidebar__foot-link--danger">
                <x-client.icon name="logout" class="h-4 w-4" />
                {{ __('Sign out') }}
            </button>
        </form>
    </div>
</aside>
