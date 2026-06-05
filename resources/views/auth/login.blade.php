@extends('layouts.auth-login')

@section('content')
    <div class="login-scene" aria-hidden="false">
        <div
            class="login-scene__background login-scene__background--active"
            style="background-image: url('{{ asset('images/login/background.jpg') }}')"
            aria-hidden="true"
        ></div>

        <div class="login-scene__overlay" aria-hidden="true"></div>

        <canvas class="login-scene__particles" data-login-particles aria-hidden="true"></canvas>

        <main class="login-scene__main" aria-label="{{ __('Sign in to Jana Prints') }}">
            <div class="login-card" data-login-card>
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
                </header>

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

                    <button type="submit" class="login-btn login-btn--primary">Sign In</button>
                </form>
            </div>
        </main>
    </div>
@endsection
