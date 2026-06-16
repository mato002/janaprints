@props([
    'title' => 'Client Portal',
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} — {{ config('site.name', config('app.name')) }}</title>
    <x-site-favicon />
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|plus-jakarta-sans:600,700,800&display=swap" rel="stylesheet">
    @vite(['resources/css/public.css', 'resources/js/public.js'])
</head>
<body class="font-sans antialiased public-page client-portal-page">
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
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <span>{{ __('Menu') }}</span>
                </button>
                <p class="client-mobile-toolbar__title">{{ $heading ?? $title }}</p>
                <x-client.profile-menu compact />
            </div>

            <div class="public-container public-container--wide client-portal-body">
                @if (session('status'))
                    <div class="client-alert client-alert--success" role="status">
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
</body>
</html>
