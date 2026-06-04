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
