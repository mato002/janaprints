<x-admin-layout
    :title="__('Work Centers')"
    :breadcrumbs="[
        ['label' => __('Production'), 'url' => route('admin.production.dashboard')],
        ['label' => __('Work Centers')],
    ]"
>
    <x-admin.page-header
        :title="__('Production Capacity & Work Centers')"
        :description="__('Manage production capacity, stage flow, workload, and bottlenecks across work centers.')"
    />

    <p class="mb-4 text-xs text-slate-500">{{ __('As of') }} {{ $dashboard['as_of'] ?? now()->format('Y-m-d H:i') }}</p>

    <div class="mb-4">
        @include('admin.production.work-centers.command-center.filters')
    </div>

    <div class="mb-4">
        @include('admin.production.work-centers.command-center.kpi-strip')
    </div>

    <div class="mb-4">
        @include('admin.production.work-centers.command-center.stage-map')
    </div>

    <div class="mb-4 grid grid-cols-1 gap-4 xl:grid-cols-12">
        <div class="xl:col-span-7">
            @include('admin.production.work-centers.command-center.register')
        </div>
        <div class="xl:col-span-5">
            @include('admin.production.work-centers.command-center.workload')
        </div>
    </div>

    <div class="mt-4">
        @include('admin.production.work-centers.command-center.bottlenecks')
    </div>
</x-admin-layout>
