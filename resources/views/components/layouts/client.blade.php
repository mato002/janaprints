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
    <meta name="turbo-cache-control" content="no-preview">
    <meta name="theme-color" content="#1F237A">
    @if (auth()->check() && auth()->user()->isClientPortalAccount())
        <meta name="client-communications-unread-url" content="{{ route('client.communications.unread') }}">
    @endif
    <title>{{ $title }} — {{ config('site.name', config('app.name')) }}</title>
    <x-site-favicon :url="$brandingFaviconUrl ?? null" />
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">
    @vite(['resources/css/public.css', 'resources/js/public.js'])
</head>
<body @class([
    'font-sans antialiased client-portal-page',
    'client-portal-page--chat' => $fullMobileChat,
])>
    <div class="client-turbo-progress" id="client-turbo-progress" aria-hidden="true"></div>

    <div class="client-portal-wrap">
        <div class="client-sidebar-backdrop" data-client-sidebar-backdrop hidden aria-hidden="true"></div>

        <x-client.sidebar />

        <div class="client-portal-main">
            <x-client.topbar :title="$heading ?? $title" />

            <turbo-frame
                id="client-main"
                data-turbo-action="advance"
                @class([
                    'client-portal-frame',
                    'client-portal-frame--chat' => $fullMobileChat,
                ])
            >
                <span
                    id="client-route-meta"
                    class="sr-only"
                    data-route="{{ Route::currentRouteName() }}"
                    data-title="{{ $title }}"
                    data-heading="{{ $heading ?? $title }}"
                    data-full-mobile-chat="{{ $fullMobileChat ? '1' : '0' }}"
                    data-app-name="{{ config('site.name', config('app.name')) }}"
                    aria-hidden="true"
                ></span>

                <div class="client-portal-body">
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
                            <h2 class="client-page-head__title">{{ $heading ?? $title }}</h2>
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
                    <div class="client-portal-footer__inner">
                        <p>&copy; {{ date('Y') }} {{ config('site.name', 'Jana Prints') }}. {{ __('All rights reserved.') }}</p>
                        <a href="{{ route('home') }}" class="client-portal-footer__link" data-turbo-frame="_top">{{ __('Back to website') }}</a>
                    </div>
                </footer>
            </turbo-frame>
        </div>
    </div>

    <x-client.bottom-nav />
</body>
</html>
