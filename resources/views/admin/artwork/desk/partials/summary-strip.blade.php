@php
    $toneClasses = fn (string $tone) => match ($tone) {
        'blue' => 'text-blue-600',
        'indigo' => 'text-indigo-600',
        'amber' => 'text-amber-600',
        'emerald' => 'text-emerald-600',
        'violet' => 'text-violet-600',
        'rose' => 'text-rose-600',
        'slate' => 'text-slate-600',
        default => 'text-erp-primary',
    };

    // Support both new flat strip and legacy grouped summary.
    $cards = is_array($summary) && array_is_list($summary)
        ? $summary
        : ($summary['operational'] ?? []);
@endphp

<section class="designer-desk-today mb-3 rounded-xl border border-erp-border bg-white px-3 py-2.5 shadow-sm" aria-label="{{ __('Today') }}">
    <div class="mb-1.5 flex items-center justify-between gap-2 px-1">
        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">{{ __('Today') }}</p>
    </div>
    <div class="grid grid-cols-3 gap-1.5 sm:grid-cols-5">
        @foreach ($cards as $card)
            @if (! empty($card['filter']))
                <button
                    type="button"
                    class="rounded-lg border border-slate-100 bg-slate-50/60 px-2 py-2 text-center transition hover:border-erp-accent/30 hover:bg-erp-accent/5"
                    :class="{ 'ring-1 ring-erp-accent/40 bg-erp-accent/5': activeFilter === @js($card['filter']) }"
                    @click="setFilter(@js($card['filter']))"
                >
                    <p class="text-lg font-bold tabular-nums {{ $toneClasses($card['tone'] ?? 'primary') }}">{{ $card['value'] }}</p>
                    <p class="mt-0.5 text-[10px] font-semibold uppercase tracking-wide text-slate-500">{{ $card['label'] }}</p>
                </button>
            @else
                <div class="rounded-lg border border-slate-100 bg-slate-50/60 px-2 py-2 text-center">
                    <p class="text-lg font-bold tabular-nums {{ $toneClasses($card['tone'] ?? 'primary') }}">{{ $card['value'] }}</p>
                    <p class="mt-0.5 text-[10px] font-semibold uppercase tracking-wide text-slate-500">{{ $card['label'] }}</p>
                </div>
            @endif
        @endforeach
    </div>
</section>
