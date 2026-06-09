<x-admin-layout :title="$title">
    <x-admin.page-header :title="$title" :description="$description" />

    @include('admin.reports.partials.filters', [
        'filters' => $filters,
        'branches' => $branches,
        'can_export' => $can_export,
        'export_route' => 'admin.reports.executive.export',
        'format_in_path' => true,
    ])

    @foreach ($widget_sections as $section)
        @include('admin.reports.partials.kpi-grid', [
            'title' => $section['title'],
            'widgets' => $section['widgets'],
        ])
    @endforeach

    @include('admin.reports.partials.attention-list', ['items' => $attention])

    @include('admin.reports.partials.pipeline-board', [
        'stages' => $pipeline,
        'title' => __('Production Pipeline'),
    ])

    @include('admin.reports.partials.branch-table', [
        'rows' => $branches_table,
        'title' => __('Branch Performance'),
    ])

    <x-admin.card class="mb-6">
        <h2 class="mb-3 text-sm font-semibold text-erp-primary">{{ __('Recent Activity') }}</h2>
        @forelse ($recent_activity as $entry)
            <p class="border-b border-erp-border/60 py-2 text-sm text-slate-600 last:border-0">
                <span>{{ $entry['message'] }}</span>
                @if (! empty($entry['created_at']))
                    <span class="ml-2 text-xs text-slate-400">{{ $entry['created_at'] }}</span>
                @endif
            </p>
        @empty
            <x-admin.empty-state
                icon="clock"
                :title="__('Recent activity')"
                :description="__('Activity feed placeholder — events will appear when modules log activity.')"
            />
        @endforelse
    </x-admin.card>
</x-admin-layout>
