@php
    use App\Support\Navigation\WorkspaceEmbed;

    $health = $workQueue['health'] ?? ['percent' => 100, 'label' => __('Healthy'), 'tone' => 'emerald', 'detail' => ''];
    $toneClasses = fn (string $tone) => match ($tone) {
        'amber' => 'text-amber-600',
        'rose' => 'text-rose-600',
        'blue' => 'text-blue-600',
        'emerald' => 'text-emerald-600',
        default => 'text-erp-primary',
    };
    $healthBadge = match ($health['tone'] ?? 'emerald') {
        'emerald' => 'bg-emerald-600 text-white',
        'amber' => 'bg-amber-500 text-white',
        'rose' => 'bg-rose-600 text-white',
        default => 'bg-slate-700 text-white',
    };
    $frame = WorkspaceEmbed::turboFrame();
@endphp

<section class="store-desk-health mb-3 rounded-xl border border-erp-border bg-white p-3 shadow-sm" aria-label="{{ __('Store health') }}">
    <div class="mb-2 flex flex-wrap items-center justify-between gap-2 px-1">
        <div>
            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">{{ __('Store health') }}</p>
            <p class="text-xs text-slate-500">{{ $health['detail'] ?? '' }}</p>
        </div>
        <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold {{ $healthBadge }}">
            <span class="tabular-nums">{{ (int) ($health['percent'] ?? 0) }}%</span>
            <span>{{ $health['label'] ?? '' }}</span>
        </span>
    </div>

    <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
        @foreach ($workQueue['summary'] ?? [] as $card)
            @if (! empty($card['url']))
                <a
                    href="{{ WorkspaceEmbed::url($card['url']) }}"
                    @class([
                        'rounded-lg border border-slate-100 bg-slate-50/60 px-3 py-2 text-center transition hover:border-erp-accent/30 hover:bg-erp-accent/5',
                        'ring-1 ring-amber-300/60' => ($card['highlight'] ?? false) && ($card['tone'] ?? '') === 'amber',
                        'ring-1 ring-rose-300/60' => ($card['highlight'] ?? false) && ($card['tone'] ?? '') === 'rose',
                    ])
                    data-turbo-frame="{{ $frame }}"
                    data-turbo-action="advance"
                >
                    <p @class(['text-xl font-bold tabular-nums', $toneClasses($card['tone'] ?? 'primary')])>{{ $card['value'] }}</p>
                    <p class="mt-0.5 text-[10px] font-semibold uppercase tracking-wide text-slate-500">{{ $card['label'] }}</p>
                </a>
            @else
                <div class="rounded-lg border border-slate-100 bg-slate-50/60 px-3 py-2 text-center">
                    <p @class(['text-xl font-bold tabular-nums', $toneClasses($card['tone'] ?? 'primary')])>{{ $card['value'] }}</p>
                    <p class="mt-0.5 text-[10px] font-semibold uppercase tracking-wide text-slate-500">{{ $card['label'] }}</p>
                </div>
            @endif
        @endforeach
    </div>
</section>
