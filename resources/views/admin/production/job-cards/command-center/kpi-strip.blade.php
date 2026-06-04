@php
    $toneClasses = [
        'slate' => 'border-slate-200 bg-slate-50',
        'indigo' => 'border-indigo-200 bg-indigo-50/50',
        'amber' => 'border-amber-200 bg-amber-50/50',
        'emerald' => 'border-emerald-200 bg-emerald-50/50',
        'red' => 'border-red-200 bg-red-50/50',
    ];
@endphp

<div class="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-7">
    @foreach ($kpis as $card)
        @php $wrap = $toneClasses[$card['tone'] ?? 'slate'] ?? $toneClasses['slate']; @endphp
        @if ($card['clickable'] ?? false)
            <a href="{{ $card['url'] }}" class="block rounded-xl border {{ $wrap }} transition-opacity hover:opacity-90" data-turbo-frame="erp-main">
                <x-admin.kpi-widget :label="$card['label']" :value="$card['value']" :icon="$card['icon'] ?? 'chart-pie'" />
            </a>
        @else
            <div class="rounded-xl border {{ $wrap }}">
                <x-admin.kpi-widget :label="$card['label']" :value="$card['value']" :icon="$card['icon'] ?? 'chart-pie'" />
            </div>
        @endif
    @endforeach
</div>
