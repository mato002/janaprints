<turbo-frame
    id="erp-main"
    data-turbo-action="advance"
    class="flex min-h-0 flex-1 flex-col overflow-x-hidden overflow-y-auto"
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
        data-compact-page="0"
        data-app-name="{{ config('app.name') }}"
        data-quick-create="{{ json_encode($frameQuickCreate, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) }}"
        data-i18n-create="{{ __('Create') }}"
        data-i18n-soon="{{ __('Soon') }}"
        aria-hidden="true"
    ></span>
    <main class="flex min-h-0 flex-1 flex-col p-4 sm:p-6 lg:p-8">
        @include('admin.partials.breadcrumbs', [
            'breadcrumbs' => [
                ['label' => __('Administration')],
                ['label' => __('Settings'), 'url' => $hubBackUrl],
                ['label' => __('Forms'), 'url' => route('admin.settings.forms.index', $scopeQuery)],
                ['label' => $activeForm['label']],
            ],
        ])

        @if (! empty($statusMessage))
            <div
                class="mb-4 flex items-start gap-3 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-erp-success"
                role="status"
                data-erp-flash-status
            >
                <x-admin.icon name="badge-check" class="h-5 w-5 shrink-0" />
                <span>{{ $statusMessage }}</span>
            </div>
        @endif

        @include('admin.partials.alerts')

        @include('admin.settings.partials.scope-selector', [
            'action' => route('admin.settings.forms.index', ['form' => $activeFormKey] + $scopeQuery),
            'companyId' => $companyId,
            'branchId' => $branchId,
            'companies' => $companies,
            'branches' => $branches,
            'compact' => true,
        ])

        @include('admin.settings.forms.partials.workspace', [
            'form' => $activeForm,
            'canManage' => $canManage,
            'companyId' => $companyId,
            'branchId' => $branchId,
        ])
    </main>
</turbo-frame>
