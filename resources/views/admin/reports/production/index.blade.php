@php
    $turboFrame = request('embedded') ? 'module-workspace-content' : 'erp-main';
@endphp
<x-admin-layout :title="$title">
    <x-admin.page-header :title="$title">
        <x-slot name="description">
            <span>{{ $active_tab_label }}</span>
            <span class="text-slate-400">·</span>
            <span>{{ $period_label }}</span>
            <span class="text-slate-400">·</span>
            <span>{{ $branch_label }}</span>
        </x-slot>
        <x-slot name="actions">
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
        'turbo_frame' => $turboFrame,
    ])

    @include('admin.reports.production.partials.tabs', [
        'tabs' => $tabs,
        'active_tab' => $active_tab,
        'filters' => $filters,
        'turbo_frame' => $turboFrame,
    ])

    @include('admin.reports.production.partials.tab-content', [
        'tab_data' => $tab_data,
        'active_tab' => $active_tab,
    ])
</x-admin-layout>
