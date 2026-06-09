@props(['tab_data', 'active_tab', 'print_mode' => false])

@php
    $summaryClass = $print_mode ? 'summary' : 'mb-6 grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6';
    $metricClass = $print_mode ? 'metric' : 'rounded-lg border border-erp-border/70 p-4';
@endphp

@if (($tab_data['type'] ?? '') === 'attendance')
    @if ($print_mode) <div> @else <x-admin.card class="mb-6"> @endif
        <div class="{{ $summaryClass }}">
            @foreach ($tab_data['summary'] ?? [] as $item)
                <div class="{{ $metricClass }}">
                    <p class="metric-label text-[11px] uppercase tracking-wide text-slate-500">{{ $item['label'] }}</p>
                    <p class="metric-value text-xl font-bold tabular-nums text-erp-primary">{{ $item['value'] }}</p>
                </div>
            @endforeach
        </div>
        <div class="grid gap-6 lg:grid-cols-2">
            @include('admin.reports.production.partials.simple-table', $tab_data['daily'])
            @include('admin.reports.production.partials.simple-table', $tab_data['departments'])
        </div>
        <div class="mt-6 grid gap-6 lg:grid-cols-2">
            @include('admin.reports.production.partials.simple-table', $tab_data['late'])
            @include('admin.reports.production.partials.simple-table', $tab_data['absent'])
        </div>
        <div class="mt-6 grid gap-6 lg:grid-cols-2">
            @include('admin.reports.production.partials.simple-table', $tab_data['absent_departments'])
            @include('admin.reports.production.partials.simple-table', $tab_data['overtime'])
        </div>
    @if ($print_mode) </div> @else </x-admin.card> @endif
@elseif (($tab_data['type'] ?? '') === 'leave')
    @if ($print_mode) <div> @else <x-admin.card class="mb-6"> @endif
        <div class="{{ $summaryClass }}">
            @foreach ($tab_data['summary'] ?? [] as $item)
                <div class="{{ $metricClass }}">
                    <p class="metric-label text-[11px] uppercase tracking-wide text-slate-500">{{ $item['label'] }}</p>
                    <p class="metric-value text-xl font-bold tabular-nums text-erp-primary">{{ $item['value'] }}</p>
                </div>
            @endforeach
        </div>
        <div class="grid gap-6 lg:grid-cols-2">
            @include('admin.reports.production.partials.simple-table', $tab_data['by_type'])
            @include('admin.reports.production.partials.simple-table', $tab_data['by_employee'])
        </div>
    @if ($print_mode) </div> @else </x-admin.card> @endif
@elseif (($tab_data['type'] ?? '') === 'payroll')
    @if ($print_mode) <div> @else <x-admin.card class="mb-6"> @endif
        <div class="{{ $summaryClass }}">
            @foreach ($tab_data['summary'] ?? [] as $item)
                <div class="{{ $metricClass }}">
                    <p class="metric-label text-[11px] uppercase tracking-wide text-slate-500">{{ $item['label'] }}</p>
                    <p class="metric-value text-xl font-bold tabular-nums text-erp-primary">{{ $item['value'] }}</p>
                </div>
            @endforeach
        </div>
        <div class="grid gap-6 lg:grid-cols-2">
            @include('admin.reports.production.partials.simple-table', $tab_data['runs'])
            @include('admin.reports.production.partials.simple-table', $tab_data['departments'])
        </div>
    @if ($print_mode) </div> @else </x-admin.card> @endif
@elseif (($tab_data['type'] ?? '') === 'workforce')
    @if ($print_mode) <div class="space-y-6"> @else <x-admin.card class="mb-6 space-y-6"> @endif
        <div class="{{ $summaryClass }}">
            @foreach ($tab_data['summary'] ?? [] as $item)
                <div class="{{ $metricClass }}">
                    <p class="metric-label text-[11px] uppercase tracking-wide text-slate-500">{{ $item['label'] }}</p>
                    <p class="metric-value text-xl font-bold tabular-nums text-erp-primary">{{ $item['value'] }}</p>
                </div>
            @endforeach
        </div>
        @include('admin.reports.production.partials.simple-table', $tab_data['headcount'])
        @include('admin.reports.production.partials.simple-table', $tab_data['movement'])
        <div class="grid gap-6 lg:grid-cols-2">
            @include('admin.reports.production.partials.simple-table', $tab_data['contracts'])
            @include('admin.reports.production.partials.simple-table', $tab_data['training'])
        </div>
    @if ($print_mode) </div> @else </x-admin.card> @endif
@else
    @unless ($print_mode)
    <x-admin.card>
        <x-admin.empty-state
            icon="chart-bar"
            :title="__('HR Reports')"
            :description="$tab_data['message'] ?? __('No data available for the selected filters.')"
        />
    </x-admin.card>
    @endunless
@endif
