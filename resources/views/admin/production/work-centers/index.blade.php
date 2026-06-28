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
    >
        <x-slot name="actions">
            @can('create', App\Models\Production\WorkCenter::class)
                <a href="{{ route('admin.production.work-centers.create') }}" class="erp-btn-primary" data-turbo-frame="erp-form-modal">{{ __('Add work center') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    <p class="mb-4 text-xs text-slate-500">{{ __('As of') }} {{ $dashboard['as_of'] ?? now()->format('Y-m-d H:i') }}</p>

    <div class="mb-4">
        @include('admin.production.work-centers.command-center.filters')
    </div>

    @include('admin.production.work-centers.command-center.register')
</x-admin-layout>
