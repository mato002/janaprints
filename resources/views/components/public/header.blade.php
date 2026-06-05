@props(['transparent' => true])

<header
    data-public-header
    @class([
        'public-header',
        'public-header--top' => $transparent,
    ])
>
    <div class="public-container">
        <div class="flex h-16 items-center justify-between lg:h-[4.5rem]">
            <a href="{{ route('home') }}" class="group flex items-center gap-3" aria-label="Jana Prints home">
                <span class="flex h-10 w-10 items-center justify-center rounded-brand-lg bg-gradient-to-br from-brand-magenta to-brand-orange text-sm font-bold text-white shadow-brand-sm transition-shadow group-hover:shadow-brand-md" aria-hidden="true">
                    JP
                </span>
                <span class="public-header__brand font-display text-lg font-bold tracking-tight transition-colors group-hover:text-brand-cyan">
                    Jana Prints
                </span>
            </a>

            <nav class="hidden items-center gap-6 lg:gap-8 md:flex" aria-label="Primary">
                <a href="{{ route('storefront.services') }}" class="public-header__link public-nav-link">Services</a>
                <a href="{{ route('storefront.products') }}" class="public-header__link public-nav-link">Products</a>
                <a href="{{ route('storefront.portfolio') }}" class="public-header__link public-nav-link">Our Work</a>
                <a href="{{ route('storefront.about') }}" class="public-header__link public-nav-link">About</a>
                <a href="{{ route('storefront.contact') }}" class="public-header__link public-nav-link">Contact</a>
            </nav>

            <div class="flex items-center gap-3">
                @auth
                    <a href="{{ url('/dashboard') }}" class="public-header__link hidden text-sm font-medium sm:inline">
                        Dashboard
                    </a>
                @else
                    @if (Route::has('client.login'))
                        <a href="{{ route('client.login') }}" class="public-header__link hidden text-sm font-medium sm:inline">
                            Client Login
                        </a>
                    @endif
                @endauth

                <a href="{{ $quoteFormHref }}" class="public-btn--primary public-btn--sm shadow-brand-glow">
                    Request Quote
                </a>
            </div>
        </div>
    </div>
</header>
