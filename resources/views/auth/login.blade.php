@extends('layouts.auth-login')

@php
    $backgrounds = [
        ['src' => asset('images/login/printing-press.svg'), 'label' => __('Commercial printing press')],
        ['src' => asset('images/login/packaging.svg'), 'label' => __('Packaging production')],
        ['src' => asset('images/login/stationery.svg'), 'label' => __('Corporate stationery')],
        ['src' => asset('images/login/vehicle-branding.svg'), 'label' => __('Vehicle branding')],
        ['src' => asset('images/login/large-format.svg'), 'label' => __('Large format printing')],
    ];

    $products = [
        __('Business Cards'),
        __('Brochures'),
        __('Packaging'),
        __('Banners'),
        __('Branding'),
        __('Vehicle Wraps'),
    ];

    $metrics = [
        ['value' => '10,000+', 'label' => __('Jobs Delivered')],
        ['value' => '500+', 'label' => __('Businesses Served')],
        ['value' => '99%', 'label' => __('On-Time Delivery')],
    ];

    $floats = [
        ['src' => asset('images/login/stationery.svg'), 'class' => 'login-float--cards'],
        ['src' => asset('images/login/packaging.svg'), 'class' => 'login-float--packaging'],
        ['src' => asset('images/login/large-format.svg'), 'class' => 'login-float--banners'],
        ['src' => asset('images/login/vehicle-branding.svg'), 'class' => 'login-float--branding'],
        ['src' => asset('images/login/printing-press.svg'), 'class' => 'login-float--press'],
    ];

    $features = [
        __('Fast Turnaround'),
        __('Artwork Approval Workflow'),
        __('Real-Time Production Tracking'),
        __('Nationwide Delivery'),
    ];
@endphp

@section('content')
    <div class="login-scene" aria-hidden="false">
        <div class="login-scene__backgrounds" data-login-backgrounds aria-hidden="true">
            @foreach ($backgrounds as $index => $bg)
                <div
                    class="login-scene__slide @if ($index === 0) login-scene__slide--active @endif"
                    data-login-bg-slide
                    style="background-image: url('{{ $bg['src'] }}')"
                    @if ($index === 0) aria-hidden="false" @else aria-hidden="true" @endif
                ></div>
            @endforeach
        </div>

        <div class="login-scene__overlay" aria-hidden="true"></div>

        <div class="login-scene__floats" aria-hidden="true">
            @foreach ($floats as $float)
                <div
                    class="login-float {{ $float['class'] }}"
                    style="background-image: url('{{ $float['src'] }}')"
                ></div>
            @endforeach
        </div>

        <canvas class="login-scene__particles" data-login-particles aria-hidden="true"></canvas>

        <main class="login-scene__main" aria-label="{{ __('Sign in to Jana Prints') }}">
            <div class="login-card login-card--centered" data-login-card>
                <header class="login-card__header">
                    <a href="{{ url('/') }}" class="login-card__brand">
                        <span class="login-card__mark-wrap" aria-hidden="true">
                            <span class="login-card__mark-glow"></span>
                            <span class="login-card__mark">JP</span>
                        </span>
                        <span class="login-card__brand-text">
                            <span class="login-card__name">{{ config('site.name', 'Jana Prints') }}</span>
                            <span class="login-card__tagline">{{ __('Print') }} &bull; {{ __('Brand') }} &bull; {{ __('Deliver') }}</span>
                        </span>
                    </a>
                    <p class="login-card__trust">{{ __('Trusted by businesses across Kenya') }}</p>
                </header>

                <div class="login-chips" aria-label="{{ __('Print services') }}">
                    @foreach ($products as $product)
                        <span class="login-chips__item">{{ $product }}</span>
                    @endforeach
                </div>

                <div class="login-metrics" aria-label="{{ __('Trust metrics') }}">
                    @foreach ($metrics as $metric)
                        <div class="login-metrics__item">
                            <span class="login-metrics__value">{{ $metric['value'] }}</span>
                            <span class="login-metrics__label">{{ $metric['label'] }}</span>
                        </div>
                    @endforeach
                </div>

                <ul class="login-features" aria-label="{{ __('Platform features') }}">
                    @foreach ($features as $feature)
                        <li class="login-features__item">
                            <span class="login-features__icon" aria-hidden="true">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                            </span>
                            {{ $feature }}
                        </li>
                    @endforeach
                </ul>

                @if (session('status'))
                    <div class="login-alert" role="status">{{ session('status') }}</div>
                @endif

                @if ($errors->any())
                    <div class="login-alert login-alert--error" role="alert">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ route('admin.login') }}" class="login-form" novalidate>
                    @csrf

                    <div class="login-field">
                        <label for="email" class="login-field__label">Email</label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            autocomplete="username"
                            placeholder="you@company.com"
                            class="login-field__input @error('email') login-field__input--error @enderror"
                        >
                        @error('email')
                            <p class="login-field__error" id="email-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="login-field">
                        <label for="password" class="login-field__label">Password</label>
                        <div class="login-field__input-wrap">
                            <input
                                id="password"
                                type="password"
                                name="password"
                                required
                                autocomplete="current-password"
                                placeholder="Enter your password"
                                class="login-field__input pr-12 @error('password') login-field__input--error @enderror"
                                data-login-password-input
                                @error('password') aria-describedby="password-error" @enderror
                            >
                            <button
                                type="button"
                                class="login-field__toggle"
                                data-login-password-toggle
                                aria-label="Show password"
                                aria-pressed="false"
                            >
                                <span data-login-password-show aria-hidden="true">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1 1 0 010-.644C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </span>
                                <span data-login-password-hide hidden aria-hidden="true">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/>
                                    </svg>
                                </span>
                            </button>
                        </div>
                        @error('password')
                            <p class="login-field__error" id="password-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="login-form__meta">
                        <div class="login-remember">
                            <input
                                id="remember_me"
                                type="checkbox"
                                name="remember"
                                class="login-remember__checkbox"
                                @checked(old('remember'))
                            >
                            <label for="remember_me" class="login-remember__label">Remember Me</label>
                        </div>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="login-form__forgot">Forgot Password</a>
                        @endif
                    </div>

                    <div class="login-actions">
                        <button type="submit" class="login-btn login-btn--primary">Sign In</button>
                        <a href="{{ route('client.login') }}" class="login-btn login-btn--secondary">Customer Portal</a>
                    </div>
                </form>
            </div>
        </main>

        <footer class="login-footer" aria-label="{{ __('Company information') }}">
            <p class="login-footer__copy">&copy; {{ date('Y') }} {{ config('site.name', 'Jana Prints') }}</p>
            <p class="login-footer__tagline">{{ __('Commercial Printing') }} &bull; {{ __('Branding') }} &bull; {{ __('Packaging') }}</p>
        </footer>
    </div>
@endsection
