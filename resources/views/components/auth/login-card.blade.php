@props([
    'title',
    'subtitle' => null,
])

<div class="login-scene" aria-hidden="false">
    <div
        class="login-scene__background login-scene__background--active"
        style="background-image: url('{{ asset('images/login/background.jpg') }}')"
        aria-hidden="true"
    ></div>

    <div class="login-scene__overlay" aria-hidden="true"></div>

    <canvas class="login-scene__particles" data-login-particles aria-hidden="true"></canvas>

    <main class="login-scene__main" aria-label="{{ $title }}">
        <div class="login-card" data-login-card>
            <header class="login-card__header">
                <a href="{{ url('/') }}" class="login-card__brand">
                    <span class="login-card__mark-wrap" aria-hidden="true">
                        <span class="login-card__mark-glow"></span>
                        <img
                            src="{{ $brandingLogoUrl }}"
                            alt=""
                            class="login-card__mark"
                            width="44"
                            height="44"
                            decoding="async"
                        >
                    </span>
                    <span class="login-card__brand-text">
                        <span class="login-card__name">{{ config('site.name', 'Jana Prints') }}</span>
                        <span class="login-card__tagline">{{ __('Print') }} &bull; {{ __('Brand') }} &bull; {{ __('Deliver') }}</span>
                    </span>
                </a>
            </header>

            <div class="login-card__intro">
                <h1 class="login-card__title">{{ $title }}</h1>
                @if ($subtitle)
                    <p class="login-card__subtitle">{{ $subtitle }}</p>
                @endif
            </div>

            @if (session('status'))
                <div class="login-alert" role="status">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="login-alert login-alert--error" role="alert">{{ $errors->first() }}</div>
            @endif

            {{ $slot }}
        </div>
    </main>
</div>
