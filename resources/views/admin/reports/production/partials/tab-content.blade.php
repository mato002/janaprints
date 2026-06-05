@props(['tab_data', 'active_tab', 'print_mode' => false])

@php
    $summaryClass = $print_mode ? 'summary' : 'mb-6 grid grid-cols-2 gap-3 md:grid-cols-4';
    $metricClass = $print_mode ? 'metric' : 'rounded-lg border border-erp-border/70 p-4';
@endphp

@if (($tab_data['type'] ?? '') === 'throughput')
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
        <div class="mt-6">
            @include('admin.reports.production.partials.simple-table', $tab_data['machines'])
        </div>
    @if ($print_mode) </div> @else </x-admin.card> @endif
@elseif (($tab_data['type'] ?? '') === 'quality')
    @if ($print_mode) <div> @else <x-admin.card class="mb-6"> @endif
        <div class="{{ $summaryClass }}">
            @foreach ($tab_data['summary'] ?? [] as $item)
                <div class="{{ $metricClass }}">
                    <p class="metric-label text-[11px] uppercase tracking-wide text-slate-500">{{ $item['label'] }}</p>
                    <p class="metric-value text-xl font-bold tabular-nums text-erp-primary">{{ $item['value'] }}</p>
                </div>
            @endforeach
        </div>
        @include('admin.reports.production.partials.simple-table', $tab_data['daily'])
    @if ($print_mode) </div> @else </x-admin.card> @endif
@elseif (($tab_data['type'] ?? '') === 'materials')
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
            @include('admin.reports.production.partials.simple-table', $tab_data['consumption'])
            @include('admin.reports.production.partials.simple-table', $tab_data['waste'])
        </div>
    @if ($print_mode) </div> @else </x-admin.card> @endif
@elseif (($tab_data['type'] ?? '') === 'dispatch')
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
            @include('admin.reports.production.partials.simple-table', $tab_data['delivered'])
            @include('admin.reports.production.partials.simple-table', $tab_data['late'])
        </div>
    @if ($print_mode) </div> @else </x-admin.card> @endif
@elseif (($tab_data['type'] ?? '') === 'profitability')
    @if ($print_mode) <div class="space-y-6"> @else <x-admin.card class="mb-6 space-y-6"> @endif
        @include('admin.reports.production.partials.simple-table', $tab_data['jobs'])
        @include('admin.reports.production.partials.simple-table', $tab_data['departments'])
        @include('admin.reports.production.partials.simple-table', $tab_data['customers'])
    @if ($print_mode) </div> @else </x-admin.card> @endif
@else
    @unless ($print_mode)
    <x-admin.card>
        <x-admin.empty-state
            icon="chart-bar"
            :title="__('Production Reports')"
            :description="$tab_data['message'] ?? __('No data available for the selected filters.')"
        />
    </x-admin.card>
    @endunless
@endif
