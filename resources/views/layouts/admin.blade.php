<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="turbo-cache-control" content="no-preview">
    <title>{{ $title ? $title.' — ' : '' }}{{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body
    class="font-sans antialiased bg-erp-page text-erp-primary"
    x-data="erpShell()"
    @keydown.escape.window="closeMobileNav()"
    @close-nav.window="closeMobileNav()"
>
    <div class="turbo-progress" id="turbo-progress" aria-hidden="true"></div>

    <div
        x-show="mobileNavOpen"
        x-transition:enter="transition-opacity ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-40 bg-erp-primary/60 lg:hidden"
        @click="closeMobileNav()"
        x-cloak
    ></div>

    @include('layouts.admin.partials.sidebar')

    <div
        class="flex min-h-screen min-w-0 flex-col transition-[margin-left] duration-sidebar max-lg:ml-0"
        :class="sidebarCollapsed ? 'lg:ml-sidebar-collapsed' : 'lg:ml-sidebar'"
    >
        @include('layouts.admin.partials.topbar')

        <turbo-frame id="erp-main" data-turbo-action="advance" class="flex flex-1 flex-col">
            <span
                id="erp-route-meta"
                class="sr-only"
                data-route="{{ Route::currentRouteName() }}"
                data-title="{{ $title }}"
                data-app-name="{{ config('app.name') }}"
                aria-hidden="true"
            ></span>
            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                @include('admin.partials.breadcrumbs')
                @include('admin.partials.alerts')

                @isset($header)
                    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        {{ $header }}
                    </div>
                @endisset

                {{ $slot }}
            </main>
        </turbo-frame>
    </div>
</body>
</html>
