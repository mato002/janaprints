@if (collect($urgent)->sum('count') > 0)
    <div class="designer-desk-urgent mb-4 flex flex-wrap gap-2">
        @foreach ($urgent as $item)
            @if ($item['count'] > 0)
                @php
                    $toneClasses = match ($item['tone'] ?? 'amber') {
                        'rose' => 'border-rose-200 bg-rose-50 text-rose-900',
                        'indigo' => 'border-indigo-200 bg-indigo-50 text-indigo-900',
                        'blue' => 'border-blue-200 bg-blue-50 text-blue-900',
                        default => 'border-amber-200 bg-amber-50 text-amber-900',
                    };
                @endphp
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-semibold transition hover:opacity-90 {{ $toneClasses }}"
                    @click="filterUrgent(@js($item['key']))"
                >
                    <span aria-hidden="true">⚠</span>
                    <span>{{ $item['label'] }} ({{ $item['count'] }})</span>
                </button>
            @endif
        @endforeach
        <button
            type="button"
            class="inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-50"
            x-show="activeFilter"
            @click="clearFilter()"
        >
            {{ __('Clear filter') }}
        </button>
    </div>
@endif
