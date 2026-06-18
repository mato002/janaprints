<x-admin-layout :title="__('Command Center')" :breadcrumbs="[]">
    <div class="exec-dashboard exec-dashboard--v2 exec-dashboard--density-pilot">
        <header class="exec-dashboard__header">
            <div>
                <h1 class="exec-dashboard__title">{{ __('Executive Command Center') }}</h1>
                <p class="exec-dashboard__context">
                    {{ $dashboard['context']['company'] }} · {{ $dashboard['context']['branch'] }} · {{ $dashboard['context']['role'] }}
                </p>
            </div>
            <span class="exec-live-badge">
                <span class="exec-live-badge__dot" aria-hidden="true"></span>
                {{ __('Live operations') }}
            </span>
        </header>

        @include('admin.dashboard.partials.hero')
        @include('admin.dashboard.partials.quote-requests-alert')
        @include('admin.dashboard.partials.health-strip')
        @include('admin.dashboard.partials.communication-health')
        @include('admin.dashboard.partials.integration-health')
        @include('admin.dashboard.partials.pipeline')

        <div class="exec-dashboard__main grid grid-cols-1 gap-2 md:gap-3 xl:grid-cols-12">
            <div class="exec-dashboard__primary space-y-2 md:space-y-3 xl:col-span-8">
                @include('admin.dashboard.partials.attention-center')
                @include('admin.dashboard.partials.today-ops')
                @include('admin.dashboard.partials.charts-grid')
                <div class="grid grid-cols-1 gap-2 md:gap-3 lg:grid-cols-2">
                    @include('admin.dashboard.partials.branch-performance')
                    @include('admin.dashboard.partials.top-customers')
                </div>
                @include('admin.dashboard.partials.intelligence')
            </div>
            <aside class="exec-dashboard__rail xl:col-span-4">
                @include('admin.dashboard.partials.activity-feed')
            </aside>
        </div>
    </div>
</x-admin-layout>
