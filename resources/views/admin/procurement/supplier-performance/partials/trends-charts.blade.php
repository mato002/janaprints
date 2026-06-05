@props(['series'])

@php
    $charts = [
        'monthly' => __('Monthly Performance'),
        'quarterly' => __('Quarterly Performance'),
        'annual' => __('Annual Performance'),
    ];
@endphp

<div class="grid gap-6 xl:grid-cols-2">
    @foreach ($charts as $key => $label)
        @php
            $points = $series[$key] ?? [];
            $maxSpend = max(1, collect($points)->max('spend') ?: 1);
        @endphp
        <x-admin.card>
            <h3 class="mb-4 text-sm font-semibold text-erp-primary">{{ $label }}</h3>
            @if ($points === [])
                <p class="py-8 text-center text-sm text-slate-500">{{ __('No trend data for selected filters.') }}</p>
            @else
                <div class="space-y-3">
                    @foreach ($points as $point)
                        @php $width = max(4, round(((float) $point['spend'] / $maxSpend) * 100)); @endphp
                        <div>
                            <div class="mb-1 flex items-center justify-between text-xs text-slate-600">
                                <span>{{ $point['label'] }}</span>
                                <span class="font-semibold tabular-nums">
                                    {{ 'KES '.number_format($point['spend'], 0) }}
                                    · {{ $point['orders'] }} {{ __('orders') }}
                                    @if (($point['on_time_percent'] ?? null) !== null)
                                        · {{ round($point['on_time_percent'], 1) }}% {{ __('on-time') }}
                                    @endif
                                </span>
                            </div>
                            <div class="h-2 rounded-full bg-erp-page">
                                <div class="h-2 rounded-full bg-erp-accent transition-all" style="width: {{ $width }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-admin.card>
    @endforeach
</div>
