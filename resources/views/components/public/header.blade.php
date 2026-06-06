@props(['transparent' => true])

<header
    data-public-header
    @class([
        'public-header',
        'public-header--top' => $transparent,
    ])
>
    <div class="public-container public-container--wide">
        <div class="public-header__bar">
            <div class="public-header__brand-col">
                <a href="{{ route('home') }}" class="group flex items-center gap-3" aria-label="Jana Prints home">
                    <span class="flex h-10 w-10 items-center justify-center rounded-brand-lg bg-gradient-to-br from-brand-magenta to-brand-orange text-sm font-bold text-white shadow-brand-sm transition-shadow group-hover:shadow-brand-md" aria-hidden="true">
                        JP
                    </span>
                    <span class="public-header__brand font-display text-lg font-bold tracking-tight transition-colors group-hover:text-brand-cyan">
                        Jana Prints
                    </span>
                </a>
            </div>

            <nav class="public-header__nav items-center gap-6 lg:gap-8" aria-label="Primary">
                <a href="{{ route('storefront.services') }}" class="public-header__link public-nav-link">Services</a>
                <a href="{{ route('storefront.products') }}" class="public-header__link public-nav-link">Products</a>
                <a href="{{ route('storefront.gallery') }}" class="public-header__link public-nav-link">Gallery</a>
                <a href="{{ $aboutSectionHref }}" class="public-header__link public-nav-link">About</a>
                <a href="{{ $contactSectionHref }}" class="public-header__link public-nav-link">Contact</a>
            </nav>

            <div class="public-header__actions">
                @auth
                    <a href="{{ url('/dashboard') }}" class="public-header__link hidden text-sm font-medium sm:inline">
                        Dashboard
                    </a>
                @else
                    @if (Route::has('client.login'))
                        <a href="{{ route('client.login') }}" class="public-header__link hidden text-sm font-medium md:inline">
                            Client Login
                        </a>
                    @endif
                @endauth

                <a href="{{ $quoteFormHref }}" class="public-btn--primary public-btn--sm shadow-brand-glow">
                    Request Quote
                </a>

                <button
                    type="button"
                    class="public-header__menu-btn md:hidden"
                    data-mobile-nav-toggle
                    aria-expanded="false"
                    aria-controls="public-mobile-nav"
                    aria-label="Open menu"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Mobile navigation drawer --}}
    <div class="public-mobile-nav" id="public-mobile-nav" data-mobile-nav hidden>
        <div class="public-mobile-nav__backdrop" data-mobile-nav-close></div>
        <nav class="public-mobile-nav__panel" aria-label="Mobile navigation">
            <div class="public-mobile-nav__header">
                <span class="font-display text-base font-bold text-brand-navy">Menu</span>
                <button type="button" class="public-mobile-nav__close" data-mobile-nav-close aria-label="Close menu">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <ul class="public-mobile-nav__links">
                <li><a href="{{ route('storefront.services') }}">Services</a></li>
                <li><a href="{{ route('storefront.products') }}">Products</a></li>
                <li><a href="{{ route('storefront.gallery') }}">Gallery</a></li>
                <li><a href="{{ $aboutSectionHref }}">About</a></li>
                <li><a href="{{ $contactSectionHref }}">Contact</a></li>
                @guest
                    @if (Route::has('client.login'))
                        <li><a href="{{ route('client.login') }}">Client Login</a></li>
                    @endif
                @endguest
            </ul>
        </nav>
    </div>
</header>
