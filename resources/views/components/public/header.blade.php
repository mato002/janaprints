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
            <a href="/" class="group flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-brand-lg bg-gradient-to-br from-brand-magenta to-brand-orange text-sm font-bold text-white shadow-brand-sm transition-shadow group-hover:shadow-brand-md">
                    JP
                </span>
                <span class="public-header__brand font-display text-lg font-bold tracking-tight transition-colors group-hover:text-brand-cyan">
                    Jana Prints
                </span>
            </a>

            <nav class="hidden items-center gap-6 lg:gap-8 md:flex">
                <a href="#services" class="public-header__link public-nav-link">Services</a>
                <a href="#portfolio" class="public-header__link public-nav-link">Our Work</a>
                <a href="#workflow" class="public-header__link public-nav-link">Process</a>
                <a href="#facility" class="public-header__link public-nav-link">Facility</a>
                <a href="#why-us" class="public-header__link public-nav-link">Why Us</a>
                <a href="#testimonials" class="public-header__link public-nav-link">Reviews</a>
                <a href="#contact" class="public-header__link public-nav-link">Contact</a>
            </nav>

            <div class="flex items-center gap-3">
                @auth
                    <a href="{{ url('/dashboard') }}" class="public-header__link hidden text-sm font-medium sm:inline">
                        Dashboard
                    </a>
                @else
                    @if (Route::has('login'))
                        <a href="{{ route('login') }}" class="public-header__link hidden text-sm font-medium sm:inline">
                            Log in
                        </a>
                    @endif
                @endauth

                <a href="#contact" class="public-btn--primary public-btn--sm shadow-brand-glow">
                    Request Quote
                </a>
            </div>
        </div>
    </div>
</header>
