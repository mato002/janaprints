@extends('layouts.auth-login')

@section('page-title', $portal === 'client' ? __('Reset Client Password') : __('Reset Password'))

@section('content')
    <x-auth.login-card
        :title="__('Forgot your password?')"
        :subtitle="__('Enter your email and we will send you a secure link to choose a new password.')"
    >
        <form method="POST" action="{{ $portal === 'client' ? route('client.password.email') : route('password.email') }}" class="login-form" novalidate>
            @csrf

            <div class="login-field">
                <label for="email" class="login-field__label">{{ __('Email') }}</label>
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

            <button type="submit" class="login-btn login-btn--primary">{{ __('Email reset link') }}</button>

            @if ($portal !== 'client')
                <p class="login-form__footer">
                    {{ __('Customer portal user?') }}
                    <a href="{{ route('client.password.request') }}" class="login-form__forgot">{{ __('Reset client portal password') }}</a>
                </p>
            @endif

            <p class="login-form__footer">
                <a href="{{ $portal === 'client' ? route('client.login') : route('admin.login') }}" class="login-form__forgot">
                    {{ __('Back to sign in') }}
                </a>
            </p>
        </form>
    </x-auth.login-card>
@endsection
