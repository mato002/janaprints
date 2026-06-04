<x-admin-layout :title="__('Command Center')" :breadcrumbs="[]">
    <div class="exec-dashboard space-y-3">
        <header class="flex flex-wrap items-end justify-between gap-2 border-b border-erp-border pb-2">
            <div>
                <h1 class="text-base font-semibold text-erp-primary">{{ __('Executive Command Center') }}</h1>
                <p class="text-[11px] text-slate-500">
                    {{ $dashboard['context']['company'] }} · {{ $dashboard['context']['branch'] }} · {{ $dashboard['context']['role'] }}
                </p>
            </div>
            <p class="text-[10px] uppercase tracking-wide text-slate-400">{{ __('Live operations') }}</p>
        </header>

        @include('admin.dashboard.partials.kpi-strip')

        @include('admin.dashboard.partials.pipeline')

        @include('admin.dashboard.partials.attention-center')

        <div class="grid grid-cols-1 gap-3 xl:grid-cols-12">
            <div class="xl:col-span-8 space-y-3">
                @include('admin.dashboard.partials.today-ops')
                @include('admin.dashboard.partials.branch-performance')
                <div class="grid grid-cols-1 gap-3 lg:grid-cols-2">
                    @include('admin.dashboard.partials.sales-performance')
                    @include('admin.dashboard.partials.production-performance')
                </div>
                <div class="grid grid-cols-1 gap-3 lg:grid-cols-2">
                    @include('admin.dashboard.partials.inventory-health')
                    @include('admin.dashboard.partials.finance-snapshot')
                </div>
                @include('admin.dashboard.partials.top-customers')
            </div>
            <div class="xl:col-span-4 space-y-3">
                @include('admin.dashboard.partials.crm-hr-pulse')
                @include('admin.dashboard.partials.smart-insights')
                @include('admin.dashboard.partials.quick-actions')
                @include('admin.dashboard.partials.activity-feed')
            </div>
        </div>
    </div>
</x-admin-layout>
