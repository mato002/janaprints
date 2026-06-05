<x-admin-layout :title="$title">
    <x-admin.page-header :title="$title" :description="$description">
        <x-slot name="actions">
            <span class="erp-badge bg-slate-100 text-slate-700">{{ __('Historical reports') }}</span>
            @include('admin.reports.production.partials.export-actions', [
                'can_export' => $can_export,
                'filters' => $filters,
                'schedule_frequencies' => $schedule_frequencies,
            ])
        </x-slot>
    </x-admin.page-header>

    @if (session('status'))
        <x-admin.card class="mb-4 border-emerald-200 bg-emerald-50 text-sm text-emerald-800">
            {{ session('status') }}
        </x-admin.card>
    @endif

    @include('admin.reports.production.partials.filters', [
        'filters' => $filters,
        'branches' => $branches,
    ])

    @include('admin.reports.production.partials.catalog', ['catalog' => $catalog])

    @include('admin.reports.production.partials.tabs', [
        'tabs' => $tabs,
        'active_tab' => $active_tab,
        'filters' => $filters,
    ])

    @include('admin.reports.production.partials.tab-content', [
        'tab_data' => $tab_data,
        'active_tab' => $active_tab,
    ])
</x-admin-layout>
