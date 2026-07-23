@php
    use App\Support\Navigation\WorkspaceEmbed;

    $toneClasses = fn (string $tone) => match ($tone) {
        'amber' => 'text-amber-600',
        'indigo' => 'text-indigo-600',
        'emerald' => 'text-emerald-600',
        'violet' => 'text-violet-600',
        'rose' => 'text-rose-600',
        'slate' => 'text-slate-600',
        default => 'text-erp-primary',
    };
    $frame = WorkspaceEmbed::turboFrame();
@endphp

<div class="sales-desk-metrics mb-4">
    <div class="rounded-xl border border-erp-border bg-white p-3 shadow-sm">
        <p class="mb-2 px-1 text-[10px] font-bold uppercase tracking-widest text-slate-400">{{ __("Today's sales work") }}</p>
        <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-5">
            @foreach ($workQueue['summary'] ?? [] as $card)
                @if (! empty($card['url']))
                    <a
                        href="{{ WorkspaceEmbed::url($card['url']) }}"
                        @class([
                            'sales-desk-kpi rounded-lg border border-slate-100 bg-slate-50/50 px-3 py-2.5 text-center transition hover:border-erp-accent/30 hover:bg-erp-accent/5',
                            'ring-2 ring-erp-accent/20' => ($card['value'] ?? 0) > 0 && ($card['tone'] ?? '') === 'amber',
                        ])
                        data-turbo-frame="{{ $frame }}"
                        data-turbo-action="advance"
                    >
                        <p class="text-xl font-bold tabular-nums {{ $toneClasses($card['tone'] ?? 'primary') }}">{{ $card['value'] }}</p>
                        <p class="mt-0.5 text-[10px] font-semibold uppercase tracking-wide text-slate-500">{{ $card['label'] }}</p>
                    </a>
                @else
                    <div class="sales-desk-kpi rounded-lg border border-slate-100 bg-slate-50/50 px-3 py-2.5 text-center">
                        <p class="text-xl font-bold tabular-nums {{ $toneClasses($card['tone'] ?? 'primary') }}">{{ $card['value'] }}</p>
                        <p class="mt-0.5 text-[10px] font-semibold uppercase tracking-wide text-slate-500">{{ $card['label'] }}</p>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</div>
