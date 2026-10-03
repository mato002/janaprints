<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="turbo-cache-control" content="no-preview">
    <title>{{ $title ? $title.' — ' : '' }}{{ config('app.name') }}</title>
    <x-site-favicon :url="$brandingFaviconUrl" />
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @php
        $erpModalFormConfig = [
            'backLabel' => __('Back'),
            'blockedPathFragments' => [
                '/commercial/pos/',
                'counter-sales',
                '/dashboard',
                '/login',
                '/logout',
            ],
        ];
    @endphp
    <script>
        window.__erpRoutes = @json($navRouteUrls ?? []);
        window.__erpModalForm = @json($erpModalFormConfig);
        window.__erpFeatureDiscovery = @json(['searchUrl' => $featureDiscoverySearchUrl ?? '']);
        window.__erpTableExportUrl = @json(route('admin.exports.table'));
    </script>
</head>
<body
    @class([
        'font-sans antialiased bg-erp-page text-erp-primary overflow-hidden',
    ])
    style="--erp-sticky-table-offset: {{ $compactPage ? '6.5rem' : ($compactWorkspace ? '10.5rem' : '12rem') }}"
    x-data="erpShell()"
    @keydown.escape.window="if (paletteOpen) { closePalette(); } else { closeMobileNav(); }"
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
        class="flex h-screen max-h-screen min-w-0 flex-col overflow-hidden transition-[margin-left] duration-sidebar max-lg:ml-0"
        :class="sidebarCollapsed ? 'lg:ml-sidebar-collapsed' : 'lg:ml-sidebar'"
    >
        @include('layouts.admin.partials.topbar')

        <turbo-frame
            id="erp-main"
            data-turbo-action="advance"
            @class([
                'flex min-h-0 flex-1 flex-col',
                'overflow-hidden' => $compactPage,
                'overflow-x-hidden overflow-y-auto' => ! $compactPage,
            ])
        >
            @php
                $frameQuickCreate = array_values(array_map(
                    fn (array $item) => [
                        'label' => $item['label'],
                        'coming_soon' => (bool) ($item['coming_soon'] ?? false),
                        'modal' => (bool) ($item['modal'] ?? false),
                        'href' => empty($item['coming_soon']) && ! empty($item['route']) && Route::has($item['route'])
                            ? route($item['route'], $item['route_params'] ?? [])
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
                data-compact-workspace="{{ $compactWorkspace ? '1' : '0' }}"
                data-app-name="{{ config('app.name') }}"
                data-quick-create="{{ json_encode($frameQuickCreate, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) }}"
                data-i18n-create="{{ __('Create') }}"
                data-i18n-soon="{{ __('Soon') }}"
                aria-hidden="true"
            ></span>
            <main @class([
                'flex min-h-0 min-w-0 flex-1 flex-col',
                'overflow-hidden p-2' => $compactPage,
                'min-h-0 overflow-x-hidden overflow-y-auto p-1.5 sm:p-2' => ! $compactPage && $compactWorkspace,
                'overflow-x-hidden overflow-y-auto p-4 sm:p-6 lg:p-8' => ! $compactPage && ! $compactWorkspace,
            ])>
                @unless ($compactPage || $compactWorkspace)
                    @include('admin.partials.breadcrumbs')
                @endunless
                {{-- Always emit flash markers (including compact pages like Shared Inbox). --}}
                @include('admin.partials.alerts')

                @isset($header)
                    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        {{ $header }}
                    </div>
                @endisset

                <div @class([
                    'workspace-wrapper flex min-h-0 min-w-0 flex-1 flex-col',
                    'overflow-hidden' => $compactPage,
                    'overflow-x-hidden overflow-y-auto' => ! $compactPage,
                ])>
                    {{ $slot }}
                </div>
            </main>
        </turbo-frame>
    </div>

    <div
        id="erp-modal-overlay"
        class="erp-modal-overlay"
        data-erp-modal-overlay
        data-turbo-permanent
        hidden
        aria-hidden="true"
    >
        <div class="erp-modal-overlay__backdrop" data-erp-form-modal-close tabindex="-1" aria-hidden="true"></div>
        <div class="erp-modal-overlay__panel" role="dialog" aria-modal="true" aria-labelledby="erp-form-modal-title">
            <div id="erp-form-modal" class="erp-form-modal-host"></div>
        </div>
    </div>

    <div
        id="erp-lookup-modal-overlay"
        class="erp-lookup-modal-overlay"
        data-erp-lookup-modal-overlay
        data-turbo-permanent
        hidden
        aria-hidden="true"
    >
        <div class="erp-lookup-modal-overlay__backdrop" data-erp-lookup-modal-close tabindex="-1" aria-hidden="true"></div>
        <div class="erp-lookup-modal-overlay__panel" role="dialog" aria-modal="true" aria-labelledby="erp-lookup-modal-title">
            <div id="erp-lookup-modal" class="erp-form-modal-host"></div>
        </div>
    </div>

    <div
        id="erp-drawer-overlay"
        class="erp-drawer-overlay"
        data-erp-drawer-overlay
        data-turbo-permanent
        hidden
        aria-hidden="true"
    >
        <div class="erp-drawer-overlay__backdrop" data-erp-drawer-close tabindex="-1" aria-hidden="true"></div>
        <turbo-frame id="erp-preview-drawer"></turbo-frame>
    </div>

    <div id="erp-toast-host" class="erp-toast-host" data-turbo-permanent aria-live="polite"></div>

    <x-admin.command-palette />
    <script>
        if (! window.__erpSettingsSaveBound) {
        window.__erpSettingsSaveBound = true;
        document.addEventListener('click', function (event) {
            const btn = event.target.closest('[data-erp-settings-save]');
            if (! btn) {
                return;
            }

            event.preventDefault();

            const form = document.getElementById('settings-save-form');
            const say = function (message, variant) {
                if (window.showErpSweetAlert) {
                    window.showErpSweetAlert(message, variant);
                } else {
                    window.alert(message);
                }
            };

            if (! form) {
                say(btn.getAttribute('data-missing-form') || 'Save form is missing. Refresh the page.', 'error');
                return;
            }

            const label = btn.textContent;
            btn.disabled = true;
            btn.textContent = btn.getAttribute('data-saving-label') || 'Saving…';

            fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            }).then(function (res) {
                return res.json().catch(function () { return {}; }).then(function (data) {
                    return { res: res, data: data };
                });
            }).then(function (payload) {
                const res = payload.res;
                const data = payload.data || {};
                if (! res.ok) {
                    var errorMsg = data.message;
                    if (! errorMsg && data.errors) {
                        errorMsg = Object.values(data.errors).flat().join(' ');
                    }
                    say(errorMsg || btn.getAttribute('data-error-label') || 'Unable to save settings. Please try again.', 'error');
                    return;
                }

                say(data.message || btn.getAttribute('data-success-label') || 'Settings saved.', 'success');
                const next = data.redirect || form.getAttribute('data-settings-show-url');
                const frame = document.getElementById('module-workspace-content');
                if (frame && window.Turbo && next) {
                    window.Turbo.visit(next, { frame: 'module-workspace-content' });
                } else if (next) {
                    window.location.assign(next);
                } else {
                    window.location.reload();
                }
            }).catch(function () {
                say(btn.getAttribute('data-error-label') || 'Unable to save settings. Please try again.', 'error');
            }).finally(function () {
                btn.disabled = false;
                btn.textContent = label;
            });
        });
        }
    </script>
</body>
</html>
