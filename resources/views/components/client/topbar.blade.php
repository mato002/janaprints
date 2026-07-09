@props([
    'title' => '',
])

<header id="client-topbar" class="client-topbar">
    <button
        type="button"
        class="client-topbar__menu-btn lg:hidden"
        data-client-sidebar-toggle
        aria-expanded="false"
        aria-controls="client-sidebar"
        aria-label="{{ __('Open menu') }}"
    >
        <x-client.icon name="menu" class="h-5 w-5" />
    </button>

    <div class="client-topbar__title-wrap">
        <p class="client-topbar__eyebrow lg:hidden">{{ __('My account') }}</p>
        <h1 class="client-topbar__title">{{ $title }}</h1>
    </div>

    <div class="client-topbar__actions">
        @if (Route::has('home'))
            <a
                href="{{ route('home') }}"
                target="_blank"
                rel="noopener noreferrer"
                class="client-topbar__website-link hidden sm:inline-flex"
            >
                <x-client.icon name="globe" class="h-4 w-4" />
                {{ __('Website') }}
            </a>
        @endif

        <x-client.profile-menu variant="topbar" />
    </div>
</header>
