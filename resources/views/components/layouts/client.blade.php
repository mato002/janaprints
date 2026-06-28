@props([
    'title' => 'Client Portal',
    'fullMobileChat' => false,
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0f2744">
    @if (auth()->check() && auth()->user()->isClientPortalAccount())
        <meta name="client-communications-unread-url" content="{{ route('client.communications.unread') }}">
    @endif
    <title>{{ $title }} — {{ config('site.name', config('app.name')) }}</title>
    <x-site-favicon />
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|plus-jakarta-sans:600,700,800&display=swap" rel="stylesheet">
    @vite(['resources/css/public.css', 'resources/js/public.js'])
</head>
<body @class([
    'font-sans antialiased public-page client-portal-page',
    'client-portal-page--chat' => $fullMobileChat,
])>
    <x-public.header :transparent="false" portal />

    <div class="client-portal-wrap">
        <div class="client-sidebar-backdrop" data-client-sidebar-backdrop hidden aria-hidden="true"></div>

        <x-client.sidebar />

        <div class="client-portal-main">
            <div class="client-mobile-toolbar lg:hidden">
                <button
                    type="button"
                    class="client-mobile-toolbar__menu"
                    data-client-sidebar-toggle
                    aria-expanded="false"
                    aria-controls="client-sidebar"
                    aria-label="{{ __('Open menu') }}"
                >
                    <x-client.icon name="menu" class="h-5 w-5" />
                </button>
                <div class="client-mobile-toolbar__title-wrap">
                    <p class="client-mobile-toolbar__eyebrow">{{ __('My account') }}</p>
                    <p class="client-mobile-toolbar__title">{{ $heading ?? $title }}</p>
                </div>
                <x-client.profile-menu compact />
            </div>

            <div class="public-container public-container--wide client-portal-body">
                @if (session('status'))
                    <div class="client-alert client-alert--success client-alert--toast" role="status" data-client-toast>
                        <x-client.icon name="sparkles" class="h-5 w-5 shrink-0" />
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="client-alert client-alert--error" role="alert">
                        <ul class="list-disc ps-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <header class="client-page-head hidden lg:block">
                    <div>
                        <p class="client-page-head__eyebrow">{{ __('My account') }}</p>
                        <h1 class="client-page-head__title">{{ $heading ?? $title }}</h1>
                        @isset($subtitle)
                            <p class="client-page-head__subtitle">{{ $subtitle }}</p>
                        @endisset
                    </div>
                </header>

                <main class="client-content">
                    {{ $slot }}
                </main>
            </div>

            <footer class="client-portal-footer">
                <div class="public-container public-container--wide client-portal-footer__inner">
                    <p>&copy; {{ date('Y') }} {{ config('site.name', 'Jana Prints') }}. {{ __('All rights reserved.') }}</p>
                    <a href="{{ route('home') }}" class="client-portal-footer__link">{{ __('Back to website') }}</a>
                </div>
            </footer>
        </div>
    </div>

    <x-client.bottom-nav />
</body>
</html>
