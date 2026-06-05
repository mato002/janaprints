<x-admin-layout
    :title="__('Work Centers')"
    :breadcrumbs="[
        ['label' => __('Production'), 'url' => route('admin.workspaces.production')],
        ['label' => __('Work Centers')],
    ]"
>
    <x-admin.page-header
        :title="__('Work Centers')"
        :description="__('Operational register of production work centers, queue status, and capacity slots.')"
    />

    <p class="mb-4 text-xs text-slate-500">{{ __('As of') }} {{ $dashboard['as_of'] ?? now()->format('Y-m-d H:i') }}</p>

    <div class="mb-4">
        @include('admin.production.work-centers.command-center.filters')
    </div>

    @include('admin.production.work-centers.command-center.register')
</x-admin-layout>
