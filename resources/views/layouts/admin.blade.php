<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="turbo-cache-control" content="no-preview">
    <title>{{ $title ? $title.' — ' : '' }}{{ config('app.name') }}</title>
    @if (! empty($brandingFaviconUrl))
        <link rel="icon" href="{{ $brandingFaviconUrl }}" type="image/png">
    @endif
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>window.__erpRoutes = @json($navRouteUrls ?? []);</script>
</head>
<body
    @class([
        'font-sans antialiased bg-erp-page text-erp-primary',
        'overflow-hidden' => $compactPage,
    ])
    x-data="erpShell(@js($navSearchIndex ?? []))"
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
        id="erp-app-shell"
        @class([
            'flex min-w-0 flex-col transition-[margin-left] duration-sidebar max-lg:ml-0',
            'h-screen max-h-screen overflow-hidden' => $compactPage,
            'min-h-screen' => ! $compactPage,
        ])
        :class="sidebarCollapsed ? 'lg:ml-sidebar-collapsed' : 'lg:ml-sidebar'"
    >
        @include('layouts.admin.partials.topbar')

        <turbo-frame
            id="erp-main"
            data-turbo-action="advance"
            @class([
                'flex min-h-0 flex-1 flex-col',
                'overflow-hidden' => $compactPage,
            ])
        >
            @php
                $frameQuickCreate = array_values(array_map(
                    fn (array $item) => [
                        'label' => $item['label'],
                        'coming_soon' => (bool) ($item['coming_soon'] ?? false),
                        'href' => empty($item['coming_soon']) && ! empty($item['route']) && Route::has($item['route'])
                            ? route($item['route'])
                            : null,
                    ],
                    array_filter(
                        app(\App\Support\Navigation\WorkspacePresenter::class)->quickCreateForRoute(Route::currentRouteName()),
                        fn (array $item) => $item['visible'] ?? true,
                    ),
                ));
            @endphp
            <span
                id="erp-route-meta"
                class="sr-only"
                data-route="{{ Route::currentRouteName() }}"
                data-title="{{ $title }}"
                data-compact-page="{{ $compactPage ? '1' : '0' }}"
                data-app-name="{{ config('app.name') }}"
                data-quick-create="{{ json_encode($frameQuickCreate, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) }}"
                data-i18n-create="{{ __('Create') }}"
                data-i18n-soon="{{ __('Soon') }}"
                aria-hidden="true"
            ></span>
            <main @class([
                'flex min-h-0 flex-1 flex-col',
                'overflow-hidden p-2' => $compactPage,
                'p-4 sm:p-6 lg:p-8' => ! $compactPage,
            ])>
                @unless ($compactPage)
                    @include('admin.partials.breadcrumbs')
                @endunless
                @if (! $compactPage)
                    @include('admin.partials.alerts')
                @endif

                @isset($header)
                    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        {{ $header }}
                    </div>
                @endisset

                <div @class([
                    'flex min-h-0 flex-1 flex-col',
                    'overflow-hidden' => $compactPage,
                ])>
                    {{ $slot }}
                </div>
            </main>
        </turbo-frame>
    </div>
</body>
</html>
