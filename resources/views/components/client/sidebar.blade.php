@php
    $navItems = collect(config('client_portal.nav', []))
        ->reject(fn (array $item) => ($item['route'] ?? '') === 'client.account.edit');
@endphp

<aside id="client-sidebar" class="client-sidebar" data-client-sidebar aria-label="{{ __('Account navigation') }}">
    <div class="client-sidebar__head">
        <p class="client-sidebar__label">{{ __('My account') }}</p>
    </div>

    <nav class="client-sidebar__nav">
        @foreach ($navItems as $item)
            @php($active = request()->routeIs($item['route'].'*') || ($item['route'] === 'client.dashboard' && request()->routeIs('client.dashboard')))
            <a href="{{ route($item['route']) }}" @class(['client-sidebar__link', 'is-active' => $active]) data-client-sidebar-link>
                <span class="client-sidebar__icon">
                    <x-client.icon :name="$item['icon']" class="h-5 w-5" />
                </span>
                <span>{{ __($item['label']) }}</span>
            </a>
        @endforeach
    </nav>
</aside>
