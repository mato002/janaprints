@props(['distribution'])

<x-admin.card class="xl:col-span-2">
    <h2 class="mb-3 text-xs font-semibold uppercase tracking-wide text-erp-primary">{{ __('Workforce Distribution') }}</h2>
    <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
        @foreach ([
            'by_department' => __('By Department'),
            'by_branch' => __('By Branch'),
            'by_employment_type' => __('By Employment Type'),
            'by_status' => __('By Status'),
        ] as $key => $title)
            @php $rows = $distribution[$key] ?? []; @endphp
            <div>
                <h3 class="mb-2 text-[11px] font-medium uppercase tracking-wide text-slate-500">{{ $title }}</h3>
                @if (! empty($rows))
                    <div class="space-y-1.5">
                        @foreach (array_slice($rows, 0, 6) as $row)
                            <div class="flex items-center gap-2 text-xs">
                                <span class="w-28 shrink-0 truncate text-slate-600" title="{{ $row['label'] }}">{{ $row['label'] }}</span>
                                <div class="h-1.5 min-w-0 flex-1 overflow-hidden rounded-full bg-slate-100">
                                    <div class="h-full rounded-full bg-erp-accent/70" style="width: {{ max($row['percent'], 4) }}%"></div>
                                </div>
                                <span class="w-6 shrink-0 text-right font-semibold tabular-nums text-erp-primary">{{ $row['count'] }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-slate-500">{{ __('No data.') }}</p>
                @endif
            </div>
        @endforeach
    </div>
</x-admin.card>
