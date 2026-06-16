@extends('layouts.auth-login')

@section('page-title', $portal === 'client' ? __('Set New Client Password') : __('Set New Password'))

@section('content')
    <x-auth.login-card
        :title="__('Choose a new password')"
        :subtitle="__('Enter your email and a new password below to complete the reset.')"
    >
        <form method="POST" action="{{ $portal === 'client' ? route('client.password.store') : route('password.store') }}" class="login-form" novalidate>
            @csrf

            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div class="login-field">
                <label for="email" class="login-field__label">{{ __('Email') }}</label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email', $request->email) }}"
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

            <x-auth.login-password-field
                id="password"
                name="password"
                :label="__('New password')"
                :required="true"
                autocomplete="new-password"
                :placeholder="__('Enter a new password')"
            />

            <x-auth.login-password-field
                id="password_confirmation"
                name="password_confirmation"
                :label="__('Confirm new password')"
                :required="true"
                autocomplete="new-password"
                :placeholder="__('Confirm your new password')"
            />

            <button type="submit" class="login-btn login-btn--primary">{{ __('Reset password') }}</button>

            <p class="login-form__footer">
                <a href="{{ $portal === 'client' ? route('client.login') : route('admin.login') }}" class="login-form__forgot">
                    {{ __('Back to sign in') }}
                </a>
            </p>
        </form>
    </x-auth.login-card>
@endsection
