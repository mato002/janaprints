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
                <a href="{{ route('home') }}" class="group flex items-center" aria-label="Jana Prints home">
                    <x-public.brand-logo full header class="transition-opacity group-hover:opacity-90" />
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
                    <a href="{{ url('/dashboard') }}" class="public-header__link hidden text-sm font-medium md:inline">
                        Dashboard
                    </a>
                @else
                    @if (Route::has('client.login'))
                        <a href="{{ route('client.login') }}" class="public-header__link hidden text-sm font-medium md:inline">
                            Client Login
                        </a>
                    @endif
                @endauth

                <a href="{{ $quoteFormHref }}" class="public-header__quote-btn public-btn--primary public-btn--sm hidden shadow-brand-glow md:inline-flex">
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
                    <svg class="public-header__menu-icon public-header__menu-icon--open h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <svg class="public-header__menu-icon public-header__menu-icon--close h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Full-width mobile navigation panel (below header) --}}
    <div class="public-mobile-nav" id="public-mobile-nav" data-mobile-nav hidden>
        <nav class="public-mobile-nav__panel" aria-label="Mobile navigation">
            <ul class="public-mobile-nav__links">
                <li><a href="{{ route('home') }}">Home</a></li>
                <li><a href="{{ route('storefront.services') }}">Services</a></li>
                <li><a href="{{ route('storefront.products') }}">Products</a></li>
                <li><a href="{{ route('storefront.gallery') }}">Gallery</a></li>
                <li><a href="{{ $aboutSectionHref }}">About</a></li>
                <li><a href="{{ $contactSectionHref }}">Contact</a></li>
                @auth
                    <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                @else
                    @if (Route::has('client.login'))
                        <li><a href="{{ route('client.login') }}">Client Login</a></li>
                    @endif
                @endauth
            </ul>

            <div class="public-mobile-nav__cta-wrap">
                <a href="{{ $quoteFormHref }}" class="public-mobile-nav__cta">
                    Request Quote
                </a>
            </div>
        </nav>
    </div>
</header>
