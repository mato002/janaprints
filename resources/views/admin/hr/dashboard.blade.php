<x-admin-layout :title="__('HR Command Center')" :breadcrumbs="[['label' => __('HR'), 'url' => route('admin.workspaces.hr')], ['label' => __('Command Center')]]">
    <x-admin.page-header
        :title="__('HR Command Center')"
        :description="__('What needs HR attention today — workforce status, pending actions, compliance, and trends.')"
    />

    <p class="mb-3 text-[11px] text-slate-500">{{ __('As of') }} {{ $dashboard['as_of'] }}</p>

    @include('admin.hr.dashboard.partials.overview', ['cards' => $dashboard['overview']])

    @include('admin.hr.dashboard.partials.action-center', ['items' => $dashboard['action_center']])

    <div class="mb-3 grid grid-cols-1 gap-3 xl:grid-cols-3">
        @include('admin.hr.dashboard.partials.workforce-distribution', ['distribution' => $dashboard['workforce_distribution']])
        @include('admin.hr.dashboard.partials.alerts-panel', ['alerts' => $dashboard['alerts']])
    </div>

    <div class="mb-3 grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-3">
        @include('admin.hr.dashboard.partials.module-snapshot', ['snapshot' => $dashboard['attendance']])
        @include('admin.hr.dashboard.partials.module-snapshot', ['snapshot' => $dashboard['leave']])
        @include('admin.hr.dashboard.partials.module-snapshot', ['snapshot' => $dashboard['payroll']])
        @include('admin.hr.dashboard.partials.module-snapshot', ['snapshot' => $dashboard['performance']])
        @include('admin.hr.dashboard.partials.module-snapshot', ['snapshot' => $dashboard['training']])
        @include('admin.hr.dashboard.partials.document-compliance', ['snapshot' => $dashboard['document_compliance']])
        @include('admin.hr.dashboard.partials.module-snapshot', ['snapshot' => $dashboard['exit']])
    </div>

    @include('admin.hr.dashboard.partials.quick-actions-floating', ['actions' => $dashboard['quick_actions']])
</x-admin-layout>
