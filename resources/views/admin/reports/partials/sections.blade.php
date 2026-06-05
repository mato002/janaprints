@foreach ($sections as $section)
    @if (($section['type'] ?? '') === 'placeholder')
        <x-admin.card class="mb-6">
            <x-admin.empty-state icon="chart-pie" :title="$section['title']" :description="$section['message'] ?? __('Module not ready')" />
        </x-admin.card>
    @elseif (($section['type'] ?? '') === 'kpis')
        <section class="mb-6">
            <h2 class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $section['title'] }}</h2>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach ($section['items'] as $item)
                    <x-admin.kpi-widget
                        :label="$item['label']"
                        :value="$item['value']"
                        :icon="$item['icon'] ?? 'chart-pie'"
                        :hint="$item['hint']"
                    />
                @endforeach
            </div>
        </section>
    @elseif (($section['type'] ?? '') === 'table')
        <x-admin.card class="mb-6">
            <h2 class="mb-3 text-sm font-semibold text-erp-primary">{{ $section['title'] }}</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-erp-border text-left text-[11px] uppercase tracking-wide text-slate-500">
                            @foreach ($section['columns'] as $col)
                                <th class="px-3 py-2 font-semibold">{{ $col }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($section['rows'] as $row)
                            <tr class="border-b border-erp-border/60">
                                @foreach ($row as $cell)
                                    <td class="px-3 py-2 tabular-nums">{{ $cell }}</td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($section['columns']) }}" class="px-3 py-6 text-center text-slate-500">
                                    {{ __('No data for selected filters.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-admin.card>
    @elseif (($section['type'] ?? '') === 'pipeline')
        <x-admin.card class="mb-6">
            <h2 class="mb-3 text-sm font-semibold text-erp-primary">{{ $section['title'] }}</h2>
            <div class="flex flex-wrap gap-2">
                @foreach ($section['stages'] as $index => $stage)
                    @if ($index > 0)
                        <span class="self-center text-slate-300" aria-hidden="true">→</span>
                    @endif
                    <div class="min-w-[7rem] rounded-lg border border-erp-border bg-erp-page px-3 py-2">
                        <p class="text-[11px] text-slate-500">{{ $stage['label'] }}</p>
                        <p class="text-lg font-semibold tabular-nums text-erp-primary">{{ number_format($stage['count']) }}</p>
                    </div>
                @endforeach
            </div>
        </x-admin.card>
    @elseif (($section['type'] ?? '') === 'attention')
        <x-admin.card class="mb-6">
            <h2 class="mb-3 text-sm font-semibold text-erp-primary">{{ $section['title'] }}</h2>
            <ul class="divide-y divide-erp-border">
                @forelse ($section['items'] as $item)
                    <li class="flex items-center justify-between py-2 text-sm">
                        <span>{{ $item['label'] }}</span>
                        <span class="font-semibold tabular-nums">{{ $item['display'] ?? $item['count'] ?? '—' }}</span>
                    </li>
                @empty
                    <li class="py-4 text-center text-slate-500">{{ __('No alerts.') }}</li>
                @endforelse
            </ul>
        </x-admin.card>
    @elseif (($section['type'] ?? '') === 'split')
        <section class="mb-6">
            <h2 class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $section['title'] }}</h2>
            @if (! empty($section['kpis']))
                <div class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach ($section['kpis'] as $item)
                        <x-admin.kpi-widget :label="$item['label']" :value="$item['value']" :icon="$item['icon'] ?? 'chart-pie'" :hint="$item['hint']" />
                    @endforeach
                </div>
            @endif
            @foreach ($section['tables'] ?? [] as $table)
                @include('admin.reports.partials.sections', ['sections' => [$table]])
            @endforeach
        </section>
    @elseif (($section['type'] ?? '') === 'drilldown')
        <x-admin.card class="mb-6">
            <h2 class="mb-3 text-sm font-semibold text-erp-primary">{{ $section['title'] }}</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-erp-border text-left text-[11px] uppercase tracking-wide text-slate-500">
                            @foreach ($section['columns'] as $col)
                                <th class="px-3 py-2 font-semibold">{{ $col }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($section['rows'] as $row)
                            <tr class="border-b border-erp-border/60">
                                @foreach ($row['cells'] as $index => $cell)
                                    <td class="px-3 py-2 tabular-nums">
                                        @if ($index === 0 && ! empty($row['url']))
                                            <a href="{{ $row['url'] }}" class="font-mono text-erp-accent hover:underline" data-turbo-frame="erp-main">{{ $cell }}</a>
                                        @else
                                            {{ $cell }}
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($section['columns']) }}" class="px-3 py-6 text-center text-slate-500">
                                    {{ __('No data for selected filters.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-admin.card>
    @elseif (($section['type'] ?? '') === 'chart')
        <x-admin.card class="mb-4">
            <h3 class="mb-1 text-sm font-semibold text-erp-primary">{{ $section['title'] }}</h3>
            @if (! empty($section['hint']))
                <p class="mb-3 text-xs text-slate-500">{{ $section['hint'] }}</p>
            @endif
            @if (! empty($section['empty']))
                <p class="py-6 text-center text-sm text-slate-500">{{ __('No trend data for selected period.') }}</p>
            @else
                <div class="flex items-end gap-1 overflow-x-auto pb-2" style="min-height: 8rem;">
                    @foreach ($section['points'] as $point)
                        @php $height = $point['max'] > 0 ? max(4, round(($point['value'] / $point['max']) * 100)) : 4; @endphp
                        <div class="flex min-w-[2rem] flex-col items-center justify-end gap-1">
                            <span class="text-[10px] tabular-nums text-slate-600">{{ $point['value'] }}</span>
                            <div class="w-full rounded-t bg-erp-accent/80" style="height: {{ $height }}px"></div>
                            <span class="text-[9px] text-slate-500">{{ $point['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-admin.card>
    @elseif (($section['type'] ?? '') === 'trends')
        <section class="mb-6">
            <h2 class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $section['title'] }}</h2>
            <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
                @foreach ($section['charts'] ?? [] as $chart)
                    @include('admin.reports.partials.sections', ['sections' => [$chart]])
                @endforeach
            </div>
        </section>
    @elseif (($section['type'] ?? '') === 'performers')
        <section class="mb-6">
            <h2 class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $section['title'] }}</h2>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                @foreach ($section['groups'] ?? [] as $group)
                    <x-admin.card>
                        <h3 class="mb-3 text-sm font-semibold text-erp-primary">{{ $group['heading'] }}</h3>
                        <ul class="divide-y divide-erp-border text-sm">
                            @forelse ($group['items'] as $item)
                                <li class="flex items-start justify-between gap-2 py-2">
                                    <div class="min-w-0">
                                        <p class="font-medium text-erp-primary">{{ $item['label'] }}</p>
                                        @if (! empty($item['hint']))
                                            <p class="text-xs text-slate-500">{{ $item['hint'] }}</p>
                                        @endif
                                    </div>
                                    <span class="shrink-0 font-semibold tabular-nums text-erp-accent">{{ $item['value'] }}</span>
                                </li>
                            @empty
                                <li class="py-4 text-center text-slate-500">{{ __('No data') }}</li>
                            @endforelse
                        </ul>
                    </x-admin.card>
                @endforeach
            </div>
        </section>
    @endif
@endforeach
